<?php

namespace App\Filament\Clusters\Services;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ServicesCluster extends Cluster
{    
    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Start;
    protected static UnitEnum|string|null $navigationGroup = 'Booking Management';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    
    public static function canAccess(): bool
    {
        return config('booking.has_listings') === true;
    }
}
