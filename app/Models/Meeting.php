<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Guava\Calendar\ValueObjects\CalendarEvent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Meeting extends Model
{
    use BelongsToCompany;
    use HasFactory;

    protected $fillable = [ 
        'customer_id',
        'user_id',
        'title',
        'description',
        'start_time',
        'end_time', 
        'location',
        'status',
        'color',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime', 
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

        public function toCalendarEvent(): CalendarEvent
    {
        return CalendarEvent::make($this)
            ->action('edit')
            ->title("($this->status) {$this?->listing?->title} {$this?->title} ")
            ->start($this->start_time)
            ->end($this->end_time)
            ->extendedProp('customer_name', $this->customer->name)
            ->backgroundColor($this->getStatusColor())
            // ->backgroundColor('#FE9A00')
        ;
    }

}
