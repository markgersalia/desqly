<section id="services" class="py-20 lg:py-32">
    <div class="container mx-auto px-6 lg:px-12">
        
        <!-- Services Grid -->
        <div class="grid md:grid-cols-3 gap-8 lg:gap-12">
            @foreach($services as $index => $service)
                <div class="scroll-animate group" style="animation-delay: {{ $index * 0.2 }}s;">
                    <!-- Image Container -->
                    <div class="relative overflow-hidden mb-6 hover-zoom">
                        <img src="{{ $service['image'] }}" 
                             alt="{{ $service['title'] }}" 
                             class="w-full h-80 object-cover">
                        
                        <!-- Overlay on Hover -->
                        <div class="absolute inset-0 bg-primary/90 opacity-0 group-hover:opacity-100 transition-opacity duration-500 flex items-center justify-center">
                            <a href="{{ $service['link'] }}" 
                               class="px-6 py-3 border-2 border-white text-white text-sm tracking-wider uppercase font-medium hover:bg-white hover:text-primary transition-all">
                                Learn More
                            </a>
                        </div>

                        <!-- Corner Decoration -->
                        <div class="absolute top-0 right-0 w-16 h-16 border-t-2 border-r-2 border-white opacity-0 group-hover:opacity-60 transition-opacity duration-500"></div>
                    </div>

                    <!-- Content -->
                    <div class="text-center">
                        <h3 class="text-2xl font-display text-dark mb-3 group-hover:text-primary transition-colors">
                            {{ $service['title'] }}
                        </h3>
                        <p class="text-dark/70 text-sm leading-relaxed mb-4">
                            {{ $service['description'] }}
                        </p>
                        <a href="{{ $service['link'] }}" 
                           class="inline-flex items-center text-sm text-primary hover:text-secondary uppercase tracking-wider font-medium group/link">
                            Read More
                            <svg class="w-4 h-4 ml-2 group-hover/link:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
