<div>
    <section class="relative h-screen min-h-[700px] flex items-center justify-center overflow-hidden">
        
        <!-- Parallax Background -->
        <div class="absolute inset-0 parallax-bg" 
             style="background-image: url('https://images.unsplash.com/photo-1540555700478-4be289fbecef?q=80&w=2070'); 
                    background-size: cover; 
                    background-position: center;
                    background-attachment: fixed;">
        </div>
        
        <!-- Overlay -->
        <div class="absolute inset-0 bg-black/50"></div>

        <!-- Content -->
        <div class="relative z-10 container mx-auto px-6 lg:px-12 text-center">
            
            <!-- Decorative Icon -->
            <div class="mb-8 inline-block">
                <svg class="w-16 h-16 text-primary" viewBox="0 0 50 50" fill="currentColor">
                    <circle cx="25" cy="25" r="3"/>
                    <circle cx="25" cy="15" r="2.5" opacity="0.8"/>
                    <circle cx="25" cy="35" r="2.5" opacity="0.8"/>
                    <circle cx="15" cy="25" r="2.5" opacity="0.8"/>
                    <circle cx="35" cy="25" r="2.5" opacity="0.8"/>
                </svg>
            </div>

            <!-- Subtitle -->
            <p class="text-white/90 text-sm lg:text-base tracking-[0.3em] uppercase font-light mb-6">
                Welcome to {{env('APP_NAME')}}
            </p>

            <!-- Main Heading -->
            <h1 class="text-5xl md:text-7xl lg:text-8xl font-display font-light text-white mb-6 leading-tight">
                Your SPA Appointment
                {{-- Beauty Centre
                <span class="block italic font-normal mt-2">& Spa</span> --}}
                <span class="block italic font-normal mt-2">Solution</span>
                {{-- {{env("APP_TAGLINE")}} --}}
            </h1>

            <!-- Description -->
            <div class="max-w-2xl mx-auto mb-12">
                <p class="text-white/80 text-lg lg:text-xl leading-relaxed font-light">
                    Your beauty truly matters to us. Experience tranquility and rejuvenation 
                    at <span class="italic text-primary">{{config('app.name')}}</span>.
                </p>
            </div>

            <!-- CTA Buttons -->
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 sm:gap-6">
                <a href="#services" 
                   class="group px-10 py-4 bg-primary text-white text-sm tracking-widest uppercase font-medium hover:bg-white hover:text-dark transition-all duration-300 border-2 border-primary hover:border-white inline-flex items-center">
                    Explore Services
                    <svg class="w-4 h-4 ml-3 group-hover:translate-x-2 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
                <a href="#booking" 
                   class="px-10 py-4 bg-transparent text-white text-sm tracking-widest uppercase font-medium hover:bg-white hover:text-dark transition-all duration-300 border-2 border-white">
                    Book Now
                </a>
            </div>

             
        </div>
    </section>
</div>
