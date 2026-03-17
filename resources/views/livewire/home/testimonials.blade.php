<section id="testimonials" class="py-20 lg:py-32 bg-gradient-to-b from-light to-white relative overflow-hidden">
    
    <!-- Background Pattern -->
    <div class="absolute inset-0 opacity-5">
        <div class="absolute top-1/4 left-1/4 w-64 h-64 border border-primary rounded-full"></div>
        <div class="absolute bottom-1/4 right-1/4 w-48 h-48 border border-accent rounded-full"></div>
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
            
            <p class="text-secondary text-sm tracking-[0.3em] uppercase mb-4 font-light">Client Stories</p>
            <h2 class="text-4xl md:text-5xl lg:text-6xl font-display font-light text-dark">
                What Our <span class="italic">Guests Say</span>
            </h2>
        </div>

        <!-- Testimonials Carousel -->
        <div class="max-w-4xl mx-auto">
            <div class="relative">
                
                <!-- Testimonial Cards -->
                <div class="overflow-hidden">
                    @foreach($testimonials as $index => $testimonial)
                        <div wire:key="testimonial-{{ $index }}"
                             class="transition-all duration-700 {{ $currentIndex === $index ? 'opacity-100 block' : 'opacity-0 hidden' }}">
                            
                            <div class="bg-white p-10 lg:p-16 shadow-xl relative">
                                
                                <!-- Quote Icon -->
                                <div class="absolute top-8 left-8 opacity-10">
                                    <svg class="w-20 h-20 text-primary" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M6.5 10c-2.5 0-4.5 2-4.5 4.5S4 19 6.5 19c1.5 0 2.8-.7 3.6-1.8-.5 1.5-1.9 2.8-3.6 2.8v2c3.9 0 7-3.1 7-7 0-2.5-2-4.5-4.5-4.5zm10 0c-2.5 0-4.5 2-4.5 4.5s2 4.5 4.5 4.5c1.5 0 2.8-.7 3.6-1.8-.5 1.5-1.9 2.8-3.6 2.8v2c3.9 0 7-3.1 7-7 0-2.5-2-4.5-4.5-4.5z"/>
                                    </svg>
                                </div>

                                <!-- Stars -->
                                <div class="flex justify-center mb-6">
                                    @for($i = 0; $i < $testimonial['rating']; $i++)
                                        <svg class="w-5 h-5 text-primary" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    @endfor
                                </div>

                                <!-- Content -->
                                <p class="text-dark/80 text-lg lg:text-xl leading-relaxed text-center mb-8 font-light italic">
                                    "{{ $testimonial['content'] }}"
                                </p>

                                <!-- Author -->
                                <div class="text-center">
                                    <h4 class="font-display text-2xl text-dark mb-1">
                                        {{ $testimonial['name'] }}
                                    </h4>
                                    <p class="text-secondary text-sm uppercase tracking-wider">
                                        {{ $testimonial['service'] }}
                                    </p>
                                </div>

                                <!-- Decorative Lines -->
                                <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 w-32 h-px bg-primary/30"></div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Navigation Arrows -->
                <button wire:click="previousTestimonial"
                        class="absolute left-0 top-1/2 -translate-y-1/2 -translate-x-6 lg:-translate-x-16 w-12 h-12 rounded-full bg-white shadow-lg flex items-center justify-center text-primary hover:bg-primary hover:text-white transition-all duration-300 group">
                    <svg class="w-6 h-6 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>

                <button wire:click="nextTestimonial"
                        class="absolute right-0 top-1/2 -translate-y-1/2 translate-x-6 lg:translate-x-16 w-12 h-12 rounded-full bg-white shadow-lg flex items-center justify-center text-primary hover:bg-primary hover:text-white transition-all duration-300 group">
                    <svg class="w-6 h-6 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>

            <!-- Dots Navigation -->
            <div class="flex justify-center space-x-3 mt-10">
                @foreach($testimonials as $index => $testimonial)
                    <button wire:click="$set('currentIndex', {{ $index }})"
                            class="w-3 h-3 rounded-full transition-all duration-300 {{ $currentIndex === $index ? 'bg-primary w-8' : 'bg-primary/30 hover:bg-primary/60' }}">
                    </button>
                @endforeach
            </div>
        </div>
    </div>
</section>
