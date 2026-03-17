<header class="fixed top-0 left-0 right-0 z-50 transition-all duration-500"
    x-data="{ scrolled: false, mobileOpen: false }" @scroll.window="scrolled = window.scrollY > 50"
    :class="scrolled ? 'bg-white shadow-lg' : 'bg-transparent'">
<div class="container mx-auto px-4 sm:px-6 lg:px-12 max-w-full overflow-hidden">
    <div class="flex items-center justify-between transition-all duration-500"
         :class="scrolled ? 'py-3 lg:py-4' : 'py-6 lg:py-8'">
        
            <!-- Logo with scroll-based switching -->
            <a href="/" class="flex items-center space-x-3 group flex-shrink-0">
                <div class="relative flex items-center" style="width: 150px; height: 40px;">
                    <!-- White logo for transparent header (shown when NOT scrolled) -->
                    <img src="{{ asset('images/dark-logo.png') }}" style="width: 50px" alt="{{config('app.name')}}"
                        x-show="!scrolled" x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                        x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0" class="absolute left-0">

                    <!-- Colored logo for white background (shown when scrolled) -->
                    <img src="{{ asset('images/logo.png') }}" style="width: 50px" alt="{{config('app.name')}}"
                        x-show="scrolled" x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                        x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0">
                </div>
            </a>

            <!-- Desktop Navigation -->
            <nav class="hidden lg:flex items-center space-x-5">
                <a href="/" class="text-sm tracking-wider uppercase font-medium hover:text-primary transition-colors"
                    :class="scrolled ? 'text-dark' : 'text-white'">
                    Home
                </a>
                <a href="#about"
                    class="text-sm tracking-wider uppercase font-medium hover:text-primary transition-colors"
                    :class="scrolled ? 'text-dark' : 'text-white'">
                    About
                </a>
                <a href="#services"
                    class="text-sm tracking-wider uppercase font-medium hover:text-primary transition-colors"
                    :class="scrolled ? 'text-dark' : 'text-white'">
                    Services
                </a>
                <a href="#treatments"
                    class="text-sm tracking-wider uppercase font-medium hover:text-primary transition-colors"
                    :class="scrolled ? 'text-dark' : 'text-white'">
                    Treatments
                </a>
                <a href="#testimonials"
                    class="text-sm tracking-wider uppercase font-medium hover:text-primary transition-colors"
                    :class="scrolled ? 'text-dark' : 'text-white'">
                    Testimonials
                </a>
                <a href="#contact"
                    class="text-sm tracking-wider uppercase font-medium hover:text-primary transition-colors"
                    :class="scrolled ? 'text-dark' : 'text-white'">
                    Contact
                </a>
            </nav>

<!-- Theme Toggle Switch -->
            <div class="hidden lg:flex items-center flex-shrink-0">
                <div class="relative">
                    <label class="flex items-center cursor-pointer group">
                        <!-- Light Theme Icon -->
                        <svg class="w-4 h-4 text-white transition-all duration-300 flex-shrink-0" 
                             :class="scrolled ? 'text-dark' : 'text-white'"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        
                        <!-- Toggle Switch -->
                        <div class="relative mx-2 w-12 h-6 bg-gray-200 rounded-full transition-colors duration-300 cursor-pointer"
                             :class="scrolled ? 'bg-gray-200' : 'bg-white/20'"
                             onclick="window.location.href = window.location.pathname === '/' ? '/home2' : '/'">
                            <div class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow-md transform transition-transform duration-300"
                                 :class="window.location.pathname === '/home2' ? 'translate-x-6 bg-primary' : 'translate-x-0'">
                            </div>
                        </div>
                        
                        <!-- Dark Theme Icon -->
                        <svg class="w-4 h-4 text-white transition-all duration-300 flex-shrink-0" 
                             :class="scrolled ? 'text-dark' : 'text-white'"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                        </svg>
                    </label>
                </div>
            </div>

            <!-- CTA Button -->
            <div class="hidden lg:block">
                <a href="#booking"
                    class="px-8 py-3 bg-primary text-white text-sm tracking-wider uppercase font-medium hover:bg-secondary transition-all duration-300 inline-block border-2 border-primary hover:border-secondary">
                    Book Now
                </a>
            </div>

            <!-- Mobile Menu Toggle -->
            <button @click="mobileOpen = !mobileOpen" class="lg:hidden p-2"
                :class="scrolled ? 'text-dark' : 'text-white'">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path x-show="!mobileOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 6h16M4 12h16M4 18h16" />
                    <path x-show="mobileOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Mobile Navigation -->
        <div x-show="mobileOpen" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform -translate-y-2"
            x-transition:enter-end="opacity-100 transform translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 transform translate-y-0"
            x-transition:leave-end="opacity-0 transform -translate-y-2"
            class="lg:hidden pb-6 bg-white rounded-b-xl shadow-xl" style="display: none;">
            <nav class="flex flex-col space-y-4 px-6">
                <a href="/"
                    class="text-sm tracking-wider uppercase font-medium text-dark hover:text-primary transition-colors py-2 border-b border-light">Home</a>
                <a href="#about"
                    class="text-sm tracking-wider uppercase font-medium text-dark hover:text-primary transition-colors py-2 border-b border-light">About</a>
                <a href="#services"
                    class="text-sm tracking-wider uppercase font-medium text-dark hover:text-primary transition-colors py-2 border-b border-light">Services</a>
                <a href="#treatments"
                    class="text-sm tracking-wider uppercase font-medium text-dark hover:text-primary transition-colors py-2 border-b border-light">Treatments</a>
                <a href="#testimonials"
                    class="text-sm tracking-wider uppercase font-medium text-dark hover:text-primary transition-colors py-2 border-b border-light">Testimonials</a>
                <a href="#contact"
                    class="text-sm tracking-wider uppercase font-medium text-dark hover:text-primary transition-colors py-2">Contact</a>
                <a href="#booking"
                    class="mt-4 px-6 py-3 bg-primary text-white text-sm tracking-wider uppercase font-medium hover:bg-secondary transition-all text-center">
                    Book Now
                </a>
                
                <!-- Mobile Theme Toggle -->
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-dark">Theme</span>
                        <div class="relative">
                            <label class="flex items-center cursor-pointer">
                                <!-- Light Theme Icon -->
                                <svg class="w-3 h-3 text-dark flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                                
                                <!-- Toggle Switch -->
                                <div class="relative mx-2 w-10 h-5 bg-gray-200 rounded-full transition-colors duration-300 cursor-pointer"
                                     onclick="window.location.href = window.location.pathname === '/' ? '/home2' : '/'">
                                    <div class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full shadow-sm transform transition-transform duration-300"
                                         :class="window.location.pathname === '/home2' ? 'translate-x-5 bg-primary' : 'translate-x-0'">
                                    </div>
                                </div>
                                
                                <!-- Dark Theme Icon -->
                                <svg class="w-3 h-3 text-dark flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                                </svg>
                            </label>
                        </div>
                    </div>
                </div>
            </nav>
        </div>
    </div>
</header>