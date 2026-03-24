<?php

namespace App\Filament\Widgets;

use App\Filament\Actions\BookingActions;
use App\Filament\Resources\BookingPayments\BookingPaymentResource;
use App\Filament\Resources\Bookings\Schemas\BookingForm;
use App\Models\Branch;
use App\Models\Booking;
use App\Models\DayOff;
use App\Models\TherapistLeave;
use App\Services\BusinessSettings;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;
use Filament\Widgets\Widget;
use Guava\Calendar\Contracts\ContextualInfo;
use Guava\Calendar\Enums\Context;
use Guava\Calendar\Enums\CalendarViewType;
use Guava\Calendar\Filament\Actions\CreateAction;
use Guava\Calendar\Filament\CalendarWidget as FilamentCalendarWidget;
use Guava\Calendar\ValueObjects\DateClickInfo;
use Guava\Calendar\ValueObjects\DateSelectInfo;
use Guava\Calendar\ValueObjects\FetchInfo;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;
use Guava\Calendar\ValueObjects\EventDropInfo;
use Illuminate\Database\Eloquent\Model;
use Livewire\Form;
use Filament\Forms\Components\Select;

class CalendarWidget extends FilamentCalendarWidget
{

    use HasWidgetShield;
    // protected bool $dayMaxEvents = true;
    protected bool $useFilamentTimezone = true;
    protected bool $eventResizeEnabled = true;
    // protected CalendarViewType $calendarView = CalendarViewType::ListDay;
    protected bool $eventClickEnabled = true;
    protected bool $dateClickEnabled  = true;
    // protected bool $dateSelectEnabled = true;
    // protected bool $eventResizeEnabled = true;


    protected bool $dateSelectEnabled = true;
    public ?int $branchFilter = null;


    // public function getHeaderActions(): array
    // {
    //     return [
    //         Action::make('Create Booking')
    //             ->schema(
    //                 BookingForm::schema()
    //             ) 
    //     ];
    // }  
    protected CalendarViewType $calendarView = CalendarViewType::TimeGridWeek;
    protected function getHeader(): ?\Illuminate\Contracts\View\View
    {
        return view('filament.widgets.calendar-legend');
    }


    public function mount(): void
    {
        $this->calendarView = CalendarViewType::tryFrom(
            session('calendar_view')
        ) ?? CalendarViewType::TimeGridWeek;

        if ($this->usesBranches()) {
            $this->branchFilter = session('calendar_branch_filter')
                ? (int) session('calendar_branch_filter')
                : app(BusinessSettings::class)->getDefaultBranchId();
        } else {
            $this->branchFilter = null;
        }
    }

    protected $listeners = ['updateUserOverview' => '$refresh'];

    public function setView(CalendarViewType $view)
    {
        session([
            'calendar_view' => $view->value,
        ]);

        // full page reload
        return redirect(request()->header('Referer'));
    }

    public function setBranchFilter(?int $branchId)
    {
        $this->branchFilter = $branchId;
        session([
            'calendar_branch_filter' => $branchId,
        ]);

        return redirect(request()->header('Referer'));
    }


      
    public function getHeaderActions(): array
    {
        $actions = [
            Action::make('calendar_view_select')
                ->label('Calendar View')
                ->fillForm([
                    'calendar_view' => session('calendar_view'), // Use fillForm instead of default
                ])
                ->schema([
                    Select::make('calendar_view')
                        ->label('View')
                        ->default(session('calendar_view'))
                        ->options([
                            'dayGridMonth' => 'Month Grid',
                            'listDay' => 'Day List',
                            'listWeek' => 'Week List',
                            'listMonth' => 'Month List',
                            'listYear' => 'Year List',
                            // 'resourceTimeGridDay' => 'Resource Day Grid',
                            // 'resourceTimeGridWeek' => 'Resource Week Grid',
                            // 'resourceTimelineDay' => 'Resource Day Timeline',
                            // 'resourceTimelineWeek' => 'Resource Week Timeline',
                            // 'resourceTimelineMonth' => 'Resource Month Timeline',
                            'timeGridDay' => 'Day Grid',
                            'timeGridWeek' => 'Week Grid',
                        ])
                        ->required()
                        ->live()
                        ->afterStateUpdated(fn($state) => $this->setView(CalendarViewType::tryFrom($state)))
                ])
                ->action(function (array $data) {
                    // Action is handled by the live afterStateUpdated
                })
                ->modalHeading('Select Calendar View')
                ->modalSubmitActionLabel('Close'),
        ];

        if ($this->usesBranches()) {
            $branchOptions = Branch::query()->orderBy('name')->pluck('name', 'id')->toArray();

            $actions[] = Action::make('calendar_branch_select')
                ->label('Branch')
                ->fillForm([
                    'branch_id' => $this->branchFilter,
                ])
                ->schema([
                    Select::make('branch_id')
                        ->label('Branch')
                        ->options($branchOptions)
                        ->live()
                        ->afterStateUpdated(fn ($state) => $this->setBranchFilter($state ? (int) $state : null)),
                ])
                ->action(function (array $data) {
                    // Action is handled by the live afterStateUpdated
                })
                ->modalHeading('Select Branch')
                ->modalSubmitActionLabel('Close');
        }

        return $actions;
    }

