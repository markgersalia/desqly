<?php

namespace App\Filament\Clusters\Setting;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Support\Icons\Heroicon;

class SettingCluster extends Cluster
{ 
    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Start;
    protected static ?string $title = 'Settings';

    protected static ?int $navigationSort = 19;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::Cog8Tooth;
}
