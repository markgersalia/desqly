<?php

namespace App\Filament\Resources\Bookings\Pages;

use App\Filament\Resources\Bookings\BookingResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBooking extends CreateRecord
{
    protected static string $resource = BookingResource::class;
    public function mount(): void
    {
        parent::mount();

        $this->form->fill(array_filter([
            'customer_id'   => request('customer_id'),
            'branch_id'     => request('branch_id'),
            'listing_id'    => request('listing_id'),
            'therapist_id'  => request('therapist_id'),
            'price'         => request('price'),
            'selected_date' => request('selected_date'),
        ]));
    }
}
