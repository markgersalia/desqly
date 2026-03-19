<div>
    <div class="max-w-7xl mx-auto">
        
        @if (!$bookingConfirmed)
            <!-- Progress Steps -->
            <div class="mb-6 md:mb-8 lg:mb-16">
                <!-- Mobile: Minimal Progress Indicator -->
                <div class="flex md:hidden mb-4 md:mb-6 lg:mb-8">
                    <div class="text-center w-full px-4">
                        <p class="text-sm text-gray-600 font-medium">
                            Step {{ $currentStep }} of {{ $totalSteps }}:
                            <span class="text-primary font-display font-semibold ml-1">
                                @if ($currentStep == 1) Category
                                @elseif ($currentStep == 2) Service
                                @elseif ($currentStep == 3) {{ $isWholeDayMode ? 'Date' : 'Date & Time' }}
                                @elseif ($currentStep == 4) Details
                                @else Confirm
                                @endif
                            </span>
                        </p>
                    </div>
                </div>
                
                <!-- Tablet: Medium Horizontal (768px+) -->
                <div class="hidden md:flex lg:hidden items-center justify-center">
                    @for ($i = 1; $i <= $totalSteps; $i++)
                        <div class="flex items-center">
                            <div class="relative">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center transition-all duration-500 border-2 
                                    {{ $currentStep >= $i ? 'bg-primary border-primary text-white' : 'bg-white border-gray-300 text-gray-400' }}">
                                    @if ($currentStep > $i)
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    @else
                                        <span class="font-display font-semibold text-sm">{{ $i }}</span>
                                    @endif
                                </div>
                                <span class="absolute -bottom-6 left-1/2 transform -translate-x-1/2 text-[10px] uppercase tracking-wider whitespace-nowrap
                                    {{ $currentStep >= $i ? 'text-primary font-medium' : 'text-gray-400' }}">
                                    @if ($i == 1) Category
                                    @elseif ($i == 2) Service
                                    @elseif ($i == 3) {{ $isWholeDayMode ? 'Date' : 'Date & Time' }}
                                    @elseif ($i == 4) Details
                                    @else Confirm
                                    @endif
                                </span>
                            </div>
                            @if ($i < $totalSteps)
                                <div class="w-12 h-0.5 mx-3 transition-all duration-500 {{ $currentStep > $i ? 'bg-primary' : 'bg-gray-300' }}"></div>
                            @endif
                        </div>
                    @endfor
                </div>
                
                <!-- Desktop: Full Horizontal (1024px+) -->
                <div class="hidden lg:flex items-center justify-center">
                    @for ($i = 1; $i <= $totalSteps; $i++)
                        <div class="flex items-center">
                            <div class="relative">
                                <div class="w-12 h-12 rounded-full flex items-center justify-center transition-all duration-500 border-2 
                                    {{ $currentStep >= $i ? 'bg-primary border-primary text-white' : 'bg-white border-gray-300 text-gray-400' }}">
                                    @if ($currentStep > $i)
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    @else
                                        <span class="font-display font-semibold text-base">{{ $i }}</span>
                                    @endif
                                </div>
                                <span class="absolute -bottom-8 left-1/2 transform -translate-x-1/2 text-xs uppercase tracking-wider whitespace-nowrap
                                    {{ $currentStep >= $i ? 'text-primary font-medium' : 'text-gray-400' }}">
                                    @if ($i == 1) Category
                                    @elseif ($i == 2) Service
                                    @elseif ($i == 3) {{ $isWholeDayMode ? 'Date' : 'Date & Time' }}
                                    @elseif ($i == 4) Your Details
                                    @else Confirm
                                    @endif
                                </span>
                            </div>
                            @if ($i < $totalSteps)
                                <div class="w-16 h-0.5 mx-4 transition-all duration-500 {{ $currentStep > $i ? 'bg-primary' : 'bg-gray-300' }}"></div>
                            @endif
                        </div>
                    @endfor
                </div>
            </div>

            <!-- Form Card -->
            <div class="bg-white shadow-2xl p-4 md:p-6 lg:p-12 mt-6 md:mt-8 lg:mt-12">
                
                <!-- Step 1: Category Selection Only -->
                @if ($currentStep === 1)
                    <div class="space-y-8">
                        <div class="text-center mb-8">
                            <h2 class="text-2xl md:text-3xl lg:text-4xl font-display font-light text-dark mb-3">{{ $usesBranches ? 'Select Branch and Category' : 'Select Category' }}</h2>
                            <p class="text-gray-600">{{ $usesBranches ? 'Choose your preferred branch before selecting a category' : 'Choose a category to continue' }}</p>
                        </div>

                        @if ($usesBranches)
                            <div class="mb-8">
                                <label class="block text-sm font-medium text-gray-700 mb-2 uppercase tracking-wider">
                                    Select Branch
                                </label>
                                <select wire:model.live="selectedBranch" class="w-full rounded-lg border-2 border-gray-200 px-4 py-3 focus:border-primary focus:ring-primary">
                                    <option value="">Choose a branch</option>
                                    @foreach ($branches as $branch)
                                        <option value="{{ $branch['id'] }}">{{ $branch['name'] }}</option>
                                    @endforeach
                                </select>
                                @error('selectedBranch')
                                    <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif
                         
                        <!-- Category Selection -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-4 uppercase tracking-wider">
                                Select Category
                            </label>
                            
                            <!-- Unified Card Grid - Responsive Layout -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6">
                                @foreach ($categories as $category)
                                    <label class="relative cursor-pointer group block" for="category_{{$category['id']}}">
                                        <input 
                                            id="category_{{$category['id']}}"
                                            name="category_selection"
                                            type="radio" 
                                            wire:model.live="selectedCategory" 
                                            value="{{ $category['id'] }}" 
                                            class="peer sr-only">
                                        
                                        <div class="p-4 md:p-6 lg:p-8 border-2 rounded-lg md:rounded-xl transition-all duration-300 
                                            peer-checked:border-primary peer-checked:bg-primary/5 hover:shadow-lg
                                            {{ $errors->has('selectedCategory') ? 'border-red-300' : 'border-gray-200' }}">
                                            
                                            <div class="flex items-start justify-between mb-3 md:mb-4">
                                                <h3 class="font-display text-base md:text-lg lg:text-xl text-dark group-hover:text-primary transition-colors flex-1 pr-3">
                                                    {{ $category['name'] }}
                                                </h3>
                                                <div class="w-5 h-5 md:w-5 md:h-5 lg:w-6 lg:h-6 rounded-full border-2 border-gray-300 
                                                    peer-checked:bg-primary peer-checked:border-primary 
                                                    flex items-center justify-center transition-all flex-shrink-0">
                                                    <div class="w-2 h-2 md:w-2 md:h-2 lg:w-3 lg:h-3 bg-white rounded-full scale-0 
                                                        peer-checked:scale-100 transition-transform"></div>
                                                </div>
                                            </div>
                                            
                                            @if ($category['description'])
                                                <p class="text-sm text-gray-600 line-clamp-2 md:line-clamp-3">{{ $category['description'] }}</p>
                                            @endif
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                            
                            @error('selectedCategory') 
                                <p class="text-red-500 text-sm mt-2">{{ $message }}</p> 
                            @enderror
                        </div>
                    </div>
                @endif

                <!-- Step 2: Service Selection Only -->
                @if ($currentStep === 2)
                    <div class="space-y-8">
                        <div class="text-center mb-8">
                            <h2 class="text-2xl md:text-3xl lg:text-4xl font-display font-light text-dark mb-3">Select Service</h2>
                            <p class="text-gray-600">Choose your specific treatment</p>
                        </div>
                        
                        <!-- Show selected category for context -->
                        <div class="bg-light p-4 rounded-lg mb-6">
                            <p class="text-sm text-gray-600">Selected Category:</p>
                            <p class="font-display text-lg">{{ collect($categories)->firstWhere('id', $selectedCategory)['name'] ?? 'Unknown' }}</p>
                        </div>
                        
                        <!-- Service Selection -->
                        <div class="mt-8">
                            <label class="block text-sm font-medium text-gray-700 mb-4 uppercase tracking-wider">
                                Select Service
                            </label>
                            
                            @if (count($listings) > 0)
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                                    @foreach ($listings as $service)
                                        <label class="relative cursor-pointer group" for="service_{{$service['id']}}">
                                            <input 
                                                id="service_{{$service['id']}}"
                                                name="service_selection"
                                                type="radio" 
                                                wire:model.live="selectedListing" 
                                                value="{{ $service['id'] }}" 
                                                class="peer sr-only hidden">
                                            
                                            <div class="p-6 border-2 transition-all duration-300 
                                                peer-checked:border-primary peer-checked:bg-primary/5 hover:shadow-lg
                                                {{ $errors->has('selectedListing') ? 'border-red-300' : 'border-gray-200' }}">
                                                
                                                <div class="flex items-start justify-between mb-3">
                                                    <h3 class="font-display text-xl text-dark group-hover:text-primary transition-colors">
                                                        {{ $service['name'] }}
                                                    </h3>
                                                    <span class="text-primary font-semibold text-lg">{{ $service['price'] }}</span>
                                                </div>
                                                
                                                <p class="text-sm text-gray-600 mb-3">{{ $service['duration'] }}</p>
                                                @if ($service['description'])
                                                    <p class="text-sm text-gray-500">{!! Str::limit($service['description'], 100) !!}</p>
                                                @endif
                                                
                                                
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                                @error('selectedListing') 
                                    <p class="text-red-500 text-sm mt-2">{{ $message }}</p> 
                                @enderror
                            @else
                                <div class="bg-yellow-50 border-2 border-yellow-200 rounded-lg p-6 text-center">
                                    <svg class="w-12 h-12 text-yellow-500 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg>
                                    <p class="text-yellow-800 font-medium">No services available in this category</p>
                                    <p class="text-yellow-600 text-sm mt-1">Please go back and select a different category.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Step 3: Date & Time Selection -->
                @if ($currentStep === 3)
                    <div class="space-y-8">
                        <div class="text-center mb-8">
                            <h2 class="text-2xl md:text-3xl lg:text-4xl font-display font-light text-dark mb-3">{{ $isWholeDayMode ? 'Choose Date' : 'Choose Date & Time' }}</h2>
                            <p class="text-gray-600">{{ $isWholeDayMode ? 'Select your preferred appointment date' : 'Select your preferred appointment slot' }}</p>
                        </div>
                        
                        <!-- Date Picker -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-3 uppercase tracking-wider">
                                Select Date
                            </label>
                            <input 
                                type="date" 
                                wire:model.live="selectedDate" 
                                min="{{ date('Y-m-d') }}"
                                class="w-full px-4 py-3 md:px-6 md:py-4 border-2 border-gray-200 focus:ring-2 focus:ring-primary focus:border-primary transition-all duration-200 text-base md:text-lg">
                            @error('selectedDate') 
                                <p class="text-red-500 text-sm mt-2">{{ $message }}</p> 
                            @enderror
                        </div>

                        <!-- Date Availability Message -->
                        @if ($selectedDate && $dateAvailableMessage)
                            <div class="bg-red-50 border-2 border-red-200 rounded-lg p-4 mb-6">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-red-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <div>
                                        <p class="text-red-700 font-medium">{{$dateAvailableMessage}}</p>
                                        <p class="text-red-600 text-sm mt-1">Please select another date for your selected service.</p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if ($isWholeDayMode && $selectedDate && !$dateAvailableMessage)
                            <div class="bg-blue-50 border-2 border-blue-200 rounded-lg p-4 mb-6">
                                <p class="text-blue-700 font-medium">Whole-day booking window: {{ $wholeDayWindow }}</p>
                            </div>
                        @endif

                        <!-- Time Slots -->
                        @if (! $isWholeDayMode && $selectedDate && count($availableTimes) > 0)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-4 uppercase tracking-wider">
                                    Select Time
                                </label>
                                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3">
                                    @foreach ($availableTimes as $key => $time)
                                        <label class="cursor-pointer" 
                                                for="time_slot_{{$key}}">
                                            <input 
                                                id="time_slot_{{$key}}"
                                                name="time_slot"
                                                type="radio" 
                                                wire:model.live="selectedTime" 
                                                value="{{ $time }}" 
                                                class="peer sr-only">
                                            <div class="p-4 text-center border-2 transition-all duration-300
                                                peer-checked:border-primary peer-checked:bg-primary peer-checked:text-white 
                                                hover:border-primary/50 border-gray-200">
                                                <span class="font-medium">{{ $time }}</span>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                                @error('selectedTime') 
                                    <p class="text-red-500 text-sm mt-2">{{ $message }}</p> 
                                @enderror
                            </div>
                        @endif

                        @if (! $isWholeDayMode && $selectedDate && count($availableTimes) === 0)
                            <div class="bg-yellow-50 border-2 border-yellow-200 rounded-lg p-4">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-yellow-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.314 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                    </svg>
                                    <div>
                                        <p class="text-yellow-700 font-medium">No time slots available</p>
                                        <p class="text-yellow-600 text-sm mt-1">All time slots for this date are fully booked.</p>
                                    </div>
                                </div>
                            </div>
                        @endif
 
                    </div>
                @endif

                <!-- Step 4: Personal Information -->
                @if ($currentStep === 4)
                    <div class="space-y-6">
                        <div class="text-center mb-8">
                            <h2 class="text-2xl md:text-3xl lg:text-4xl font-display font-light text-dark mb-3">Your Information</h2>
                            <p class="text-gray-600">Please provide your contact details</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2 uppercase tracking-wider">
                                Full Name *
                            </label>
                            <input 
                                type="text" 
                                wire:model="name" 
                                placeholder="John Doe"
                                class="w-full px-4 py-3 md:px-6 md:py-4 border-2 border-gray-200 focus:ring-2 focus:ring-primary focus:border-primary transition-all duration-200">
                            @error('name') 
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p> 
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2 uppercase tracking-wider">
                                Email Address *
                            </label>
                            <input 
                                type="email" 
                                wire:model="email" 
                                placeholder="john@example.com"
                                class="w-full px-4 py-3 md:px-6 md:py-4 border-2 border-gray-200 focus:ring-2 focus:ring-primary focus:border-primary transition-all duration-200">
                            @error('email') 
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p> 
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2 uppercase tracking-wider">
                                Phone Number *
                            </label>
                            <input 
                                type="tel" 
                                wire:model="phone" 
                                placeholder="+1 (555) 000-0000"
                                class="w-full px-4 py-3 md:px-6 md:py-4 border-2 border-gray-200 focus:ring-2 focus:ring-primary focus:border-primary transition-all duration-200">
                            @error('phone') 
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p> 
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2 uppercase tracking-wider">
                                Additional Notes (Optional)
                            </label>
                            <textarea 
                                wire:model="notes" 
                                rows="3" 
                                placeholder="Any special requests or information we should know..."
                                class="w-full px-4 py-3 md:px-6 md:py-4 border-2 border-gray-200 focus:ring-2 focus:ring-primary focus:border-primary transition-all duration-200"></textarea>
                            @error('notes') 
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p> 
                            @enderror
                        </div>
                    </div>
                @endif

                <!-- Step 5: Review & Confirm -->
                @if ($currentStep === 5 && !$bookingConfirmed)
                    <div class="space-y-8">
                        <div class="text-center mb-8">
                            <h2 class="text-2xl md:text-3xl lg:text-4xl font-display font-light text-dark mb-3">Review Your Booking</h2>
                            <p class="text-gray-600">Please confirm your appointment details</p>
                        </div>
                        
                        <div class="bg-light p-4 md:p-6 lg:p-8 space-y-4">
                             <div class="flex justify-between items-center pb-4 border-b border-gray-300">
                                 <span class="text-gray-600 uppercase tracking-wider text-sm">Service</span>
                                 <span class="font-display text-base md:text-lg text-dark">
                                     {{ collect($listings)->firstWhere('id', $selectedListing)['name'] ?? 'N/A' }}
                                 </span>
                             </div>
                            <div class="flex justify-between items-center pb-4 border-b border-gray-300">
                                <span class="text-gray-600 uppercase tracking-wider text-sm">Date</span>
                                <span class="font-display text-base md:text-lg text-dark">{{ \Carbon\Carbon::parse($selectedDate)->format('F d, Y') }}</span>
                            </div>
                            <div class="flex justify-between items-center pb-4 border-b border-gray-300">
                                <span class="text-gray-600 uppercase tracking-wider text-sm">Time</span>
                                <span class="font-display text-base md:text-lg text-dark">{{ $isWholeDayMode ? $wholeDayWindow : $selectedTime }}</span>
                            </div>
                            <div class="flex justify-between items-center pb-4 border-b border-gray-300">
                                <span class="text-gray-600 uppercase tracking-wider text-sm">Name</span>
                                <span class="font-display text-base md:text-lg text-dark">{{ $name }}</span>
                            </div>
                            <div class="flex justify-between items-center pb-4 border-b border-gray-300">
                                <span class="text-gray-600 uppercase tracking-wider text-sm">Email</span>
                                <span class="font-display text-base md:text-lg text-dark">{{ $email }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600 uppercase tracking-wider text-sm">Phone</span>
                                <span class="font-display text-base md:text-lg text-dark">{{ $phone }}</span>
                            </div>
                        </div>

                        <button 
                            wire:click="submitBooking" 
                            wire:loading.attr="disabled"
                            class="w-full bg-primary text-white py-5 px-8 text-sm tracking-widest uppercase font-medium hover:bg-secondary transition-all duration-300 transform hover:scale-[1.02] shadow-lg disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none">
                            
                            <span wire:loading.remove wire:target="submitBooking">
                                Confirm Booking
                            </span>
                            
                            <span wire:loading wire:target="submitBooking" class="flex items-center justify-center">
                                <svg class="animate-spin h-5 w-5 mr-3" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Processing...
                            </span>
                        </button>
                    </div>
                @endif

                <!-- Navigation Buttons -->
                @if ($currentStep < 5)
                    <div class="mt-8 md:mt-10 pt-6 md:pt-8 border-t border-gray-200">
                        <!-- Desktop: Side by side -->
                        <div class="hidden md:flex justify-between items-center">
                            @if ($currentStep > 1)
                                <button 
                                    wire:click="previousStep" 
                                    class="px-8 py-3 border-2 border-gray-300 text-gray-700 font-medium hover:bg-gray-50 transition-all duration-200 uppercase tracking-wider text-sm">
                                    ← Previous
                                </button>
                            @else
                                <div></div>
                            @endif

                            <button 
                                wire:click="nextStep" 
                                class="px-10 py-3 bg-primary text-white font-medium hover:bg-secondary transition-all duration-300 transform hover:scale-[1.02] uppercase tracking-wider text-sm">
                                Continue →
                            </button>
                        </div>
                        
                        <!-- Mobile: Stacked -->
                        <div class="md:hidden space-y-3">
                            @if ($currentStep > 1)
                                <button 
                                    wire:click="previousStep" 
                                    class="w-full px-6 py-3 border-2 border-gray-300 text-gray-700 font-medium hover:bg-gray-50 transition-all duration-200 uppercase tracking-wider text-sm">
                                    ← Previous
                                </button>
                            @endif

                            <button 
                                wire:click="nextStep" 
                                class="w-full px-6 py-3 bg-primary text-white font-medium hover:bg-secondary transition-all duration-300 transform hover:scale-[1.02] uppercase tracking-wider text-sm">
                                Continue →
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        @else
            <!-- Success Message -->
            <div class="text-center py-8 md:py-12 lg:py-16 bg-white shadow-2xl px-4 md:px-6 lg:px-8">
                <div class="w-24 h-24 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-8">
                    <svg class="w-12 h-12 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                
                <h2 class="text-3xl md:text-4xl font-display font-light text-dark mb-4">Booking Confirmed!</h2>
                <p class="text-gray-600 mb-2 text-lg">Your appointment has been successfully scheduled.</p>
                <p class="text-gray-600 mb-10">We've sent a confirmation email to <strong class="text-primary">{{ $email }}</strong></p>
                
                <div class="bg-light p-4 md:p-6 lg:p-8 mb-6 md:mb-8 lg:mb-10 text-left max-w-md mx-auto">
                    <h3 class="font-display text-xl text-dark mb-6 text-center">Appointment Details</h3>
                    <div class="space-y-3 text-gray-700">
                         <div class="flex justify-between">
                             <span class="font-medium">Service:</span>
                             <span>{{ collect($listings)->firstWhere('id', $selectedListing)['name'] }}</span>
                         </div>
                        <div class="flex justify-between">
                            <span class="font-medium">Date:</span>
                            <span>{{ \Carbon\Carbon::parse($selectedDate)->format('F d, Y') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium">Time:</span>
                            <span>{{ $isWholeDayMode ? $wholeDayWindow : $selectedTime }}</span>
                        </div>
                    </div>
                </div>

                <button 
                    wire:click="resetForm" 
                    class="px-10 py-4 bg-primary text-white font-medium hover:bg-secondary transition-all duration-300 uppercase tracking-wider text-sm">
                    Book Another Appointment
                </button>
            </div>
        @endif
    </div>
</div>


