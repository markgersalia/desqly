<section id="treatments" class="py-20 lg:py-32 bg-white relative overflow-hidden">
    
    <!-- Background Decorations -->
    <div class="absolute top-0 right-0 w-96 h-96 opacity-5">
        <svg viewBox="0 0 200 200" fill="currentColor" class="text-primary">
            <circle cx="100" cy="100" r="80"/>
            <circle cx="100" cy="100" r="60" fill="white"/>
            <circle cx="100" cy="100" r="40" fill="currentColor"/>
        </svg>
    </div>

    <div class="container mx-auto px-6 lg:px-12 relative z-10">
        
        <!-- Section Header -->
        <div class="text-center mb-16 scroll-animate">
            <div class="flex justify-center mb-6">
                <svg class="w-16 h-16 text-primary" viewBox="0 0 50 50" fill="currentColor">
                    <circle cx="25" cy="25" r="3"/>
                    <circle cx="25" cy="15" r="2.5" opacity="0.8"/>
                    <circle cx="25" cy="35" r="2.5" opacity="0.8"/>
                    <circle cx="15" cy="25" r="2.5" opacity="0.8"/>
                    <circle cx="35" cy="25" r="2.5" opacity="0.8"/>
                </svg>
            </div>
            
            <p class="text-secondary text-sm tracking-[0.3em] uppercase mb-4 font-light">Extraordinary</p>
            <h2 class="text-4xl md:text-5xl lg:text-6xl font-display font-light text-dark mb-4">
                Your <span class="italic">Wellbeing</span>
            </h2>
            <p class="text-dark/70 max-w-2xl mx-auto text-base lg:text-lg leading-relaxed">
                Experience our curated selection of wellness treatments designed to restore balance and enhance your natural beauty.
            </p>
        </div>

        <!-- Treatments Grid -->
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8 mb-16">
            @foreach($treatments as $index => $treatment)
                <div class="scroll-animate bg-light p-8 hover:bg-white hover:shadow-xl transition-all duration-500 group" 
                     style="animation-delay: {{ $index * 0.1 }}s;">
                    
                    <!-- Icon -->
                    <div class="w-16 h-16 mb-6 relative">
                        <div class="absolute inset-0 bg-primary/10 rounded-full group-hover:scale-110 transition-transform duration-500"></div>
                        <div class="absolute inset-2 bg-primary/20 rounded-full group-hover:scale-110 transition-transform duration-500" style="transition-delay: 0.1s;"></div>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <svg class="w-8 h-8 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                            </svg>
                        </div>
                    </div>

                    <!-- Content -->
                    <h3 class="font-display text-2xl text-dark mb-3 group-hover:text-primary transition-colors">
                        {{ $treatment['name'] }}
                    </h3>
                    
                    <p class="text-dark/70 text-sm leading-relaxed mb-4">
                        {{ $treatment['description'] }}
                    </p>

                    <!-- Price -->
                    <div class="flex items-baseline space-x-2">
                        <span class="text-sm text-secondary uppercase tracking-wider">from</span>
                        <span class="text-3xl font-display font-semibold text-primary">${{ $treatment['price'] }}</span>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Wellness Package CTA -->
        <div class="scroll-animate bg-gradient-to-r from-primary to-secondary p-12 lg:p-16 text-center text-white">
            <h3 class="text-3xl lg:text-4xl font-display font-light mb-4">
                A Place of True <span class="italic">Splendor</span>
            </h3>
            <p class="text-white/90 max-w-2xl mx-auto mb-8 text-base lg:text-lg">
                Discover our exclusive wellness packages designed to provide you with the ultimate spa experience.
            </p>
            <a href="#booking" 
               class="inline-block px-10 py-4 bg-white text-primary text-sm tracking-wider uppercase font-medium hover:bg-light transition-all duration-300">
                Book Now
            </a>
        </div>
    </div>
</section>
