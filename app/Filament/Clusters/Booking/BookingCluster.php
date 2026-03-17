<?php

namespace App\Filament\Clusters\Booking;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class BookingCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;
     
    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Start;
    protected static UnitEnum|string|null $navigationGroup = 'Booking Management';

}
