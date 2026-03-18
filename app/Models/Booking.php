<?php

namespace App\Models;

use App\Mail\BookingMailNotification;
use App\Models\Concerns\BelongsToCompany;
use App\Services\BusinessSettings;
use App\Services\BookingNotificationService;
use App\Services\InvoiceGenerateService;
use App\Services\TimeslotService;
use Carbon\Carbon;
use Guava\Calendar\Contracts\Eventable;
use Guava\Calendar\ValueObjects\CalendarEvent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str as SupportStr;
use LaravelDaily\Invoices\Classes\Buyer;
use Psy\Util\Str;

class Booking extends Model implements Eventable
{
    use BelongsToCompany;
    use HasFactory, SoftDeletes;
    //
    protected $fillable = [
        'company_id',
        'user_id',     // who booked
        'booking_number',     // who booked
        'customer_id',  // who bookedich listing
        'branch_id',
        'listing_id',  // which listing
        'therapist_id',
        'start_time',
        'end_time',
        'status',      // pending, confirmed, canceled, completed,expired
        'notes',
        'title',
        'price',
        'type',
        'location',
        'payment_status',
        'bed_id',
        'no_show',
    ];


    protected function getStatusColor(): string
    {
        return match ($this->status) {
            'pending'   => '#fbbf24', // amber
            'confirmed' => '#60a5fa', // blue
            'canceled'  => '#f87171', // red
            'completed' => '#4ade80', // green
            'expired'   => $this->no_show ? '#dc2626' : '#9ca3af', // red for no_show, gray for expired
            default     => '#9ca3af', // gray
        };
    }

    public $statuses = ['pending', 'confirmed', 'canceled', 'completed', 'expired'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function therapist()
    {
        return $this->belongsTo(Therapist::class);
    }
    public function bed()
    {
        return $this->belongsTo(Bed::class);
    }


    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }

    public function payments()
    {
        return $this->hasMany(BookingPayment::class);
    }

    public function post_assestment()
    {
        return $this->hasOne(CustomerPostAssesment::class);
    }

    public function scopeNotYetReminded($q){
        $q->whereNull('reminded_at');
    }


    public function totalPayment()
    {
        return $this->payments()->where('payment_status', 'paid')->sum('amount');
    }

    public function balance()
    {
        return $this->price - $this->totalPayment();
    }

    public function scopeConfirmed($q)
    {
        return $q->where('status', 'confirmed');
    }

    public function scopeCompleted($q)
    {
        return $q->where('status', 'completed');
    }

