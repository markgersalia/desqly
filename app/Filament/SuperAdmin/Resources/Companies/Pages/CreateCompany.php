<?php

namespace App\Filament\SuperAdmin\Resources\Companies\Pages;

use App\Filament\SuperAdmin\Resources\Companies\CompanyResource;
use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Services\BusinessSettings;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Arr;
use Spatie\Permission\Models\Role;

class CreateCompany extends CreateRecord
{
    protected static string $resource = CompanyResource::class;

    /**
     * @var array{admin_name: string, admin_email: string, admin_password: string}|array{}
     */
    protected array $adminData = [];

    protected array $coreSettingsData = [];

    protected string $businessEntityType = 'company';

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->adminData = Arr::only($data, [
            'admin_name',
            'admin_email',
            'admin_password',
        ]);

        $businessData = (array) ($data['business'] ?? []);
        $bookingData = (array) ($data['booking'] ?? []);

        $entityType = (string) ($businessData['entity_type'] ?? 'company');
        $this->businessEntityType = in_array($entityType, ['company', 'individual'], true)
            ? $entityType
            : 'company';

        $this->coreSettingsData = [
            'business' => [
                'entity_type' => $this->businessEntityType,
                'timezone' => (string) ($businessData['timezone'] ?? ''),
                'currency' => strtoupper((string) ($businessData['currency'] ?? '')),
            ],
            'booking' => [
                'mode' => (string) ($bookingData['mode'] ?? 'time_slot'),
                'has_listings' => (bool) ($bookingData['has_listings'] ?? true),
                'requires_staff' => $this->businessEntityType === 'company',
                'requires_bed' => (bool) ($bookingData['requires_bed'] ?? false),
                'requires_follow_up' => (bool) ($bookingData['requires_follow_up'] ?? true),
                'slot_interval_minutes' => (int) ($bookingData['slot_interval_minutes'] ?? 60),
                'day_start' => (string) ($bookingData['day_start'] ?? '09:00'),
                'day_end' => (string) ($bookingData['day_end'] ?? '17:00'),
                'expire_after_hours' => (int) ($bookingData['expire_after_hours'] ?? 24),
                'grace_period_minutes' => (int) ($bookingData['grace_period_minutes'] ?? 30),
            ],
        ];

        unset(
            $data['admin_name'],
            $data['admin_email'],
            $data['admin_password'],
            $data['business'],
            $data['booking'],
            $data['business_entity_type']
        );

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->initializeBusinessSettings();
        $this->createInitialAdminUser();
    }

    protected function initializeBusinessSettings(): void
    {
        if (! $this->record instanceof Company) {
            return;
        }

        $businessSettings = app(BusinessSettings::class);
        $defaults = $businessSettings->defaults();
        $defaultBranchId = null;

        if ($this->businessEntityType === 'company') {
            $branch = Branch::query()->create([
                'company_id' => $this->record->getKey(),
                'name' => 'Main Branch',
                'address' => null,
                'is_active' => true,
            ]);

            $defaultBranchId = $branch->id;
        }

        $businessTimezone = (string) data_get(
            $this->coreSettingsData,
            'business.timezone',
            (string) data_get($defaults, 'business.timezone', config('app.timezone', 'UTC'))
        );
        $businessCurrency = strtoupper((string) data_get(
            $this->coreSettingsData,
            'business.currency',
            (string) data_get($defaults, 'business.currency', 'PHP')
        ));

        $bookingOverrides = array_replace(
            (array) data_get($defaults, 'booking', []),
            (array) data_get($this->coreSettingsData, 'booking', []),
            ['requires_staff' => $this->businessEntityType === 'company']
        );

        $businessSettings->saveSettings([
            'onboarding' => [
                'completed_at' => now()->toDateTimeString(),
            ],
            'business' => [
                'name' => (string) $this->record->name,
                'entity_type' => $this->businessEntityType,
                'type' => 'generic',
                'timezone' => $businessTimezone,
                'currency' => $businessCurrency,
            ],
            'booking' => $bookingOverrides,
            'labels' => (array) data_get($defaults, 'labels', []),
            'branches' => [
                'default_branch_id' => $defaultBranchId,
            ],
        ], $this->record);

        if ($defaultBranchId) {
            $businessSettings->backfillBranchAssignments($defaultBranchId, $this->record);
        }

        $businessSettings->applyRuntimeConfig($this->record);
    }

    protected function createInitialAdminUser(): void
    {
        $adminName = (string) ($this->adminData['admin_name'] ?? '');
        $adminEmail = (string) ($this->adminData['admin_email'] ?? '');
        $adminPassword = (string) ($this->adminData['admin_password'] ?? '');

        if (blank($adminName) || blank($adminEmail) || blank($adminPassword)) {
            return;
        }

        $adminRole = Role::query()->firstOrCreate([
            'name' => 'Admin',
            'guard_name' => 'web',
        ]);

        $admin = User::query()->create([
            'company_id' => $this->record->getKey(),
            'name' => $adminName,
            'email' => $adminEmail,
            'password' => $adminPassword,
            'email_verified_at' => now(),
        ]);

        $admin->assignRole($adminRole);
    }
}
