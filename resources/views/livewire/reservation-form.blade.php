<div class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-purple-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-3xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-900 mb-2">Book Your Appointment</h1>
            <p class="text-gray-600">Choose your service and pick a convenient time</p>
        </div>

        <!-- Progress Bar -->
        <div class="mb-8">
            <div class="flex items-center justify-between mb-2">
                @for ($i = 1; $i <= $totalSteps; $i++)
                    <div class="flex items-center {{ $i < $totalSteps ? 'flex-1' : '' }}">
                        <div class="relative">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center transition-all duration-300 {{ $currentStep >= $i ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-600' }}">
                                @if ($currentStep > $i)
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                @else
                                    {{ $i }}
                                @endif
                            </div>
                            <span class="absolute -bottom-6 left-1/2 transform -translate-x-1/2 text-xs text-gray-600 whitespace-nowrap">
                                @if ($i == 1) Service
                                @elseif ($i == 2) Date & Time
                                @elseif ($i == 3) Details
                                @else Confirm
                                @endif
                            </span>
                        </div>
                        @if ($i < $totalSteps)
                            <div class="flex-1 h-1 mx-2 {{ $currentStep > $i ? 'bg-indigo-600' : 'bg-gray-200' }} transition-all duration-300"></div>
                        @endif
                    </div>
                @endfor
            </div>
        </div>

        <form wire:submit.prevent="submit" class="space-y-4">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                <div>
                    <label class="block text-sm font-medium text-gray-700">Select Date</label>
                    <input type="date" wire:model.live="selected_date" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm
                   focus:outline-none focus:ring-amber-500 focus:border-amber-500 sm:text-sm">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Available Time Slot</label>

<select wire:model.live="time_slot" class="mt-1 block w-full ">
                        <option value="">Select time</option>

                        @foreach ($this->availableTimeSlots as $key => $value)
                        <option value="{{ $key }}">{{ $value }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div>
                <label for="service-name" class="block text-sm font-medium text-gray-700">Your Name</label>
                <input type="text" name="service-name" id="service-name" autocomplete="name" required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-amber-500 focus:border-amber-500 sm:text-sm"
                    placeholder="Your Full Name">
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="service-email" class="block text-sm font-medium text-gray-700">Email Address</label>
                    <input type="email" name="service-email" id="service-email" autocomplete="email" required
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-amber-500 focus:border-amber-500 sm:text-sm"
                        placeholder="you@example.com">
                </div>
                <div>
                    <label for="service-phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                    <input type="tel" name="service-phone" id="service-phone" autocomplete="tel" required
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-amber-500 focus:border-amber-500 sm:text-sm"
                        placeholder="(123) 456-7890">
                </div>
            @endif
        </div>

        <!-- Additional Info -->
        <div class="text-center mt-8 text-gray-600 text-sm">
            <p>Need help? Contact us at <a href="mailto:support@example.com" class="text-indigo-600 hover:text-indigo-700">support@example.com</a></p>
        </div>
    </div>

    <style>
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in {
            animation: fadeIn 0.4s ease-out;
        }
    </style>
</div>
