<?php

namespace App\Filament\Clusters\Dashboard;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Support\Icons\Heroicon;

class DashboardCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::Home;

        protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

}