    protected function getCalendarConfig(): array
    {
        return [
            'initialView' => $this->calendarView->value,
            'headerToolbar' => false,
        ];
    }



    public function editBookingAction(): EditAction
    {
        return $this->editAction(\App\Models\Booking::class)
            ->label('Edit Booking')

            ->extraModalFooterActions([
                Action::make('saveAndCreateAnother')
                    ->label('Save & Add Another')
                    ->color('gray')
                    ->action(function (array $data) {
                        // Custom logic here
                    }),
            ])
            ->extraModalFooterActions([
                BookingActions::complete(),
                BookingActions::confirm(),
                BookingActions::cancel(),
                BookingActions::makePayment()
            ])
            ->after(fn() => $this->refreshRecords());
    }

    public function createFollowupBookingAction(): CreateAction
    {
        return $this->createAction(\App\Models\Booking::class)
            ->label('Add followup Booking')
            ->extraModalFooterActions([
                Action::make('saveAndCreateAnother')
                    ->label('Save & Add Another')
                    ->color('gray')
                    ->action(function (array $data) {
                        // Custom logic here
                    }),
            ])->mountUsing(function (Schema $form, $arguments) {
                $form->fill($arguments);
                // ...
            })
            ->after(fn() => $this->refreshRecords());
    }



    public function createLeavesAction(): CreateAction
    {
        return $this->createAction(\App\Models\TherapistLeave::class)
            ->label('Create Leave')
            ->mountUsing(function (Schema $form, ?ContextualInfo $info): void {
                if ($info instanceof DateClickInfo) {
                    $form->fill([
                        'start_date' => $info->date->toDateTimeString(),
                        'end_date' => $info->date->copy()->endOfDay()->toDateTimeString(),
                    ]);

                    return;
                }

                if ($info instanceof DateSelectInfo || $info?->getContext() === Context::DateSelect) {
                    $start = $info->start;
                    $end = $info->end->copy()->subSecond();

                    if ($end->lt($start)) {
                        $end = $start->copy();
                    }

                    $form->fill([
                        'start_date' => $start->toDateTimeString(),
                        'end_date' => $end->toDateTimeString(),
                    ]);

                    return;
                }

                $form->fill();
            })
            ->extraModalFooterActions([
                Action::make('saveAndCreateAnother')
                    ->label('Save & Add Another')
                    ->color('gray')
                    ->action(function (array $data) {
                        // Custom logic here
                    }),
            ])
            ->after(fn() => $this->refreshRecords());
    }

    public function createDayOffAction(): CreateAction
    {
        return $this->createAction(\App\Models\DayOff::class)
            ->label('Create Day Off')
            ->mountUsing(function (Schema $form, ?ContextualInfo $info): void {
                if ($info instanceof DateClickInfo) {
                    $date = $info->date->toDateString();

                    $form->fill([
                        'start_date' => $date,
                        'end_date' => $date,
                        'status' => 'approved',
                    ]);

                    return;
                }

                if ($info instanceof DateSelectInfo || $info?->getContext() === Context::DateSelect) {
                    $start = $info->start->copy()->startOfDay();
                    $end = $info->end->copy()->subSecond()->endOfDay();

                    if ($end->lt($start)) {
                        $end = $start->copy()->endOfDay();
                    }

                    $form->fill([
                        'start_date' => $start->toDateString(),
                        'end_date' => $end->toDateString(),
                        'status' => 'approved',
                    ]);

                    return;
                }

                $form->fill();
            })
            ->extraModalFooterActions([
                Action::make('saveAndCreateAnother')
                    ->label('Save & Add Another')
                    ->color('gray')
                    ->action(function (array $data) {
                        // Custom logic here
                    }),
            ])
            ->after(fn() => $this->refreshRecords());
    }
    
