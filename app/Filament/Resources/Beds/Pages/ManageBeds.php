<?php

namespace App\Filament\Resources\Beds\Pages;

use App\Filament\Resources\Beds\BedResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageBeds extends ManageRecords
{
    protected static string $resource = BedResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
