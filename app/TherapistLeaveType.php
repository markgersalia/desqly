<?php

namespace App;

enum TherapistLeaveType: string
{
    case SickLeave = 'sick_leave';
    case VacationLeave = 'vacation_leave';
    case EmergencyLeave = 'emergency_leave';
    case Other = 'other';

    /**
     * Human-readable label (for UI)
     */
    public function label(): string
    {
        return match ($this) {
            self::SickLeave => 'Sick Leave',
            self::VacationLeave => 'Vacation Leave',
            self::EmergencyLeave => 'Emergency Leave',
            self::Other => 'Other',
        };
    }

    /**
     * Filament color mapping (optional)
     */
    public function color(): string
    {
        return match ($this) {
            self::SickLeave => 'warning',
            self::VacationLeave => 'info',
            self::EmergencyLeave => 'danger',
            self::Other => 'gray',
        };
    }

    /**
     * For Select options
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($case) => [$case->value => $case->label()])
            ->toArray();
    }
}
