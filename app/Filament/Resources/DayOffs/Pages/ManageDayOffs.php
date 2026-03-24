<?php

namespace App\Filament\Resources\DayOffs\Pages;

use App\Filament\Resources\DayOffs\DayOffResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageDayOffs extends ManageRecords
{
    protected static string $resource = DayOffResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
