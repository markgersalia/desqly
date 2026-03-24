<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Guava\Calendar\Contracts\Eventable;
use Guava\Calendar\ValueObjects\CalendarEvent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class DayOff extends Model implements Eventable
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'status',
        'reason',
        'start_date',
        'end_date',
    ];
    
    protected function casts(): array
    {
        return [
            'start_date' => 'datetime',
            'end_date' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $dayOff): void {
            if (! $dayOff->start_date && ! $dayOff->end_date) {
                return;
            }

            $start = Carbon::parse($dayOff->start_date ?? $dayOff->end_date);
            $end = Carbon::parse($dayOff->end_date ?? $dayOff->start_date);

            if ($end->lt($start)) {
                [$start, $end] = [$end, $start];
            }

            $dayOff->start_date = $start->startOfDay()->toDateTimeString();
            $dayOff->end_date = $end->endOfDay()->toDateTimeString();
        });
    }

    public function toCalendarEvent(): CalendarEvent
    {
        $reason = trim((string) $this->reason);
        $title = $reason !== '' ? $reason : 'Day Off';

        return CalendarEvent::make($this)
            ->action('edit')
            ->title("(Day Off: {$this->status}) {$title}")
            ->start($this->start_date)
            ->end($this->end_date)
            ->backgroundColor('#ef4444')
            ->displayAuto()
            ->allDay();
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }

    public function scopeOverlapping(Builder $query, $start, $end): Builder
    {
        return $query
            ->where('start_date', '<=', $end)
            ->where('end_date', '>=', $start);
    }

    public static function isDateOff(string $date, ?int $companyId = null): bool
    {
        if (! Schema::hasTable('day_offs')) {
            return false;
        }
        
        if ($companyId === null) {
            $companyId = static::resolveContextCompanyId();
        }

        if ($companyId === null) {
            return false;
        }

        $start = Carbon::parse($date)->startOfDay()->toDateTimeString();
        $end = Carbon::parse($date)->endOfDay()->toDateTimeString();

        return static::query()
            ->where('company_id', $companyId)
            ->approved()
            ->overlapping($start, $end)
            ->exists();
    }

    private static function resolveContextCompanyId(): ?int
    {
        try {
            $tenant = Filament::getTenant();

            if ($tenant instanceof Company) {
                return (int) $tenant->getKey();
            }
        } catch (\Throwable $e) {
        }

        $userCompanyId = auth('web')->user()?->company_id;

        if ($userCompanyId) {
            return (int) $userCompanyId;
        }

        if (! Schema::hasTable('companies')) {
            return null;
        }

        $query = Company::query();

        return $query->count() === 1
            ? (int) $query->value('id')
            : null;
    }
}
