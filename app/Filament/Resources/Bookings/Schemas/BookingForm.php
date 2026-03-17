<?php

namespace App\Filament\Resources\Bookings\Schemas;

use App\Filament\Resources\Customers\Schemas\CustomerForm;
use App\Models\Branch;
use App\Models\Bed;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Listing;
use App\Models\Therapist;
use App\PaymentStatus;
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
                        ->options(Branch::query()->orderBy('name')->pluck('name', 'id'))
                        ->default(fn ($record) => $record?->branch_id ?? app(\App\Services\BusinessSettings::class)->getDefaultBranchId())
                        ->required()
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
                        ->afterStateHydrated(function ($state, callable $set, callable $get) {
                            $startTime = $get('start_time');

                            if ($startTime) {
                                $date = $startTime instanceof Carbon ? $startTime : Carbon::parse($startTime);
                                $set('selected_date', $date->toDateString());
                            }
                        })
                        ->required()
                        ->reactive(),

                    ToggleButtons::make('available_timeslots')
                        ->hidden(function (callable $get) {
                            return $get('selected_date') == null;
                        })
                        ->options(function (callable $get) {
                            $date = $get('selected_date');
                            $branchId = $get('branch_id');

                            if (! $date || ! $branchId) {
                                return [];
                            }

                            return Booking::availableTimeslots($date, $branchId);
                        })->disableOptionWhen(function (string $value, callable $get, $record = null) {

                            $date = $get('selected_date');
                            $branchId = $get('branch_id');

                            if (! $date || ! $branchId) {
                                return true;
                            }

                            [$slotStartStr, $slotEndStr] = explode(' - ', $value);

                            $slotStart = Carbon::parse("$date $slotStartStr");
                            $slotEnd   = Carbon::parse("$date $slotEndStr");

                            // Get all therapists in this branch
                            $therapists = Therapist::where('branch_id', $branchId)
                                ->active()
                                ->get();

                            // Check if ANY therapist is available
                            foreach ($therapists as $therapist) {

                                $hasConflict = $therapist->bookings()
                                    ->confirmed()
                                    // ->completed()
                                    ->whereDate('start_time', $date)
                                    ->when($record, fn($q) => $q->where('id', '!=', $record->id))
                                    ->where(function ($q) use ($slotStart, $slotEnd) {
                                        $q->where('start_time', '<', $slotEnd)
                                            ->where('end_time', '>', $slotStart);
                                    })
                                    ->exists();

                                if (!$hasConflict) {
                                    // Therapist is free → timeslot should be allowed
                                    return false;
                                }
                            }

                            // No therapist is available → disable
                            return true;
                        })

                        ->afterStateHydrated(function ($state, callable $set, callable $get, $record) {
                            if (!$record || !$record->start_time || !$record->end_time) return;

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
                        ->required()
                        ->dehydrated(false)
                        ->validatedWhenNotDehydrated(false)
                        ->reactive(),
                    Select::make('therapist_id')
                        ->label(business_label('staff', 'Therapist'))
                        ->relationship('therapist', 'name')
                        ->options(function (callable $get) {
                            $branchId = $get('branch_id');

                            return Therapist::active()
                                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                                ->pluck('name', 'id');
                        })
                        ->disabled(fn (callable $get) => ! $get('branch_id'))
                        ->disableOptionWhen(function ($value, callable $get, $record = null) {
                            $date = $get('selected_date');
                            $start = $get('start_time');
                            $end = $get('end_time');
                            $branchId = $get('branch_id');

                            if (!$date || !$start || !$end || !$branchId) return false;

                            $therapist = Therapist::active()
                                ->where('branch_id', $branchId)
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
                        ->required(fn($record) => $record === null)
                        ->dehydrated(false)
                        ->validatedWhenNotDehydrated(false)
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
                        ->disabled(fn (callable $get) => ! $get('branch_id'))
                        ->disableOptionWhen(function ($value, callable $get, $record = null) {
                            $date = $get('selected_date');
                            $start = $get('start_time');
                            $end = $get('end_time');
                            $branchId = $get('branch_id');

                            if (!$date || !$start || !$end || !$branchId) return false;

                            $bed = Bed::available()
                                ->where('branch_id', $branchId)
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
