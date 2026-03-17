<?php

namespace App\Filament\Resources\Invoices\Schemas;

use App\Models\Rental;
use App\Models\Invoice;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use Coolsam\Flatpickr\Forms\Components\Flatpickr;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Schema;

use Filament\Schemas\Components\Section;

class InvoiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make()->columnSpan(2)
                    ->schema([
                        Section::make('Billing Information')
                            ->schema([

                                Select::make('customer_id')
                                    ->label('Customer')
                                    ->relationship('customer', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->columnSpan(1),
                                Select::make('booking_id')
                                    ->label('Booking')
                                    ->relationship('booking', 'booking_number')
                                    ->searchable()
                                    ->preload()->reactive() // make it reactive to trigger callbacks
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $booking = Booking::find($state);
                                        if ($booking) {
                                            // Set repeater default or initial values
                                            $set('items', [
                                                [
                                                    'name' => 'Booking Price',
                                                    'price_per_unit' => $booking->price,
                                                    'quantity' => 1,
                                                    'total' => $booking->price,
                                                ],
                                            ]);

                                            // Update overall amount
                                            $set('amount', $booking->price);
                                        } else {
                                            $set('items', []);
                                            $set('amount', 0);
                                        }
                                    })

                                    ->columnSpan(1),
                            ])
                            ->columns(2)
                            ->collapsible(),


                        Section::make([

                            Repeater::make('items')
                                ->label("Additional Items")
                                ->reactive() // important to trigger afterStateUpdated
                                ->schema([
                                    TextInput::make('name')
                                        ->label('Item Name')
                                        ->required()
                                        ->placeholder('e.g., Rent')
                                        ->columnSpan(2),

                                    TextInput::make('price_per_unit')
                                        ->label('Price Per Unit')
                                        ->numeric()
                                        ->required()
                                        ->step(0.01)
                                        ->prefix('₱')
                                        ->columnSpan(1)
                                        ->live(debounce: 500)
                                        ->reactive()
                                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                            $quantity = $get('quantity') ?: 1;
                                            $set('total', $state * $quantity);
                                        }),

                                    TextInput::make('quantity')
                                        ->label('Quantity')
                                        ->numeric()
                                        ->required()
                                        ->default(1)
                                        ->minValue(1)
                                        ->columnSpan(1)
                                        ->reactive()
                                        ->live(debounce: 500)
                                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                            $pricePerUnit = $get('price_per_unit') ?: 0;
                                            $set('total', $pricePerUnit * $state);
                                        }),

                                    TextInput::make('total')
                                        ->label('Total')
                                        ->numeric()
                                        ->dehydrated(false)
                                        ->prefix('₱')
                                        ->columnSpan(1),
                                ])
                                ->columns(5)
                                ->reactive() // ensures top-level afterStateUpdated triggers
                                ->afterStateUpdated(function ($state, callable $set) {
                                    $total = collect($state ?: [])->sum(function ($item) {
                                        return ((float)($item['price_per_unit'] ?? 0)) * ((float)($item['quantity'] ?? 1));
                                    });
                                    $set('amount', $total);
                                })
                                ->defaultItems(1)
                                ->collapsible()
                                ->cloneable()
                                ->columnSpanFull()
                                ->addActionLabel('Add Item'),
                            TextInput::make('amount')
                                ->label('Total Amount')
                                ->numeric()
                                ->prefix('₱')
                                ->reactive() 
                                ->dehydrated(true)
                        ])
                                ->compact()
                            ->columnSpanFull(),
                    ]),
                Group::make()
                    ->schema([
                        Section::make('')
                            ->schema([

                                Select::make('status')
                                    ->options(['pending' => 'Pending', 'paid' => 'Paid', 'cancelled' => 'Cancelled'])
                                    ->default('pending')
                                    ->required()
                                    ->columnSpan(1),
                                TextInput::make('invoice_number')
                                    ->default(function () {
                                      return Invoice::generateInvoiceNumber();
                                    })
                                    ->required(),
                                DatePicker::make('invoice_date')
                                    ->required(),
                                DatePicker::make('due_date')
                            ])
                            ->collapsible(),



                    ])->columnSpan(1),


            ])->columns(3);
    }
}