    public function createBookingAction(): CreateAction
    {
        return $this->createAction(\App\Models\Booking::class)
            ->label('Create Booking')
            ->mountUsing(function (array $arguments, Form $form) {
                $form->fill([
                    'branch_id'    => $this->usesBranches() ? ($arguments['branch_id'] ?? $this->branchFilter ?? app(BusinessSettings::class)->getDefaultBranchId()) : null,
                    'customer_id'  => $arguments['customer_id'] ?? null,
                    'listing_id'   => $arguments['listing_id'] ?? null,
                    'therapist_id' => $arguments['therapist_id'] ?? null,
                ]); 
            })
           ->fillForm(function (?ContextualInfo $info) {
                // You can now access contextual info from the calendar using the $info argument
                if ($info instanceof DateClickInfo) {
                    return [
                        'start_time' => $info?->date?->toDateTimeString(),
                        'end_time'   => $info?->date?->toDateTimeString(),
                        'selected_date' => $info?->date?->toDateString()
                    ];
                }
            })
            ->extraModalFooterActions([
                Action::make('saveAndCreateAnother')
                    ->label('Save & Add Another')
                    ->color('gray')
                    ->action(function (array $data) {
                        // Custom logic here
                    }),
            ])
            // ->extraModalFooterActions([
            //     Action::make('Confirm Booking')
            //     ->color(Color::Blue)
            //     ->visible(function($record){
            //         return $record->status == 'pending';
            //     })->action(function($record){
            //         $record->status = 'confirmed';
            //         $record->save();
            //     }),
            //     Action::make('Cancel Booking')
            //     ->visible(function($record){
            //         return $record->status == 'pending' || $record->status == 'confirmed';
            //     })->action(function($record){
            //         $record->status = 'canceled';
            //         $record->save();
            //     })
            //     ->color('danger'),
            //     // ViewAction::make(),
            //     Action::make('Make Payment')
            //         ->schema(BookingPaymentResource::schema())
            //         ->visible(function ($record) {
            //             return $record->canAddPayment();
            //         })
            //         ->action(function ($record, array $data): void {
            //             // ...
            //             $data['payment_status'] = 'paid';
            //             $record->payments()->create($data);

            //             $totalPaid = $record->totalPayment();
            //             $bookingPrice = $record->price;

            //             if ($totalPaid < $bookingPrice) {
            //                 $record->update(['payment_status' => 'partially_paid']);
            //             } else {
            //                 $record->update(['payment_status' => 'paid']);
            //             }
            //         })->after(function () {
            //             $this->dispatch('paymentsRelationManager');
            //         }),


            //     DeleteAction::make(),
            // ])
            ->after(fn() => $this->refreshRecords());
    }

    private function usesBranches(): bool
    {
        return app(BusinessSettings::class)->usesBranches();
    }

    protected function getDateClickContextMenuActions(): array
    {
        return [
            $this->createBookingAction(),
            $this->createLeavesAction(),
            $this->createDayOffAction(),
            // Any other action you want
        ];
    }
    
    protected function getDateSelectContextMenuActions(): array
    {
        return [
            $this->createLeavesAction(),
            $this->createDayOffAction(),
        ];
    }

    public function createFooAction(): CreateAction
    {
        // You can use our helper method
        // return $this->createAction(Booking::class);

        // Or you can add it manually, both variants are equivalent:
        return CreateAction::make('createFoo')
            ->model(Booking::class);
    }


    protected function onEventDrop(EventDropInfo $info, Model $event): bool
    {
        // Access the updated dates using getter methods
        $newStart = $info->event->getStart();
        $newEnd = $info->event->getEnd();

        // Update the event with the new start/end dates to persist the drag & drop
        $event->update([
            'start_time' => $newStart,
            'end_time' => $newEnd,
        ]);
        // Return true to accept the drop and keep the event in the new position
        return true;
    }
    protected function getEvents(FetchInfo $info): Collection
    {
        $companyId = Filament::getTenant()?->getKey() ?? auth('web')->user()?->company_id;

        return Booking::whereBetween('start_time', [$info->start, $info->end])
            ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->when($this->branchFilter, fn ($q) => $q->where('branch_id', $this->branchFilter))
            ->get()
            ->merge(
                TherapistLeave::whereBetween('start_date', [$info->start, $info->end])
                    ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
                    ->when($this->branchFilter, fn ($q) => $q->whereHas('therapist', fn ($therapistQuery) => $therapistQuery->where('branch_id', $this->branchFilter)))
                    ->get()
            )
            ->merge(
                DayOff::overlapping($info->start, $info->end)
                    ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
                    ->get()
            );
    }




    public function editEventAction(): EditAction
    {

        return $this->editAction(fn($record) => $this->getEditModel($record))
            ->label(fn($record) => $this->getEditLabel($record))
            ->extraModalFooterActions(fn($record) => $this->getEditFooterActions($record))
            ->after(fn($livewire) => $this->safeRefresh($livewire));
    }
    protected function getEditModel($event): string
    {
        return $event instanceof Booking
            ? Booking::class
            : ($event instanceof DayOff
                ? DayOff::class
                : TherapistLeave::class);
    }

    protected function getEditLabel($event): string
    {
        return $event instanceof Booking
            ? 'Edit Booking'
            : ($event instanceof DayOff
                ? 'Edit Day Off'
                : 'Edit Leave');
    }

    protected function getEditFooterActions($event): array
    {
        if (! $event instanceof Booking) {
            return [];
        }

        return [
            BookingActions::complete(),
            BookingActions::confirm(),
            BookingActions::cancel(),
            BookingActions::makePayment(),
        ];
    }

    protected function safeRefresh($livewire): void
    {
        if (method_exists($livewire, 'refreshRecords')) {
            $livewire->refreshRecords();
            return;
        }

        // fallback for pages / non-widget contexts
        $livewire?->dispatch('refresh');
    }


    protected function getEventClickContextMenuActions(): array
    {
        return [

            $this->editEventAction(),
            // $this->editLeaveAction(),
            // $this->editBookingAction(),
            $this->deleteAction(),
        ];
    }

}





