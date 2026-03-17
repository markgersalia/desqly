<?php

namespace App\Services;

use App\Models\Setting;
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
                'type' => 'spa',
                'timezone' => config('app.timezone', 'UTC'),
                'currency' => 'PHP',
            ],
            'booking' => [
                'has_listings' => config('booking.has_listings', true),
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

    public function getSettings(): array
    {
        $defaults = $this->defaults();

        if (! $this->settingsTableExists()) {
            return $defaults;
        }

        $row = Setting::query()->first();
        $data = is_array($row?->data) ? $row->data : [];

        return array_replace_recursive($defaults, $data);
    }

    public function saveSettings(array $data): Setting
    {
        if (! $this->settingsTableExists()) {
            throw new \RuntimeException('Settings table does not exist yet.');
        }

        $merged = array_replace_recursive($this->getSettings(), $data);

        $row = Setting::query()->first() ?? new Setting();
        $row->data = $merged;
        $row->save();

        return $row;
    }

    public function isOnboardingComplete(): bool
    {
        return ! empty(data_get($this->getSettings(), 'onboarding.completed_at'));
    }

    public function getDefaultBranchId(): ?int
    {
        $id = data_get($this->getSettings(), 'branches.default_branch_id');

        return $id ? (int) $id : null;
    }

    public function getLabel(string $key, string $default): string
    {
        $value = data_get($this->getSettings(), "labels.{$key}", $default);

        return is_string($value) && $value !== '' ? $value : $default;
    }

    public function applyRuntimeConfig(): void
    {
        $settings = $this->getSettings();

        config([
            'app.name' => data_get($settings, 'business.name', config('app.name')),
            'app.currency' => data_get($settings, 'business.currency', 'PHP'),
            'app.timezone' => data_get($settings, 'business.timezone', config('app.timezone')),
            'booking.has_listings' => (bool) data_get($settings, 'booking.has_listings', true),
            'booking.requires_bed' => (bool) data_get($settings, 'booking.requires_bed', false),
            'booking.requires_follow_up' => (bool) data_get($settings, 'booking.requires_follow_up', true),
            'booking.slot_interval_minutes' => (int) data_get($settings, 'booking.slot_interval_minutes', 60),
            'booking.day_start' => data_get($settings, 'booking.day_start', '09:00'),
            'booking.day_end' => data_get($settings, 'booking.day_end', '17:00'),
            'booking.expire_after_hours' => (int) data_get($settings, 'booking.expire_after_hours', 24),
            'booking.grace_period_minutes' => (int) data_get($settings, 'booking.grace_period_minutes', 30),
        ]);
    }

    public function backfillBranchAssignments(int $defaultBranchId): void
    {
        if (! Schema::hasTable('therapists') || ! Schema::hasTable('beds') || ! Schema::hasTable('bookings')) {
            return;
        }

        DB::table('therapists')->whereNull('branch_id')->update(['branch_id' => $defaultBranchId]);
        DB::table('beds')->whereNull('branch_id')->update(['branch_id' => $defaultBranchId]);
        DB::table('bookings')->whereNull('branch_id')->update(['branch_id' => $defaultBranchId]);
    }

    private function settingsTableExists(): bool
    {
        return Schema::hasTable('settings');
    }
}