    public function invoice(){
        return $this->hasOne(Invoice::class);
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


    /**
     * The "booted" method of the model.
     */
    protected static function boot(): void
    {
        

        parent::boot();

        // Before creating a new Customer
        static::creating(function ($booking) {
            // Generate a unique code
            $booking->user_id = auth()->user()->id ?? null;

            if (! $booking->company_id) {
                $branchCompanyId = Branch::query()->whereKey($booking->branch_id)->value('company_id');
                $booking->company_id = $branchCompanyId ?: auth()->user()?->company_id;
            }

            if (! $booking->branch_id) {
                $booking->branch_id = app(BusinessSettings::class)->getDefaultBranchId();
            }
            
            if($booking->customer->is_vip){
                $booking->status = 'confirmed';
            }
        });

        static::updated(function ($booking) {
            $appName = config('app.name');
            $booking->load('listing', 'therapist','invoice');
            // Check if the 'status' field was changed
            if ($booking->isDirty('status')) {
                 
                $oldStatus = $booking->getOriginal('status');
                $newStatus = $booking->status;
                $statusMap = [
                    'confirmed' => [
                        'template' => 'mails.bookings.confirmed',
                        'subject'  => " [$appName] Booking Confirmed #%s", // %s will be booking number
                    ],
                    'canceled' => [
                        'template' => 'mails.bookings.canceled',
                        'subject'  => " [$appName] Booking Canceled #%s",
                    ],
                    'completed' => [
                        'template' => 'mails.bookings.completed',
                        'subject'  => " [$appName] Booking Completed #%s",
                    ],
                ];

                if (isset($statusMap[$booking?->status])) {
                    $template = $statusMap[$booking->status]['template'];
                    $subject  = sprintf($statusMap[$booking->status]['subject'], $booking->booking_number);

                    // Mail::to($booking->customer->email)->queue(new BookingMailNotification($subject, $template, $booking->toArray()));
                    
                    app(BookingNotificationService::class)->sendNotification($booking, $booking->status);
                }

            }
        });

        

        static::created(function ($booking) { 
            if (app()->runningUnitTests()) {
                return;
            }
            $subject = "[".config('app.name') . "] Booking Created – Pending Confirmation #{$booking->booking_number}";
            $template = 'mails.bookings.created';
            $booking->load('listing', 'therapist','customer');

            if(!$booking->status){
                $booking->status = 'pending';
            }

            app(BookingNotificationService::class)->sendNotification($booking, 'created');
            // Mail::to($booking->customer->email)->queue(new BookingMailNotification($subject, $template, $booking->toArray()));

                    $item = [[
                            'name'=>$booking?->listing?->title ??$booking?->title,
                            'price_per_unit'=>$booking->price,
                            'quantity'=>1,
                            'total'=>1 * $booking->price,
                    ]];
                    $invoice = $booking->invoice()->create([
                        'invoice_number' => Invoice::generateInvoiceNumber(),
                        'customer_id'=>$booking->customer->id,
                        'status'=>'pending',
                        'amount'=>$booking->price,
                        'items' => $item,
                        'invoice_date'=>now(),
                    ]);

                    $invoice->file_path = (new InvoiceGenerateService)->generateInvoice($invoice);
                    $invoice->save();

        });
        // Before saving (both creating and updating)
        static::saving(function ($booking) {
            // Example: ensure name is title-cased
            if(auth()->user()){
                $booking->user_id = auth()->user()->id;
            }

        });
    }

    public static function availableTimeslots($date, $branchId = null)
    {
        $slots = TimeslotService::generateForDay($date);

        $available = [];

        foreach ($slots as $slot) {
            $overlap = false;

            // foreach ($bookings as $booking) {
            //     $bStart = Carbon::parse($booking->start_time);
            //     $bEnd   = Carbon::parse($booking->end_time);

            //     if ($slot['start'] < $bEnd && $slot['end'] > $bStart) {
            //         $overlap = true;
            //         break;
            //     }
            // }

            if (!$overlap) {
                $label = $slot['label'];
                $available[$label] = $label;
            }
        }

        return $available;
    }
    

    public function canAddPayment(){
        $status = $this->status;
        return ($status == 'pending' || $status == 'confirmed') && $this->payment_status != 'paid';
    }

    public function canConfirm(){
        $status = $this->status;
        return ($status == 'pending') && $this->payment_status != 'pending';
    }

    public function canComplete(){
        $status = $this->status;
        return ($status == 'confirmed' && $this->payment_status == 'paid') ;
    }



    // public static function availableTimeslots($date)
    // {
    //     $slots = TimeslotService::generateForDay($date); // returns ['start' => Carbon, 'end' => Carbon, 'label' => '...']
    //     $bookings = self::whereDate('start_time', $date)->get();

    //     $timeslots = [];

    //     foreach ($slots as $slot) {
    //         $slotStart = Carbon::parse($slot['start']);
    //         $slotEnd = Carbon::parse($slot['end']);

    //         $overlap = false;

    //         foreach ($bookings as $booking) {
    //             $bStart = Carbon::parse($booking->start_time);
    //             $bEnd = Carbon::parse($booking->end_time);

    //             if ($slotStart < $bEnd && $slotEnd > $bStart) {
    //                 $overlap = true;
    //                 break;
    //             }
    //         }

    //         // Convert to 12-hour format for display
    //         $label = $slotStart->format('g:i A') . ' - ' . $slotEnd->format('g:i A');

    //         $timeslots[$label] = [
    //             'label' => $label,
    //             'disabled' => $overlap, // mark overlap slots as disabled
    //         ];
    //     }

    //     return $timeslots;
    // }

    public static function clearBookingData(){
        DB::table('bookings')->delete();
        DB::table('booking_payments')->delete();
        DB::table('invoices')->delete();
    }



}
