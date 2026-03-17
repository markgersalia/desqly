<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\Listing;
use App\Models\Category;
use App\Models\Therapist;
use App\Models\Customer;
use App\Models\User;
use Carbon\Carbon;
use App\Services\TimeslotService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class BookingForm extends Component
{
    public $currentStep = 1;
    public $totalSteps = 5;

    // Step 1: Service Selection
    public $selectedBranch = '';
    public $selectedCategory = '';
    public $selectedService = '';
    public $selectedListing = ''; 
    public $branches = [];
    public $categories = [];
    public $services = [];
    public $listings = []; 
    

    // Step 2: Date & Time
    public $selectedDate = '';
    public $selectedTime = '';
    public $availableTimes = [];
    public $dateAvailableMessage = '';

    // Step 3: Personal Information
    public $name = '';
    public $email = '';
    public $phone = '';
    public $notes = '';

    // Step 4: Confirmation
    public $bookingConfirmed = false;
    public $booking = null;
    public $isSubmitting = false;

    protected $rules = [
        'selectedBranch' => 'required|exists:branches,id',
        'selectedCategory' => 'required|exists:categories,id',
        'selectedListing' => 'required|exists:listings,id',
        'selectedDate' => 'required|date|after_or_equal:today',
        'selectedTime' => 'required',
        'name' => 'required|string|min:2',
        'email' => 'required|email',
        'phone' => 'required|string|min:10',
        'notes' => 'nullable|string|max:500',
    ];

    // Dynamic validation rules based on config
    protected function getValidationRules(): array
    {
        $rules = $this->rules;
         
        
        return $rules;
    }

    protected $messages = [
        'selectedBranch.required' => 'Please select a branch',
        'selectedBranch.exists' => 'Selected branch is not available',
        'selectedCategory.required' => 'Please select a category',
        'selectedCategory.exists' => 'Selected category is not available',
        'selectedListing.required' => 'Please select a service',
        'selectedListing.exists' => 'Selected service is not available',
        'selectedDate.required' => 'Please select a date',
        'selectedDate.after_or_equal' => 'Selected date must be today or in the future',
        'selectedTime.required' => 'Please select a time slot', 
        'name.required' => 'Your name is required',
        'email.required' => 'Your email is required',
        'email.email' => 'Please enter a valid email address',
        'phone.required' => 'Your phone number is required',
    ];

    public function mount()
    {
        if (\Illuminate\Support\Facades\Schema::hasTable('branches')) {
            $this->branches = Branch::active()
                ->orderBy('name')
                ->get(['id', 'name'])
                ->toArray();

            $this->selectedBranch = Branch::active()->value('id') ?? '';
        }

        // Load categories
        if (\Illuminate\Support\Facades\Schema::hasTable('categories')) {
            $this->categories = Category::orderBy('name')
                ->get(['id', 'name', 'description', 'slug'])
                ->map(function ($category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                        'description' => $category->description,
                        'slug' => $category->slug,
                    ];
                })
                ->toArray();
        }
 
    }

    public function updatedSelectedBranch()
    {
        $this->selectedDate = '';
        $this->selectedTime = '';
        $this->availableTimes = [];
        $this->dateAvailableMessage = '';
    }

    public function updatedSelectedCategory()
    {
        // Clear previously selected service when category changes
        $this->selectedListing = '';
        $this->selectedService = '';
        
        // Load services (listings) based on selected category
        if ($this->selectedCategory) {
            $this->listings = Listing::where('category_id', $this->selectedCategory)
                ->orderBy('title')
                ->get(['id', 'title', 'duration', 'price', 'description', 'availability'])
                ->map(function ($listing) {
                    return [
                        'id' => $listing->id,
                        'name' => $listing->title,
                        'duration' => ($listing->duration ?? 60) . ' min',
                        'price' => '₱' . number_format($listing->price, 2),
                        'description' => $listing->description,
                    ];
                })
                ->toArray();
        } else {
            $this->listings = [];
        }
    }

    public function updatedSelectedListing()
    {
        // Clear time selection when service changes
        $this->selectedDate = '';
        $this->selectedTime = '';
        $this->availableTimes = [];
    }

    public function updatedSelectedDate()
    {
        $this->isDateAvailable($this->selectedDate);
        // Clear previously selected time when date changes
        $this->selectedTime = '';
        
        // Generate available time slots based on selected date and service
        $this->availableTimes = $this->generateAvailableTimeSlots();
 
    }

    private function generateAvailableTimeSlots()
    {
        if (! $this->selectedDate || ! $this->selectedListing || ! $this->selectedBranch) {
            return [];
        }

        // Get available timeslots from the existing booking system
        $availableSlots = Booking::availableTimeslots($this->selectedDate, $this->selectedBranch);
        
        // Check if listing is available for selected date
        $listing = Listing::find($this->selectedListing);
        if (!$listing || !$listing->isAvailable($this->selectedDate)) {
            return [];
        }

        return $availableSlots;
    }

    public function isDateAvailable($date)
    {
        if (! $this->selectedBranch) {
            $this->dateAvailableMessage = 'Please select a branch first';
            return false;
        }

        if (!$date || !$this->selectedListing) {
            $this->dateAvailableMessage = 'Please select a service first';
            return false;
        }

        $listing = Listing::find($this->selectedListing);
        if (!$listing) {
            $this->dateAvailableMessage = 'Selected service is not available';
            return false;
        }

        // Check listing availability first
        if (!$listing->isAvailable($date)) {
            $this->dateAvailableMessage = 'This service is not available on the selected date';
            return false;
        }

        // Check if any available therapist exists for the date
        $therapists = Therapist::where('is_active', 1)
            ->where('branch_id', $this->selectedBranch)
            ->get();
        
        foreach ($therapists as $therapist) {
            // Check if therapist is on leave for the entire day
            $isOnLeave = $therapist->isOnLeave($date . ' 00:00:00', $date . ' 23:59:59');
            
            if (!$isOnLeave) {
                // Therapist is available on this date
                $this->dateAvailableMessage = '';
                return true;
            }
        }  

        // No therapist available for this date
        $this->dateAvailableMessage = 'No therapists are available on this date';
        return false;
    }

    public function nextStep()
    {
        $this->validateCurrentStep();

        if ($this->currentStep < $this->totalSteps) {
            $this->currentStep++;
        }
    }

    public function previousStep()
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    public function goToStep($step)
    {
        if ($step <= $this->currentStep || $step == 1) {
            $this->currentStep = $step;
        }
    }

    private function validateCurrentStep()
    {
        if ($this->currentStep == 1) {
            // Step 1: Category only
            $this->validate([
                'selectedBranch' => 'required|exists:branches,id',
                'selectedCategory' => 'required|exists:categories,id',
            ]);
        } elseif ($this->currentStep == 2) {
            // Step 2: Service only
            $this->validate([
                'selectedListing' => 'required|exists:listings,id',
            ]);
        } elseif ($this->currentStep == 3) {
            // Step 3: Date & Time
            $step3Rules = [
                'selectedDate' => 'required|date|after_or_equal:today',
                'selectedTime' => 'required',
            ];
             
            $this->validate($step3Rules);
            
            // Additional validation for date/time availability
            if (!$this->isDateAvailable($this->selectedDate)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'selectedDate' => 'Selected date is not available for this service.',
                ]);
            }
            
            if (!in_array($this->selectedTime, $this->availableTimes)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'selectedTime' => 'Selected time slot is no longer available.',
                ]);
            }
        } elseif ($this->currentStep == 4) {
            // Step 4: Personal Information
            $step4Rules = [
                'name' => 'required|string|min:2',
                'email' => 'required|email',
                'phone' => 'required|string|min:10',
            ];
             
            $this->validate($step4Rules);
        }
    }

    public function submitBooking()
    {
        $this->isSubmitting = true;
        
        $this->validate($this->getValidationRules());

        // Start database transaction
        try {
            DB::beginTransaction();

            // Create or find customer
            $customer = Customer::firstOrCreate(
                ['email' => $this->email],
                [
                    'name' => $this->name,
                    'phone' => $this->phone,
                ]
            );

            // Get listing details
            $listing = Listing::find($this->selectedListing);
            
            // Parse time slot to get start and end times
            [$startTime, $endTime] = explode(' - ', $this->selectedTime);


            
            // Create booking in database
            $bookingData = [
                'customer_id' => $customer->id,
                'branch_id' => $this->selectedBranch,
                'listing_id' => $this->selectedListing,
                'title' => $listing->title,
                'type' => $listing->type,
                'price' => $listing->price,
                'start_time' => Carbon::parse($this->selectedDate . ' ' . $startTime),
                'end_time' => Carbon::parse($this->selectedDate . ' ' . $endTime),
                'notes' => $this->notes,
                'status' => 'pending',
                'payment_status' => 'pending',
                'booking_number' => 'BK-' . str_pad(Booking::max('id') + 1, 5, '0', STR_PAD_LEFT),
            ];
 

            $this->booking = Booking::create($bookingData);


            $recipients = User::getAdminUsers();

            foreach ($recipients as $recipient) {
                $recipient->notify(
                    Notification::make()
                        ->title('New External Appointment')
                        ->body("Appointment {$this->booking->booking_number} has been created for {$customer->name}. Service: {$listing->title} ({$listing->type}) scheduled for " . Carbon::parse($this->selectedDate)->format('M d, Y') . " at {$startTime}. Total: P" . number_format($listing->price, 2) . '. Status: Pending payment.')
                        ->success()
                        ->actions([
                            Action::make('view')
                                ->label('View Booking')
                                ->url(route('filament.admin.resources.bookings.edit', ['record' => $this->booking->id]))
                                ->markAsRead(),
                        ])
                        ->toDatabase()
                );
            }
            DB::commit();

            $this->bookingConfirmed = true;
            $this->currentStep = 5;

            // Send confirmation email (optional)
            // Mail::to($this->email)->send(new BookingConfirmation($this->booking));

        } catch (\Exception $e) {
            $this->isSubmitting = false;
            DB::rollBack();
            session()->flash('error', 'There was an error creating your booking. Please try again.');
            throw $e;
        }
    }

    public function resetForm()
    {
        $this->reset();
        $this->currentStep = 1;
        $this->mount();
    }

    public function render()
    {
        return view('livewire.home.booking-form');
    }
}

