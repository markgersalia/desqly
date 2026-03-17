<?php

namespace App\Filament\Resources\BookingPayments;

use App\Filament\Resources\BookingPayments\Pages\ManageBookingPayments;
use App\Models\BookingPayment;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Enums\TextSize;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class BookingPaymentResource extends Resource
{
    protected static ?string $model = BookingPayment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyDollar;

    protected static UnitEnum|string|null $navigationGroup = 'Billing Management';
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components(self::schema());
    }

    public static function schema($balance = 0)
    {
        return [
            TextEntry::make('booking.balance')
            ->label("")
            ->money('PHP')
            ->size(TextSize::Large)
            ->visible($balance != 0)
            ->weight('bold')
            ->default($balance),
            Select::make('payment_method')
                ->options([
                    'card' => 'Card',
                    'bank_transfer' => 'Bank transfer',
                    'cash' => 'Cash',
                ])
                ->default('cash')
                ->required()
                ->reactive(), // make the select reactive so dependent fields update

            Hidden::make('status')
                ->default('paid'),

            TextInput::make('payment_reference')
                ->label('Payment Reference')
                ->visible(function (callable $get) {
                    return $get('payment_method') !== 'cash';
                })
                ->reactive(), // important: updates visibility on change

            TextInput::make('amount')
                ->required()
                ->numeric(),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('booking.booking_number')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('processed_by.name')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('payment_method')
                    ->badge(),
                TextColumn::make('payment_status')

                    ->badge(),
                TextColumn::make('payment_reference')
                    ->searchable(),
                TextColumn::make('amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageBookingPayments::route('/'),
        ];
    }
}
