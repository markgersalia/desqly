<?php

namespace App\Filament\SuperAdmin\Resources\Companies\Pages;

use App\Filament\SuperAdmin\Resources\Companies\CompanyResource;
use App\Models\Company;
use App\Services\BusinessSettings;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCompany extends EditRecord
{
    protected static string $resource = CompanyResource::class;

    protected array $coreSettingsData = [];

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (! $this->record instanceof Company) {
            return $data;
        }

        $businessSettings = app(BusinessSettings::class);
        $defaults = $businessSettings->defaults();
        $settings = $businessSettings->getSettings($this->record);

        $entityType = (string) data_get($settings, 'business.entity_type', 'company');

        $data['business'] = [
            'entity_type' => in_array($entityType, ['company', 'individual'], true) ? $entityType : 'company',
            'timezone' => (string) data_get($settings, 'business.timezone', data_get($defaults, 'business.timezone', config('app.timezone', 'UTC'))),
            'currency' => strtoupper((string) data_get($settings, 'business.currency', data_get($defaults, 'business.currency', 'PHP'))),
        ];

        $data['booking'] = [
            'mode' => (string) data_get($settings, 'booking.mode', data_get($defaults, 'booking.mode', 'time_slot')),
            'has_listings' => (bool) data_get($settings, 'booking.has_listings', data_get($defaults, 'booking.has_listings', true)),
            'requires_staff' => $entityType === 'company',
            'requires_bed' => (bool) data_get($settings, 'booking.requires_bed', data_get($defaults, 'booking.requires_bed', false)),
            'requires_follow_up' => (bool) data_get($settings, 'booking.requires_follow_up', data_get($defaults, 'booking.requires_follow_up', true)),
            'slot_interval_minutes' => (int) data_get($settings, 'booking.slot_interval_minutes', data_get($defaults, 'booking.slot_interval_minutes', 60)),
            'day_start' => (string) data_get($settings, 'booking.day_start', data_get($defaults, 'booking.day_start', '09:00')),
            'day_end' => (string) data_get($settings, 'booking.day_end', data_get($defaults, 'booking.day_end', '17:00')),
            'expire_after_hours' => (int) data_get($settings, 'booking.expire_after_hours', data_get($defaults, 'booking.expire_after_hours', 24)),
            'grace_period_minutes' => (int) data_get($settings, 'booking.grace_period_minutes', data_get($defaults, 'booking.grace_period_minutes', 30)),
        ];

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $businessData = (array) ($data['business'] ?? []);
        $bookingData = (array) ($data['booking'] ?? []);

        $entityType = (string) ($businessData['entity_type'] ?? 'company');
        $entityType = in_array($entityType, ['company', 'individual'], true) ? $entityType : 'company';

        $this->coreSettingsData = [
            'business' => [
                'entity_type' => $entityType,
                'timezone' => (string) ($businessData['timezone'] ?? ''),
                'currency' => strtoupper((string) ($businessData['currency'] ?? '')),
            ],
            'booking' => [
                'mode' => (string) ($bookingData['mode'] ?? 'time_slot'),
                'has_listings' => (bool) ($bookingData['has_listings'] ?? true),
                'requires_staff' => $entityType === 'company',
                'requires_bed' => (bool) ($bookingData['requires_bed'] ?? false),
                'requires_follow_up' => (bool) ($bookingData['requires_follow_up'] ?? true),
                'slot_interval_minutes' => (int) ($bookingData['slot_interval_minutes'] ?? 60),
                'day_start' => (string) ($bookingData['day_start'] ?? '09:00'),
                'day_end' => (string) ($bookingData['day_end'] ?? '17:00'),
                'expire_after_hours' => (int) ($bookingData['expire_after_hours'] ?? 24),
                'grace_period_minutes' => (int) ($bookingData['grace_period_minutes'] ?? 30),
            ],
        ];

        unset($data['business'], $data['booking'], $data['business_entity_type']);

        return $data;
    }

    protected function afterSave(): void
    {
        if (! $this->record instanceof Company) {
            return;
        }

        $businessSettings = app(BusinessSettings::class);

        $businessSettings->saveSettings([
            'business' => array_replace(
                (array) ($this->coreSettingsData['business'] ?? []),
                ['name' => (string) $this->record->name]
            ),
            'booking' => (array) ($this->coreSettingsData['booking'] ?? []),
        ], $this->record);

        $entityType = (string) data_get($this->coreSettingsData, 'business.entity_type', 'company');

        if ($entityType === 'company') {
            $defaultBranchId = $businessSettings->getDefaultBranchId($this->record);

            if ($defaultBranchId) {
                $businessSettings->backfillBranchAssignments($defaultBranchId, $this->record);
            }
        }

        $businessSettings->applyRuntimeConfig($this->record);
    }
}

