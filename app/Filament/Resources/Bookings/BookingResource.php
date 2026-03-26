<?php

namespace App\Filament\Resources\Bookings;

use App\Filament\Clusters\Booking\BookingCluster;
use App\Filament\Resources\Bookings\Pages\CreateBooking;
use App\Filament\Resources\Bookings\Pages\EditBooking;
use App\Filament\Resources\Bookings\Pages\ListBookings;
use App\Filament\Resources\Bookings\RelationManagers\PaymentsRelationManager;
use App\Filament\Resources\Bookings\Schemas\BookingForm;
use App\Filament\Resources\Bookings\Tables\BookingsTable;
use App\Filament\Resources\Bookings\Widgets\BookingStats;
use App\Models\Booking;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use UnitEnum;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::CalendarDateRange;

    protected static ?int $navigationSort = -1;


    // protected static ?string $cluster = BookingCluster::class;
    protected static UnitEnum|string|null $navigationGroup = 'Booking Management';

    public static function getModelLabel(): string
    {
        return business_label('booking', 'Booking');
    }

    public static function getPluralModelLabel(): string
    {
        return Str::plural(self::getModelLabel());
    }

    public static function form(Schema $schema): Schema
    {
        return BookingForm::configure($schema);
    }

    public static function getNavigationBadge(): ?string
    {
        $tenantId = Filament::getTenant()?->getKey();

        return (string) Booking::query()
            ->when($tenantId, fn ($query) => $query->where('company_id', $tenantId))
            ->count();
    }

    public static function table(Table $table): Table
    {
        return BookingsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            PaymentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBookings::route('/'),
            'create' => CreateBooking::route('/create'),
            'edit' => EditBooking::route('/{record}/edit'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            BookingStats::class,
        ];
    }
}
