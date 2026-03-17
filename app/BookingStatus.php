<?php

namespace App;

enum BookingStatus: string
{
    //
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Canceled = 'canceled';
    case Completed = 'completed'; 
 
    
    public function getColor(): string | array | null
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Confirmed => 'info',
            self::Canceled => 'danger',
            self::Completed => 'success',
        };
    }
}
