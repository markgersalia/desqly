<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Setting;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BusinessSettings
{
    public function defaults(): array
    {
        return [
            'onboarding' => [
                'completed_at' => null,
            ],
            'business' => [
                'name' => config('app.name'),
                'entity_type' => 'company',
                'type' => 'spa',
                'timezone' => config('app.timezone', 'UTC'),
                'currency' => 'PHP',
            ],
            'booking' => [
                'mode' => config('booking.mode', 'time_slot'),
                'has_listings' => config('booking.has_listings', true),
                'requires_staff' => config('booking.requires_staff', true),
                'requires_bed' => config('booking.requires_bed', false),
                'requires_follow_up' => config('booking.requires_follow_up', true),
                'slot_interval_minutes' => config('booking.slot_interval_minutes', 60),
                'day_start' => config('booking.day_start', '09:00'),
                'day_end' => config('booking.day_end', '17:00'),
                'expire_after_hours' => config('booking.expire_after_hours', 24),
                'grace_period_minutes' => config('booking.grace_period_minutes', 30),
            ],
            'labels' => [
                'staff' => 'Therapist',
                'resource' => 'Bed',
                'service' => 'Service',
                'booking' => 'Booking',
            ],
            'branches' => [
                'default_branch_id' => null,
            ],
        ];
    }

    public function getTemplatePreset(string $type): array
    {
        $presets = [
            'spa' => [
                'booking' => [
                    'requires_bed' => true,
                    'requires_follow_up' => true,
                ],
                'labels' => [
                    'staff' => 'Therapist',
                    'resource' => 'Bed',
                    'service' => 'Service',
                    'booking' => 'Booking',
                ],
            ],
            'clinic' => [
                'booking' => [
                    'requires_bed' => false,
                    'requires_follow_up' => true,
                ],
                'labels' => [
                    'staff' => 'Practitioner',
                    'resource' => 'Room',
                    'service' => 'Appointment',
                    'booking' => 'Visit',
                ],
            ],
            'gym' => [
                'booking' => [
                    'requires_bed' => false,
                    'requires_follow_up' => false,
                ],
                'labels' => [
                    'staff' => 'Trainer',
                    'resource' => 'Station',
                    'service' => 'Session',
                    'booking' => 'Booking',
                ],
            ],
            'generic' => [
                'booking' => [
                    'requires_bed' => false,
                    'requires_follow_up' => false,
                ],
                'labels' => [
                    'staff' => 'Staff',
                    'resource' => 'Resource',
                    'service' => 'Service',
                    'booking' => 'Booking',
                ],
            ],
        ];

        return $presets[$type] ?? $presets['generic'];
    }

    public function getSettings(?Company $company = null): array
    {
        $defaults = $this->defaults();

        if (! $this->settingsTableExists()) {
            return $defaults;
        }

        $resolvedCompany = $this->resolveCompany($company);
        $query = Setting::query();

        if ($resolvedCompany && Schema::hasColumn('settings', 'company_id')) {
            $query->where('company_id', $resolvedCompany->getKey());
        }

        $row = $query->first();
        $data = is_array($row?->data) ? $row->data : [];

        return $this->normalizeSettings(array_replace_recursive($defaults, $data));
    }

    public function saveSettings(array $data, ?Company $company = null): Setting
    {
        if (! $this->settingsTableExists()) {
            throw new \RuntimeException('Settings table does not exist yet.');
        }

        $resolvedCompany = $this->resolveCompany($company);
        $merged = $this->normalizeSettings(array_replace_recursive($this->getSettings($resolvedCompany), $data));
        $query = Setting::query();

        if ($resolvedCompany && Schema::hasColumn('settings', 'company_id')) {
            $query->where('company_id', $resolvedCompany->getKey());
        }

        $row = $query->first() ?? new Setting();

        if ($resolvedCompany && Schema::hasColumn('settings', 'company_id')) {
            $row->company_id = $resolvedCompany->getKey();
        }

        $row->data = $merged;
        $row->save();

        return $row;
    }

    public function isOnboardingComplete(?Company $company = null): bool
    {
        return ! empty(data_get($this->getSettings($company), 'onboarding.completed_at'));
    }

    public function getDefaultBranchId(?Company $company = null): ?int
    {
        if (! $this->usesBranches($company)) {
            return null;
        }

        $id = data_get($this->getSettings($company), 'branches.default_branch_id');

        return $id ? (int) $id : null;
    }

    public function isCompanyMode(?Company $company = null): bool
    {
        $entityType = (string) data_get($this->getSettings($company), 'business.entity_type', 'company');

        return $entityType === 'company';
    }

    public function usesBranches(?Company $company = null): bool
    {
        return $this->isCompanyMode($company);
    }

    public function requiresStaffAssignment(?Company $company = null): bool
    {
        return $this->isCompanyMode($company);
    }

    public function getLabel(string $key, string $default): string
    {
        $value = data_get($this->getSettings(), "labels.{$key}", $default);

        return is_string($value) && $value !== '' ? $value : $default;
    }

    public function applyRuntimeConfig(?Company $company = null): void
    {
        $settings = $this->getSettings($company);

        config([
            'app.name' => data_get($settings, 'business.name', config('app.name')),
            'app.currency' => data_get($settings, 'business.currency', 'PHP'),
            'app.timezone' => data_get($settings, 'business.timezone', config('app.timezone')),
            'business.entity_type' => data_get($settings, 'business.entity_type', 'company'),
            'booking.mode' => data_get($settings, 'booking.mode', 'time_slot'),
            'booking.has_listings' => (bool) data_get($settings, 'booking.has_listings', true),
            'booking.requires_staff' => $this->requiresStaffAssignment($company),
            'booking.requires_bed' => (bool) data_get($settings, 'booking.requires_bed', false),
            'booking.requires_follow_up' => (bool) data_get($settings, 'booking.requires_follow_up', true),
            'booking.slot_interval_minutes' => (int) data_get($settings, 'booking.slot_interval_minutes', 60),
            'booking.day_start' => data_get($settings, 'booking.day_start', '09:00'),
            'booking.day_end' => data_get($settings, 'booking.day_end', '17:00'),
            'booking.expire_after_hours' => (int) data_get($settings, 'booking.expire_after_hours', 24),
            'booking.grace_period_minutes' => (int) data_get($settings, 'booking.grace_period_minutes', 30),
        ]);
    }

    public function backfillBranchAssignments(int $defaultBranchId, ?Company $company = null): void
    {
        if ($defaultBranchId <= 0) {
            return;
        }

        if (! Schema::hasTable('therapists') || ! Schema::hasTable('beds') || ! Schema::hasTable('bookings')) {
            return;
        }

        $resolvedCompany = $this->resolveCompany($company);
        $companyId = $resolvedCompany?->getKey();

        $therapists = DB::table('therapists')->whereNull('branch_id');
        $beds = DB::table('beds')->whereNull('branch_id');
        $bookings = DB::table('bookings')->whereNull('branch_id');

        if ($companyId && Schema::hasColumn('therapists', 'company_id')) {
            $therapists->where(function ($query) use ($companyId) {
                $query->where('company_id', $companyId)->orWhereNull('company_id');
            });
        }

        if ($companyId && Schema::hasColumn('beds', 'company_id')) {
            $beds->where(function ($query) use ($companyId) {
                $query->where('company_id', $companyId)->orWhereNull('company_id');
            });
        }

        if ($companyId && Schema::hasColumn('bookings', 'company_id')) {
            $bookings->where(function ($query) use ($companyId) {
                $query->where('company_id', $companyId)->orWhereNull('company_id');
            });
        }

        $therapists->update(['branch_id' => $defaultBranchId]);
        $beds->update(['branch_id' => $defaultBranchId]);
        $bookings->update(['branch_id' => $defaultBranchId]);
    }

    private function settingsTableExists(): bool
    {
        return Schema::hasTable('settings');
    }

    private function normalizeSettings(array $settings): array
    {
        $entityType = (string) data_get($settings, 'business.entity_type', 'company');
        $entityType = in_array($entityType, ['company', 'individual'], true) ? $entityType : 'company';
        $isCompanyMode = $entityType === 'company';
        $bookingMode = (string) data_get($settings, 'booking.mode', 'time_slot');
        $bookingMode = in_array($bookingMode, ['time_slot', 'whole_day'], true) ? $bookingMode : 'time_slot';

        data_set($settings, 'business.entity_type', $entityType);
        data_set($settings, 'booking.requires_staff', $isCompanyMode);
        data_set($settings, 'booking.mode', $bookingMode);

        return $settings;
    }

    private function resolveCompany(?Company $company = null): ?Company
    {
        if ($company) {
            return $company;
        }

        try {
            $tenant = Filament::getTenant();

            if ($tenant instanceof Company) {
                return $tenant;
            }
        } catch (\Throwable $e) {
        }

        $userCompany = auth('web')->user()?->company;

        if ($userCompany instanceof Company) {
            return $userCompany;
        }

        if (! Schema::hasTable('companies')) {
            return null;
        }

        $query = Company::query();
        $count = $query->count();

        if ($count === 1) {
            return $query->first();
        }

        return null;
    }
}
