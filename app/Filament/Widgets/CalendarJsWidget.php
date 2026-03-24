<?php

namespace App\Filament\Widgets;

use App\Filament\Actions\BookingActions;
use App\Filament\Clusters\Therapist\Resources\TherapistLeaves\TherapistLeaveResource;
use App\Filament\Resources\DayOffs\DayOffResource;
use App\Filament\Resources\Bookings\Schemas\BookingForm;
use App\Models\Booking;
use App\Models\DayOff;
use App\Models\TherapistLeave;
use App\Services\BusinessSettings;
use Carbon\Carbon;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Livewire\Attributes\Reactive;

class CalendarJsWidget extends Widget implements HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithSchemas;

    protected static bool $isLazy = false;

    protected string $view = 'filament.widgets.calendar-js-widget';

    protected int | string | array $columnSpan = 'full';

    /**
     * @var array<string, mixed> | null
     */
    #[Reactive]
    public ?array $pageFilters = null;

    public string $calendarView = 'week';

    public function updatedPageFilters(): void
    {
        $this->dispatch('calendarjs-refresh');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function fetchCalendarEvents(string $start, string $end): array
    {
        $startAt = Carbon::parse($start)->startOfDay();
        $endAt = Carbon::parse($end)->endOfDay();

        $companyId = $this->getCompanyId();
        $branchId = $this->getSelectedBranchId();

        $bookingEvents = Booking::query()
            ->where('start_time', '<=', $endAt)
            ->where('end_time', '>=', $startAt)
            ->when($companyId, fn (Builder $query) => $query->where('company_id', $companyId))
            ->when($branchId, fn (Builder $query) => $query->where('branch_id', $branchId))
            ->with(['customer:id,name', 'listing:id,title'])
            ->get()
            ->map(fn (Booking $booking) => $this->formatBookingEvent($booking));

        $leaveEvents = TherapistLeave::query()
            ->where('start_date', '<=', $endAt)
            ->where('end_date', '>=', $startAt)
            ->when($companyId, fn (Builder $query) => $query->where('company_id', $companyId))
            ->when($branchId, fn (Builder $query) => $query->whereHas('therapist', fn (Builder $therapistQuery) => $therapistQuery->where('branch_id', $branchId)))
            ->with('therapist:id,name')
            ->get()
            ->map(fn (TherapistLeave $leave) => $this->formatLeaveEvent($leave));

        $dayOffEvents = DayOff::query()
            ->overlapping($startAt, $endAt)
            ->when($companyId, fn (Builder $query) => $query->where('company_id', $companyId))
            ->get()
            ->map(fn (DayOff $dayOff) => $this->formatDayOffEvent($dayOff));

        return $bookingEvents
            ->merge($leaveEvents)
            ->merge($dayOffEvents)
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $slot
     */
    public function openCreateBookingFromCalendar(array $slot): void
    {
        $this->mountAction('createBooking', ['slot' => $slot]);
    }

    public function requestDeleteForCalendarEvent(string $guid): void
    {
        $recordInfo = $this->parseRecordGuid($guid);

        if (! $recordInfo) {
            return;
        }

        $this->mountAction($this->deleteActionNameForType($recordInfo['type']), ['id' => $recordInfo['id']]);
    }

    public function openEditActionForCalendarEvent(string $guid): void
    {
        $recordInfo = $this->parseRecordGuid($guid);

        if (! $recordInfo) {
            return;
        }

        $this->mountAction($this->editActionNameForType($recordInfo['type']), ['id' => $recordInfo['id']]);
    }

    /**
     * @param  array<string, mixed>  $newEvent
     * @param  array<string, mixed>  $oldEvent
     * @return array{ok: bool, message?: string}
     */
    public function persistCalendarEventChange(array $newEvent, array $oldEvent = []): array
    {
        $recordGuid = (string) (Arr::get($newEvent, 'recordGuid') ?: Arr::get($newEvent, 'guid'));
        $recordInfo = $this->parseRecordGuid($recordGuid);

        if (! $recordInfo) {
            return [
                'ok' => false,
                'message' => 'Unable to resolve event record.',
            ];
        }

        $start = $this->resolveEventStart($newEvent);

        if (! $start) {
            return [
                'ok' => false,
                'message' => 'Invalid event time payload.',
            ];
        }

        $end = $this->resolveEventEnd($newEvent, $start);

        if (! $end) {
            return [
                'ok' => false,
                'message' => 'Invalid event time payload.',
            ];
        }

        try {
            return match ($recordInfo['type']) {
                'booking' => $this->persistBookingMove($recordInfo['id'], $start, $end),
                'leave' => $this->persistLeaveMove($recordInfo['id'], $start, $end),
                'dayoff' => $this->persistDayOffMove($recordInfo['id'], $start, $end),
                default => [
                    'ok' => false,
                    'message' => 'Unsupported event type.',
                ],
            };
        } catch (\Throwable $exception) {
            report($exception);

            return [
                'ok' => false,
                'message' => 'Unable to persist event update.',
            ];
        }
    }

    public function createBookingAction(): CreateAction
    {
        return CreateAction::make('createBooking')
            ->label('Create Booking')
            ->model(Booking::class)
            ->schema(BookingForm::schema())
            ->mountUsing(function (Schema $schema, array $arguments): void {
                $slot = Arr::get($arguments, 'slot', []);
                $date = (string) (Arr::get($slot, 'date') ?: now()->toDateString());
                $start = (string) (Arr::get($slot, 'start') ?: config('booking.day_start', '09:00'));
                $end = (string) (Arr::get($slot, 'end') ?: config('booking.day_end', '10:00'));

                $startAt = Carbon::parse("{$date} {$start}");
                $endAt = Carbon::parse("{$date} {$end}");

                if ($endAt->lte($startAt)) {
                    $endAt = $startAt->copy()->addHour();
                }

                $branchId = $this->usesBranches()
                    ? ($this->getSelectedBranchId() ?? app(BusinessSettings::class)->getDefaultBranchId())
                    : null;

                $schema->fill([
                    'branch_id' => $branchId,
                    'selected_date' => $startAt->toDateString(),
                    'start_time' => $startAt->toDateTimeString(),
                    'end_time' => $endAt->toDateTimeString(),
                    'available_timeslots' => $startAt->format('h:i a') . ' - ' . $endAt->format('h:i a'),
                ]);
            })
            ->after(fn () => $this->dispatch('calendarjs-refresh'));
    }

    public function editBookingAction(): EditAction
    {
        return EditAction::make('editBooking')
            ->label('Edit Booking')
            ->record(fn (array $arguments) => Booking::query()->findOrFail((int) Arr::get($arguments, 'id')))
            ->schema(BookingForm::schema())
            ->extraModalFooterActions([
                BookingActions::complete(),
                BookingActions::confirm(),
                BookingActions::cancel(),
                BookingActions::makePayment(),
            ])
            ->after(fn () => $this->dispatch('calendarjs-refresh'));
    }

    public function deleteBookingAction(): DeleteAction
    {
        return DeleteAction::make('deleteBooking')
            ->record(fn (array $arguments) => Booking::query()->findOrFail((int) Arr::get($arguments, 'id')))
            ->after(fn () => $this->dispatch('calendarjs-refresh'));
    }

    public function createTherapistLeaveAction(): CreateAction
    {
        return CreateAction::make('createTherapistLeave')
            ->label('Create Leave')
            ->model(TherapistLeave::class)
            ->schema(TherapistLeaveResource::schema())
            ->mountUsing(function (Schema $schema, array $arguments): void {
                $date = (string) (Arr::get($arguments, 'date') ?: now()->toDateString());

                $schema->fill([
                    'start_date' => Carbon::parse($date)->startOfDay()->toDateTimeString(),
                    'end_date' => Carbon::parse($date)->endOfDay()->toDateTimeString(),
                ]);
            })
            ->after(fn () => $this->dispatch('calendarjs-refresh'));
    }

    public function editTherapistLeaveAction(): EditAction
    {
        return EditAction::make('editTherapistLeave')
            ->record(fn (array $arguments) => TherapistLeave::query()->findOrFail((int) Arr::get($arguments, 'id')))
            ->schema(TherapistLeaveResource::schema())
            ->after(fn () => $this->dispatch('calendarjs-refresh'));
    }

    public function deleteTherapistLeaveAction(): DeleteAction
    {
        return DeleteAction::make('deleteTherapistLeave')
            ->record(fn (array $arguments) => TherapistLeave::query()->findOrFail((int) Arr::get($arguments, 'id')))
            ->after(fn () => $this->dispatch('calendarjs-refresh'));
    }

    public function createDayOffAction(): CreateAction
    {
        return CreateAction::make('createDayOff')
            ->label('Create Day Off')
            ->model(DayOff::class)
            ->schema($this->dayOffSchema())
            ->mountUsing(function (Schema $schema, array $arguments): void {
                $date = (string) (Arr::get($arguments, 'date') ?: now()->toDateString());

                $schema->fill([
                    'start_date' => $date,
                    'end_date' => $date,
                    'status' => 'approved',
                ]);
            })
            ->after(fn () => $this->dispatch('calendarjs-refresh'));
    }

    public function editDayOffAction(): EditAction
    {
        return EditAction::make('editDayOff')
            ->record(fn (array $arguments) => DayOff::query()->findOrFail((int) Arr::get($arguments, 'id')))
            ->schema($this->dayOffSchema())
            ->after(fn () => $this->dispatch('calendarjs-refresh'));
    }

    public function deleteDayOffAction(): DeleteAction
    {
        return DeleteAction::make('deleteDayOff')
            ->record(fn (array $arguments) => DayOff::query()->findOrFail((int) Arr::get($arguments, 'id')))
            ->after(fn () => $this->dispatch('calendarjs-refresh'));
    }

    /**
     * @return array<int, mixed>
     */
    protected function dayOffSchema(): array
    {
        return DayOffResource::form(Schema::make($this))
            ->getComponents();
    }

    /**
     * @return array<string, mixed>
     */
    protected function formatBookingEvent(Booking $booking): array
    {
        $start = Carbon::parse($booking->start_time);
        $end = Carbon::parse($booking->end_time);

        $titleParts = [
            '(' . Str::title((string) $booking->status) . ')',
            $booking->listing?->title,
            $booking->title,
        ];

        return [
            'guid' => $this->buildRecordGuid('booking', (int) $booking->getKey()),
            'recordGuid' => $this->buildRecordGuid('booking', (int) $booking->getKey()),
            'title' => trim(implode(' ', array_filter($titleParts))),
            'date' => $start->toDateString(),
            'start' => $start->format('H:i'),
            'end' => $end->format('H:i'),
            'color' => $this->bookingColor((string) $booking->status),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function formatLeaveEvent(TherapistLeave $leave): array
    {
        $start = Carbon::parse($leave->start_date);
        $end = Carbon::parse($leave->end_date);

        if ($end->lte($start)) {
            $end = $start->copy()->addHour();
        }

        $typeValue = $leave->type;
        $typeLabel = (is_object($typeValue) && method_exists($typeValue, 'label'))
            ? (string) $typeValue->label()
            : (filled($typeValue) ? Str::title(str_replace('_', ' ', (string) $typeValue)) : 'Leave');

        $titleParts = [
            "({$typeLabel}: " . Str::title((string) $leave->status) . ')',
            $leave->therapist?->name,
            $leave->reason,
        ];

        return [
            'guid' => $this->buildRecordGuid('leave', (int) $leave->getKey()),
            'recordGuid' => $this->buildRecordGuid('leave', (int) $leave->getKey()),
            'title' => trim(implode(' ', array_filter($titleParts))),
            'date' => $start->toDateString(),
            'start' => $start->format('H:i'),
            'end' => $end->format('H:i'),
            'color' => '#808080',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function formatDayOffEvent(DayOff $dayOff): array
    {
        $start = Carbon::parse($dayOff->start_date)->startOfDay();
        $end = Carbon::parse($dayOff->end_date)->endOfDay();

        return [
            'guid' => $this->buildRecordGuid('dayoff', (int) $dayOff->getKey()),
            'recordGuid' => $this->buildRecordGuid('dayoff', (int) $dayOff->getKey()),
            'title' => '(Day Off: ' . Str::title((string) $dayOff->status) . ') ' . ($dayOff->reason ?: 'Day Off'),
            'date' => $start->toDateString(),
            'start' => $start->format('H:i'),
            'end' => $end->format('H:i'),
            'color' => '#ef4444',
        ];
    }

    /**
     * @return array{ok: bool, message?: string}
     */
    protected function persistBookingMove(int $id, Carbon $start, Carbon $end): array
    {
        /** @var Booking $booking */
        $booking = Booking::query()->with(['therapist', 'bed'])->findOrFail($id);

        if ($message = $this->validateBookingPlacement($booking, $start, $end)) {
            return [
                'ok' => false,
                'message' => $message,
            ];
        }

        $booking->update([
            'start_time' => $start->toDateTimeString(),
            'end_time' => $end->toDateTimeString(),
        ]);

        $this->dispatch('calendarjs-refresh');

        return ['ok' => true];
    }

    /**
     * @return array{ok: bool, message?: string}
     */
    protected function persistLeaveMove(int $id, Carbon $start, Carbon $end): array
    {
        /** @var TherapistLeave $leave */
        $leave = TherapistLeave::query()->findOrFail($id);

        if ($end->lte($start)) {
            $end = $start->copy()->addHour();
        }

        $leave->update([
            'start_date' => $start->toDateTimeString(),
            'end_date' => $end->toDateTimeString(),
        ]);

        $this->dispatch('calendarjs-refresh');

        return ['ok' => true];
    }

    /**
     * @return array{ok: bool, message?: string}
     */
    protected function persistDayOffMove(int $id, Carbon $start, Carbon $end): array
    {
        /** @var DayOff $dayOff */
        $dayOff = DayOff::query()->findOrFail($id);

        if ($end->lt($start)) {
            $end = $start->copy();
        }

        $dayOff->update([
            'start_date' => $start->copy()->startOfDay()->toDateTimeString(),
            'end_date' => $end->copy()->endOfDay()->toDateTimeString(),
        ]);

        $this->dispatch('calendarjs-refresh');

        return ['ok' => true];
    }

    protected function validateBookingPlacement(Booking $booking, Carbon $start, Carbon $end): ?string
    {
        $date = $start->toDateString();

        if (DayOff::isDateOff($date, $booking->company_id)) {
            return 'Selected date falls on a configured day off.';
        }

        $therapist = $booking->therapist;

        if ($therapist) {
            if ($therapist->isOnLeave($start, $end)) {
                return 'Assigned therapist is currently on leave for this time slot.';
            }

            if (! $therapist->isAvailable($date, $start->toDateTimeString(), $end->toDateTimeString(), $booking->id)) {
                return 'Assigned therapist is not available for the selected slot.';
            }
        }

        $bed = $booking->bed;

        if ($bed && ! $bed->isAvailable($date, $start->toDateTimeString(), $end->toDateTimeString(), $booking->id)) {
            return 'Selected bed is not available for the selected slot.';
        }

        return null;
    }

    protected function resolveEventStart(array $event): ?Carbon
    {
        $date = Arr::get($event, 'date');
        $start = Arr::get($event, 'start');

        if (! $date || ! $start) {
            return null;
        }

        return Carbon::parse("{$date} {$start}");
    }

    protected function resolveEventEnd(array $event, Carbon $start): ?Carbon
    {
        $date = (string) Arr::get($event, 'date', $start->toDateString());
        $end = Arr::get($event, 'end');

        if (! $end) {
            return $start->copy()->addHour();
        }

        $endAt = Carbon::parse("{$date} {$end}");

        if ($endAt->lte($start)) {
            $endAt = $endAt->addDay();
        }

        return $endAt;
    }

    protected function getCompanyId(): ?int
    {
        return Filament::getTenant()?->getKey() ?? auth('web')->user()?->company_id;
    }

    protected function getSelectedBranchId(): ?int
    {
        $branchId = data_get($this->pageFilters, 'branch_id');

        if (blank($branchId)) {
            return null;
        }

        $branchId = (int) $branchId;

        return $branchId > 0 ? $branchId : null;
    }

    protected function usesBranches(): bool
    {
        return app(BusinessSettings::class)->usesBranches();
    }

    protected function buildRecordGuid(string $type, int $id): string
    {
        return "{$type}:{$id}";
    }

    /**
     * @return array{type: string, id: int} | null
     */
    protected function parseRecordGuid(string $guid): ?array
    {
        $normalized = Str::before((string) $guid, '@');

        if (! str_contains($normalized, ':')) {
            return null;
        }

        [$type, $rawId] = explode(':', $normalized, 2);

        if (! in_array($type, ['booking', 'leave', 'dayoff'], true)) {
            return null;
        }

        $id = (int) $rawId;

        if ($id <= 0) {
            return null;
        }

        return [
            'type' => $type,
            'id' => $id,
        ];
    }

    protected function editActionNameForType(string $type): string
    {
        return match ($type) {
            'booking' => 'editBooking',
            'leave' => 'editTherapistLeave',
            'dayoff' => 'editDayOff',
            default => 'editBooking',
        };
    }

    protected function deleteActionNameForType(string $type): string
    {
        return match ($type) {
            'booking' => 'deleteBooking',
            'leave' => 'deleteTherapistLeave',
            'dayoff' => 'deleteDayOff',
            default => 'deleteBooking',
        };
    }

    protected function bookingColor(string $status): string
    {
        return match ($status) {
            'pending' => '#fbbf24',
            'confirmed' => '#60a5fa',
            'canceled' => '#f87171',
            'completed' => '#4ade80',
            'expired' => '#9ca3af',
            default => '#9ca3af',
        };
    }
}
