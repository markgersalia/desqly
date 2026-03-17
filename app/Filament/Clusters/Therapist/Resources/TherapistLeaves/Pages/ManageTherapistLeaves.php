<?php

namespace App\Filament\Clusters\Therapist\Resources\TherapistLeaves\Pages;

use App\Filament\Clusters\Therapist\Resources\TherapistLeaves\TherapistLeaveResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageTherapistLeaves extends ManageRecords
{
    protected static string $resource = TherapistLeaveResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
