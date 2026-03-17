<?php

namespace App\Filament\Resources\Bookings\Pages;

use App\Filament\Actions\BookingActions;
use App\Filament\Resources\BookingPayments\BookingPaymentResource;
use App\Filament\Resources\Bookings\BookingResource;
use App\Models\BookingPayment;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Colors\Color;

class EditBooking extends EditRecord
{
    protected static string $resource = BookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            BookingActions::complete(),
            BookingActions::confirm('form'),
            BookingActions::cancel(),
            BookingActions::makePayment() ,
 

            DeleteAction::make(),
        ];
    }
}
