<?php

namespace App\Models;

use App\TherapistLeaveType;
use Guava\Calendar\Contracts\Eventable;
use Guava\Calendar\ValueObjects\CalendarEvent;
use Illuminate\Database\Eloquent\Model;

class TherapistLeave extends Model implements Eventable
{

    protected $fillable = ['therapist_id', 'status', 'reason', 'start_date', 'end_date', 'type'];


    protected $casts = [
        'type' => TherapistLeaveType::class,
    ];

    public function therapist()
    {
        return $this->belongsTo(Therapist::class);
    }
    //
    public function toCalendarEvent(): CalendarEvent
    {

        $typeLabel = $this->type instanceof TherapistLeaveType
            ? $this->type->label()   // Human-readable label from enum
            : ucfirst(str_replace('_', ' ', $this->type));

        return CalendarEvent::make($this)
            ->action('edit')
            ->title(" ({$typeLabel} : {$this->status})   {$this?->therapist?->name} {$this?->reason} ")
            ->start($this->start_date)
            ->end($this->end_date)
            ->backgroundColor("#808080")
            ->displayAuto() 
            // ->backgroundColor('#ff0000')
            ->allDay()
            // ->url(
            //     route('filament.admin.therapist.resources.therapists.edit', $this)
            // )
        ;
    }

    public function scopeApproved($query){
        return $query->where('status', 'approved');
    }
    
    public function isOnLeave($start, $end)
    {
        return $this
            ->where('therapist_id', $this->therapist_id)
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('start_date', [$start, $end])
                    ->orWhereBetween('end_date', [$start, $end])
                    ->orWhere(function ($q) use ($start, $end) {
                        $q->where('start_date', '<=', $start)
                            ->where('end_date', '>=', $end);
                    });
            })
            ->exists();
    }
}
