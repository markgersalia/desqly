<?php

namespace App\Filament\Resources\BookingPayments\Pages;

use App\Filament\Resources\BookingPayments\BookingPaymentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageBookingPayments extends ManageRecords
{
    protected static string $resource = BookingPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
