@extends('layouts.app2')

@section('content')
    @livewire('hero')
    @livewire('about-section')
    @livewire('services')
    @livewire('treatments')
    @livewire('testimonials')
    
    <!-- Booking Section Integration -->
    <section id="booking" class="py-20 lg:py-32 bg-light">
        <div class="container mx-auto px-6 lg:px-12">
            <div class="text-center mb-12">
                <div class="flex justify-center mb-6">
                    <svg class="w-16 h-16 text-primary" viewBox="0 0 50 50" fill="currentColor">
                        <circle cx="25" cy="25" r="3"/>
                        <circle cx="25" cy="15" r="2.5" opacity="0.8"/>
                        <circle cx="25" cy="35" r="2.5" opacity="0.8"/>
                        <circle cx="15" cy="25" r="2.5" opacity="0.8"/>
                        <circle cx="35" cy="25" r="2.5" opacity="0.8"/>
                    </svg>
                </div>
                
                <p class="text-secondary text-sm tracking-[0.3em] uppercase mb-4 font-light">Reserve Your Time</p>
                <h2 class="text-4xl md:text-5xl lg:text-6xl font-display font-light text-dark">
                    Book Your <span class="itlic">Appointment</span>
                </h2>
            </div>
            
            <!-- Include the booking form component here -->
            @livewire('booking-form')
        </div>
    </section>

    <style>
        /* Additional dark theme overrides for this page */
        body {
            background-color: var(--color-light) !important;
        }
        
        /* Force dark backgrounds for sections */
        #about, 
        #treatments, 
        #services,
        #testimonials {
            background-color: var(--color-light) !important;
        }

        /* Override any white backgrounds in Livewire components */
        .bg-white {
            background-color: #242924 !important;
        }

        /* Ensure text is readable on dark backgrounds */
        .text-white {
            color: var(--color-dark) !important;
        }

        /* Specific component overrides */
        #about .bg-white {
            background-color: #242924 !important;
        }

        #treatments .bg-white {
            background-color: #242924 !important;
        }

        #services .bg-white {
            background-color: #242924 !important;
        }

        #testimonials .bg-white {
            background-color: #242924 !important;
        }

        /* Hover effects */
        .hover\:bg-white:hover {
            background-color: #2a302a !important;
        }

        /* Treatment cards */
        .bg-light.p-8 {
            background-color: #242924 !important;
        }

        /* Wellness package */
        .bg-gradient-to-r {
            background: linear-gradient(to right, var(--color-primary), var(--color-secondary)) !important;
        }
    </style>
@endsection