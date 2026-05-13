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

protected static ?int $navigationSort = 1000; // Lower numbers appear first
    protected static string|BackedEnum|null $navigationIcon = Heroicon::Cog8Tooth;
}
