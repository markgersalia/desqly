<?php

namespace App\Filament\Resources\Bookings\Schemas;

use App\Filament\Resources\Customers\Schemas\CustomerForm;
use App\Models\Branch;
use App\Models\Bed;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\DayOff;
use App\Models\Listing;
use App\Models\Therapist;
use App\PaymentStatus;
use App\Services\BusinessSettings;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Storage;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Icon;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Support\Colors\Color;
use Illuminate\Support\Str;


class BookingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(self::schema())->columns(3);
    }


    public static function schema($type = null): array
    {
        $businessSettings = app(BusinessSettings::class);
        $usesBranches = $businessSettings->usesBranches();
        $requiresStaff = $businessSettings->requiresStaffAssignment();
        $isWholeDayMode = config('booking.mode', 'time_slot') === 'whole_day';
        $dayStart = (string) config('booking.day_start', '09:00');
        $dayEnd = (string) config('booking.day_end', '17:00');

        return [
            Group::make([
                Section::make('Booking Status')->schema([

                    TextEntry::make('status')
                        ->badge()
                        ->color(fn($state) => match ($state) {
                            'pending' => 'warning',
                            'approved' => 'success',
                            'canceled' => 'danger',
                            'confirmed' => 'info',
                            'completed' => 'success',
                            default => 'gray',
                        }),

                    // Select::make('status')
                    //     ->options([
                    //         'pending' => 'Pending',
                    //         'confirmed' => 'Confirmed',
                    //         'canceled' => 'Canceled',
                    //         'completed' => 'Completed',
                    //     ])

                    //     ->afterStateHydrated(function ($state, callable $set) {
                    //         if (!$state) {
                    //             $set('status', 'pending');
                    //         }
                    //     })
                    //     ->columnSpan(1)
                    //     ->required(),
                    TextEntry::make('payment_status')
                        // ->badge()
                        ->formatStateUsing(function ($state, $record) {
                            $status = Str::of($state)->replace('_', ' ')->title();
                            $balance = number_format($record->balance() ?? 0, 2);

                            return "{$status} (₱{$balance} Balance)";
                        })

                        ->color(fn(string $state): string => match ($state) {
                            'partially_paid' => 'danger',
                            'pending' => 'warning',
                            'paid' => 'success',
                            'failed' => 'danger',
                        }),
                    // TextEntry::make('payment_status')->value('Asdasd'),
                    // ->options(PaymentStatus::class)
                    // ->afterStateHydrated(function ($state, callable $set) {
                    //     if (!$state) {
                    //         $set('status', 'pending');
                    //     }
                    // })
                    // ->columnSpan(1) 
                ])->columns(2)
                    ->visible(function ($record) {
                        return $record?->id;
                    }),
                Section::make('Booking Information')->schema([
                    TextInput::make('booking_number')
                        ->label('Booking Number')
                        ->readOnly()
                        ->afterStateHydrated(function ($state, callable $set) {
                            // Only populate if the field is empty
                            if (!$state) {
                                $latest = \App\Models\Booking::latest('id')->first();
                                $nextNumber = $latest ? $latest->id + 1 : 1;

                                $set('booking_number', 'BK-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT));
                            }
                        }),



                    Group::make([
                        TextInput::make('title')
                            ->label('Booking Title')
                            ->helperText('Enter a short descriptive title for this booking.')
                            ->required(),

                        TextInput::make('type')
                            ->label('Booking Type')
                            ->helperText('Start typing and you will see suggestions, but you can type a custom type too.')
                            ->datalist([
                                'Room',
                                'Service',
                                'Event',
                                'Meeting',
                                'Consultation',
                                'Appointment',
                            ])
                            ->required(),
                        TextInput::make('price')
                            ->label('Booking Price')
                            ->columnSpanFull()
                            ->numeric()
                            ->helperText('Set the price for this booking. '),
                    ])->columns(2)
                        ->hidden(config('booking.has_listings')),
                ]),
                Section::make([
                    Select::make('branch_id')
                        ->label('Branch')
                        ->visible($usesBranches)
                        ->options(Branch::query()->orderBy('name')->pluck('name', 'id'))
                        ->default(fn ($record) => $usesBranches ? ($record?->branch_id ?? app(BusinessSettings::class)->getDefaultBranchId()) : null)
                        ->required($usesBranches)
                        ->searchable()
                        ->preload()
                        ->reactive()
                        ->afterStateUpdated(function (callable $set) {
                            $set('therapist_id', null);
                            $set('bed_id', null);
                            $set('available_timeslots', null);
                        })
                        ->columnSpanFull(),

                    Select::make('customer_id')
                        ->relationship(name: 'customer', titleAttribute: 'name')
                        ->options(function($q){
                            $customers = Customer::All();
                            $customerOptions = [];
                            foreach ($customers as $key => $customer) {
                                $customerOptions[$customer->id] = $customer->displayNameWithStatus();
                            }
                            return $customerOptions;
                        })
                        ->hidden($type == "customers")
                        ->searchable()
                        ->createOptionForm(
                            CustomerForm::schema()
                        )
                        ->columnSpanFull()
                        ->helperText("If selected is VIP, the booking will automatically set to confirmed")
                        ->required(),

                    Select::make('listing_id')
                        ->label(business_label('service', 'Service'))
                        ->hidden($type == "listings")
                        ->relationship(name: 'listing', titleAttribute: 'title')
                        ->searchable()
                        ->hidden(!config('booking.has_listings'))
                        ->options(Listing::query()->pluck('title', 'id'))
                        ->loadingMessage('Loading listings...')
                        ->reactive() // make it reactive to trigger callbacks
                        ->afterStateUpdated(function ($state, callable $set) {
                            $listing = Listing::find($state);
                            if ($listing) {
                                $set('price', $listing->price); // update the price field dynamically
                            } else {
                                $set('price', null);
                            }
                        })
                        ->columnSpanFull(),
                    Hidden::make('start_time'),
                    Hidden::make('end_time'),

                    TextInput::make('price')
                        ->label('Booking Price')

                        ->columnSpanFull()
                        ->numeric()
                        ->hidden(!config('booking.has_listings'))
                        ->helperText('Set the price for this booking. '),
                    Textarea::make('notes')
                        ->columnSpanFull(),
                ])->columns(2),

            ])->columnSpan(2)

                ->disabled(function ($record) {
                    return false;
                    if (!$record) {
                        return false;
                    }
                    return $record->status == 'confirmed' || $record->status == 'completed';
                }),
            Group::make([
                Section::make([
                    DatePicker::make('selected_date')
                        ->label('Select Date')
                        ->afterStateHydrated(function ($state, callable $set, callable $get) use ($isWholeDayMode, $dayStart, $dayEnd) {
                            $startTime = $get('start_time');

                            if ($startTime) {
                                $date = $startTime instanceof Carbon ? $startTime : Carbon::parse($startTime);
                                $set('selected_date', $date->toDateString());
                            }

                            $selectedDate = $get('selected_date');
                            if ($isWholeDayMode && $selectedDate) {
                                $set('start_time', Carbon::parse("{$selectedDate} {$dayStart}")->toDateTimeString());
                                $set('end_time', Carbon::parse("{$selectedDate} {$dayEnd}")->toDateTimeString());
                                $set('available_timeslots', null);
                            }
                        })
                        ->afterStateUpdated(function ($state, callable $set) use ($isWholeDayMode, $dayStart, $dayEnd) {
                            if (! $isWholeDayMode) {
                                return;
                            }

                            if (! $state) {
                                $set('start_time', null);
                                $set('end_time', null);
                                $set('available_timeslots', null);
                                return;
                            }

                            $set('start_time', Carbon::parse("{$state} {$dayStart}")->toDateTimeString());
                            $set('end_time', Carbon::parse("{$state} {$dayEnd}")->toDateTimeString());
                            $set('available_timeslots', null);
                            $set('therapist_id', null);
                        })
                        ->required()
                        ->reactive()
                        ->rule(function (callable $get) use ($usesBranches) {
                            return function (string $attribute, $value, \Closure $fail) use ($get, $usesBranches): void {
                                if (! $value) {
                                    return;
                                }

                                $branchId = $usesBranches ? $get('branch_id') : null;
                                $companyId = $branchId
                                    ? Branch::query()->whereKey($branchId)->value('company_id')
                                    : null;

                                if (DayOff::isDateOff((string) $value, $companyId)) {
                                    $fail('Selected date falls on a configured day off.');
                                }
                            };
                        }),

                    ToggleButtons::make('available_timeslots')
                        ->hidden(function (callable $get) use ($isWholeDayMode) {
                            return $isWholeDayMode || $get('selected_date') == null;
                        })
                        ->options(function (callable $get) use ($usesBranches, $isWholeDayMode) {
                            $date = $get('selected_date');
                            $branchId = $get('branch_id');

                            if (! $date) {
                                return [];
                            }

                            if ($usesBranches && ! $branchId) {
                                return [];
                            }

                            if ($isWholeDayMode) {
                                return [];
                            }

                            return Booking::availableTimeslots($date, $branchId ?: null);
                        })->disableOptionWhen(function (string $value, callable $get, $record = null) use ($usesBranches, $requiresStaff) {

                            $date = $get('selected_date');
                            $branchId = $get('branch_id');

                            if (! $date) {
                                return true;
                            }

                            if ($usesBranches && ! $branchId) {
                                return true;
                            }

                            [$slotStartStr, $slotEndStr] = explode(' - ', $value);

                            $slotStart = Carbon::parse("$date $slotStartStr");
                            $slotEnd   = Carbon::parse("$date $slotEndStr");

                            if (! $requiresStaff) {
                                return Booking::query()
                                    ->confirmed()
                                    ->whereDate('start_time', $date)
                                    ->when($usesBranches && $branchId, fn ($query) => $query->where('branch_id', $branchId))
                                    ->when($record, fn ($query) => $query->where('id', '!=', $record->id))
                                    ->where(function ($query) use ($slotStart, $slotEnd) {
                                        $query->where('start_time', '<', $slotEnd)
                                            ->where('end_time', '>', $slotStart);
                                    })
                                    ->exists();
                            }

                            $therapists = Therapist::query()
                                ->active()
                                ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
                                ->get();

                            foreach ($therapists as $therapist) {
                                $hasConflict = $therapist->bookings()
                                    ->confirmed()
                                    ->whereDate('start_time', $date)
                                    ->when($record, fn ($query) => $query->where('id', '!=', $record->id))
                                    ->where(function ($query) use ($slotStart, $slotEnd) {
                                        $query->where('start_time', '<', $slotEnd)
                                            ->where('end_time', '>', $slotStart);
                                    })
                                    ->exists();

                                if (! $hasConflict) {
                                    return false;
                                }
                            }

                            return true;
                        })

                        ->afterStateHydrated(function ($state, callable $set, callable $get, $record) use ($isWholeDayMode) {
                            if ($isWholeDayMode || !$record || !$record->start_time || !$record->end_time) return;

                            $start = Carbon::parse($record->start_time)->format('h:i a');
                            $end   = Carbon::parse($record->end_time)->format('h:i a');

                            $set('available_timeslots', "$start - $end");
                        })

                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                            if (!$state) {
                                // if cleared, also clear start/end and therapist selection
                                $set('start_time', null);
                                $set('end_time', null);
                                $set('therapist_id', null);
                                return;
                            }

                            [$start, $end] = explode(' - ', $state);

                            // NOTE: use the same date key used by this toggle -> selected_date
                            $date = $get('selected_date');
                            if (!$date) return;

                            // set start_time/end_time as full datetimes (consistent with your DB)
                            $set('start_time', Carbon::parse("$date $start")->toDateTimeString());
                            $set('end_time',   Carbon::parse("$date $end")->toDateTimeString());

                            // IMPORTANT: clear previously chosen therapist so select reloads
                            $set('therapist_id', null);
                        })
                        ->required(! $isWholeDayMode)
                        ->dehydrated(false)
                        ->validatedWhenNotDehydrated(false)
                        ->reactive(),
                    Select::make('therapist_id')
                        ->label(business_label('staff', 'Therapist'))
                        ->relationship('therapist', 'name')
                        ->options(function (callable $get) use ($requiresStaff) {
                            if (! $requiresStaff) {
                                return [];
                            }

                            $branchId = $get('branch_id');

                            return Therapist::active()
                                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                                ->pluck('name', 'id');
                        })
                        ->visible($requiresStaff)
                        ->disabled(fn (callable $get) => $requiresStaff && $usesBranches && ! $get('branch_id'))
                        ->disableOptionWhen(function ($value, callable $get, $record = null) use ($usesBranches, $requiresStaff) {
                            if (! $requiresStaff) {
                                return false;
                            }

                            $date = $get('selected_date');
                            $start = $get('start_time');
                            $end = $get('end_time');
                            $branchId = $get('branch_id');
                            $companyId = $branchId
                                ? Branch::query()->whereKey($branchId)->value('company_id')
                                : null;

                            if (!$date || !$start || !$end || ($usesBranches && !$branchId)) return false;
                            
                            if (DayOff::isDateOff((string) $date, $companyId)) {
                                return true;
                            }

                            $therapist = Therapist::active()
                                ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
                                ->find($value);

                            if (! $therapist) {
                                return true;
                            }

                            if ($therapist->isOnLeave($start, $end)) {
                                return true;
                            }

                            // Exclude current booking ID from availability check
                            return !$therapist?->isAvailable($date, $start, $end, $record?->id);
                        })
                        // ->hidden(fn(callable $get) => $get('available_timeslots') == null)
                        ->preload()
                        ->required($requiresStaff)
                        ->dehydrated($requiresStaff)
                        ->reactive(),
                    Select::make('bed_id')
                        
                        ->label(business_label('resource', 'Bed'))
                        ->relationship('bed', 'name')
                        ->options(function (callable $get) {
                            $branchId = $get('branch_id');

                            return Bed::available()
                                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                                ->pluck('name', 'id');
                        })
                        ->disabled(fn (callable $get) => $usesBranches && ! $get('branch_id'))
                        ->disableOptionWhen(function ($value, callable $get, $record = null) use ($usesBranches) {
                            $date = $get('selected_date');
                            $start = $get('start_time');
                            $end = $get('end_time');
                            $branchId = $get('branch_id');

                            if (!$date || !$start || !$end || ($usesBranches && !$branchId)) return false;

                            $bed = Bed::available()
                                ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
                                ->find($value);

                            if (! $bed) {
                                return true;
                            }

                            // Exclude current booking ID from availability check
                            return !$bed?->isAvailable($date, $start, $end, $record?->id);
                        })
                        // ->hidden(fn(callable $get) => $get('available_timeslots') == null)
                        ->preload()
                        ->visible(config('booking.requires_bed'))
                        // ->required(fn($record) => $record === null)
                        // ->dehydrated(fal se)
                        ->validatedWhenNotDehydrated(false)

                        ->reactive(),

                ]),


            ])->columnSpan(1)
                // ->disabled(function ($record) {
                //     if (!$record) {
                //         return false;
                //     }
                //     return $record->status == 'confirmed' || $record->status == 'completed';
                // })
        ];
    }




    public static function postAssessmentWizard(): array
    {
        return [
            Step::make('Session Details')
                ->schema([
                    TextInput::make('primary_concern')
                        ->columnSpanFull(),

                    Select::make('listing_id')
                        ->default(fn($record) => $record?->listing_id)
                        ->relationship('listing', 'title')
                        ->columnSpanFull(),

                    DateTimePicker::make('start_time')
                        ->default(fn($record) => $record?->start_time),

                    DateTimePicker::make('end_time')
                        ->default(fn($record) => $record?->end_time),
                ])
                ->columns(2),

            Step::make('Vitals')
                ->schema([
                    TextInput::make('bp')->placeholder('120/80'),
                    TextInput::make('pr')->numeric(),
                    TextInput::make('o2')->numeric(),
                ])
                ->columns(3),

            Step::make('Therapist Evaluation')
                ->schema([
                    Select::make('therapist_id')
                        ->relationship('therapist', 'name')
                        ->default(function ($record) {
                            return $record?->therapist_id;
                        }),

                    Select::make('therapist_rating')
                        ->options([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]),

                    Select::make('post_pain_rating')
                        ->options([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]),
                ]),

            Step::make('Remarks')
                ->schema([
                    Toggle::make('require_followup')
                        ->label('Require Follow-up Session')
                        ->default(false)
                        ->reactive(),

                    DatePicker::make('next_session_date')
                        ->label('Next Session Date')
                        ->reactive()
                        ->visible(fn($get) => $get('require_followup') === true),

                    Textarea::make('client_remarks')
                        ->rows(4)
                        ->columnSpanFull(),
                ]),

        ];
    }
}






