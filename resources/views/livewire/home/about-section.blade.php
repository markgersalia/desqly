<div>
<section id="about" class="py-20 lg:py-32 bg-white">
    <div class="container mx-auto px-6 lg:px-12">
        
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
            
            <p class="text-secondary text-sm tracking-[0.3em] uppercase mb-4 font-light">Welcome to</p>
            <h2 class="text-4xl md:text-5xl lg:text-6xl font-display font-light text-dark">
                Beauty and <span class="italic">Spa Center</span>
            </h2>
        </div>

        <!-- Content Grid -->
        <div class="grid lg:grid-cols-2 gap-12 lg:gap-20 items-center">
            
            <!-- Text Content -->
            <div class="scroll-animate order-2 lg:order-1">
                <p class="text-dark/70 text-base lg:text-lg leading-relaxed mb-6">
                    At {{config('app.name')}}, we believe that true beauty radiates from within. Our sanctuary offers a harmonious blend of traditional wellness practices and modern spa treatments, designed to rejuvenate your body, mind, and spirit.
                </p>
                
                <p class="text-dark/70 text-base lg:text-lg leading-relaxed mb-8">
                    Step into an oasis of tranquility where every detail has been carefully crafted to ensure your complete relaxation. From our expertly trained therapists to our premium organic products, we are committed to providing you with an unforgettable wellness journey.
                </p>

                <!-- Features -->
                <div class="space-y-6 mb-10">
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0 w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-display text-xl text-dark mb-1">Expert Therapists</h4>
                            <p class="text-dark/60 text-sm">Certified professionals with years of experience in wellness and beauty care</p>
                        </div>
                    </div>

                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0 w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-display text-xl text-dark mb-1">Premium Products</h4>
                            <p class="text-dark/60 text-sm">Only the finest organic and natural products for your treatments</p>
                        </div>
                    </div>

                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0 w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-display text-xl text-dark mb-1">Tranquil Environment</h4>
                            <p class="text-dark/60 text-sm">A peaceful sanctuary designed for complete relaxation and renewal</p>
                        </div>
                    </div>
                </div>

                <!-- CTA Button -->
                <a href="#services" 
                   class="inline-flex items-center px-8 py-4 bg-primary text-white text-sm tracking-wider uppercase font-medium hover:bg-secondary transition-all duration-300 group">
                    Explore Services
                    <svg class="w-4 h-4 ml-3 group-hover:translate-x-2 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                    </svg>
                </a>
            </div>

            <!-- Image Gallery -->
            <div class="scroll-animate order-1 lg:order-2">
                <div class="relative">
                    <!-- Main Image -->
                    <div class="relative overflow-hidden rounded-sm">
                        <img src="https://images.unsplash.com/photo-1544161515-4ab6ce6db874?q=80&w=2070" 
                             alt="Spa Treatment" 
                             class="w-full h-[500px] object-cover transition-transform duration-700 hover:scale-110">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent"></div>
                    </div>

                    <!-- Decorative Frame -->
                    <div class="absolute -bottom-6 -right-6 w-48 h-48 border-2 border-primary/30 -z-10"></div>
                    <div class="absolute -top-6 -left-6 w-32 h-32 border-2 border-accent/30 -z-10"></div>

                    <!-- Floating Card -->
                    <div class="absolute -bottom-8 -left-8 bg-white p-6 shadow-2xl max-w-xs hidden lg:block">
                        <div class="flex items-center space-x-4">
                            <div class="flex-shrink-0">
                                <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center">
                                    <svg class="w-8 h-8 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                            </div>
                            <div>
                                <h5 class="font-display text-2xl text-primary font-semibold">15+</h5>
                                <p class="text-dark/60 text-sm">Years Experience</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
</div>
