<?php

namespace App\Filament\Clusters\Therapist;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class TherapistCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;
    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Start;
    protected static UnitEnum|string|null $navigationGroup = 'Booking Management';

    public static function getModelLabel(): string
    {
        return business_label('staff', 'Therapist');
    }
 

    public static function getNavigationLabel(): string
    {
        return self::getModelLabel() ?? str(class_basename(static::class))
            ->beforeLast('Cluster')
            ->kebab()
            ->replace('-', ' ')
            ->ucwords();
    }

}
