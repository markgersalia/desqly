<?php

namespace App\Filament\Resources\Listings;

use App\Filament\Clusters\Services\ServicesCluster;
use App\Filament\Resources\Listings\Pages\CreateListing;
use App\Filament\Resources\Listings\Pages\EditListing;
use App\Filament\Resources\Listings\Pages\ListListings;
use App\Filament\Resources\Listings\Pages\ViewListing;
use App\Filament\Resources\Listings\RelationManagers\BookingsRelationManager;
use App\Filament\Resources\Listings\Schemas\ListingForm;
use App\Filament\Resources\Listings\Schemas\ListingInfolist;
use App\Filament\Resources\Listings\Tables\ListingsTable;
use App\Models\Listing;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Str;

class ListingResource extends Resource
{
    protected static ?string $model = Listing::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ListBullet;

    protected static ?string $cluster = ServicesCluster::class;

    public static function getModelLabel(): string
    {
        return business_label('service', 'Service');
    }

    public static function getPluralModelLabel(): string
    {
        return Str::plural(self::getModelLabel());
    }

    public static function getNavigationBadge(): ?string
    {
        $tenantId = Filament::getTenant()?->getKey();

        return (string) Listing::query()
            ->when($tenantId, fn ($query) => $query->where('company_id', $tenantId))
            ->count();
    }

    public function getTitle(): string | Htmlable
    {
        return Str::plural(self::getModelLabel());
    }

    public static function canAccess(): bool
    {
        return config('booking.has_listings') === true;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return config('booking.has_listings') === true;
    }

    public static function form(Schema $schema): Schema
    {
        return ListingForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ListingInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ListingsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            BookingsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListListings::route('/'),
            'create' => CreateListing::route('/create'),
            'view' => ViewListing::route('/{record}'),
            'edit' => EditListing::route('/{record}/edit'),
        ];
    }
}
