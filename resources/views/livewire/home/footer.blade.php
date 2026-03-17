<footer id="contact" class="bg-dark text-white/80 pt-20 pb-10">
    <div class="container mx-auto px-6 lg:px-12">
        
        <!-- Main Footer Content -->
        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-12 mb-16">
            
            <!-- About Column -->
            <div>
                <div class="mb-6">
                    <img src="{{asset('images/dark-logo.png')}}" style="width: 140px" alt="">
                    <p class="text-xs tracking-widest uppercase text-primary">{{env('APP_TAGLINE')}}</p>
                </div>
                <p class="text-white/70 text-sm leading-relaxed mb-6">
                    Step into an oasis of magnificence we devised for your wellness journey. We are sure you'll love your time with {{config('app.name')}}.
                </p>
                <a href="#booking" 
                   class="inline-block px-6 py-3 bg-primary text-white text-xs tracking-wider uppercase font-medium hover:bg-secondary transition-all duration-300">
                    Book Online
                </a>
            </div>

            <!-- Quick Links -->
            <div>
                <h4 class="text-lg font-display text-white mb-6 border-b border-white/10 pb-3">Quick Links</h4>
                <ul class="space-y-3">
                    <li>
                        <a href="#services" class="text-sm hover:text-primary transition-colors inline-flex items-center group">
                            <svg class="w-4 h-4 mr-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                            Our Services
                        </a>
                    </li>
                    <li>
                        <a href="#treatments" class="text-sm hover:text-primary transition-colors inline-flex items-center group">
                            <svg class="w-4 h-4 mr-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                            Treatments & Pricing
                        </a>
                    </li>
                    <li>
                        <a href="#about" class="text-sm hover:text-primary transition-colors inline-flex items-center group">
                            <svg class="w-4 h-4 mr-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                            About Us
                        </a>
                    </li>
                    <li>
                        <a href="#testimonials" class="text-sm hover:text-primary transition-colors inline-flex items-center group">
                            <svg class="w-4 h-4 mr-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                            Testimonials
                        </a>
                    </li>
                    <li>
                        <a href="#booking" class="text-sm hover:text-primary transition-colors inline-flex items-center group">
                            <svg class="w-4 h-4 mr-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                            Book Appointment
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Contact Info -->
            <div>
                <h4 class="text-lg font-display text-white mb-6 border-b border-white/10 pb-3">Contact Us</h4>
                <ul class="space-y-4">
                    <li class="flex items-start space-x-3">
                        <svg class="w-5 h-5 text-primary flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <div class="text-sm">
                            <p class="text-white/90">123 Wellness Street</p>
                            <p>Downtown, City 12345</p>
                        </div>
                    </li>
                    <li class="flex items-start space-x-3">
                        <svg class="w-5 h-5 text-primary flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <a href="mailto:info@hbcwellness.com" class="text-sm hover:text-primary transition-colors">
                            info@hbcwellness.com
                        </a>
                    </li>
                    <li class="flex items-start space-x-3">
                        <svg class="w-5 h-5 text-primary flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                        <a href="tel:+12345678900" class="text-sm hover:text-primary transition-colors">
                            +1 (234) 567-8900
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Opening Hours -->
            <div>
                <h4 class="text-lg font-display text-white mb-6 border-b border-white/10 pb-3">Opening Hours</h4>
                <ul class="space-y-3 text-sm">
                    <li class="flex justify-between">
                        <span class="text-white/90">Monday - Friday</span>
                        <span class="text-primary">09:00 - 20:00</span>
                    </li>
                    <li class="flex justify-between">
                        <span class="text-white/90">Saturday</span>
                        <span class="text-primary">09:00 - 18:00</span>
                    </li>
                    <li class="flex justify-between">
                        <span class="text-white/90">Sunday</span>
                        <span class="text-primary">10:00 - 16:00</span>
                    </li>
                </ul>

                <!-- Social Media -->
                <div class="mt-8">
                    <h5 class="text-sm font-display text-white mb-4">Follow Us</h5>
                    <div class="flex space-x-3">
                        <a href="#" class="w-10 h-10 rounded-full border border-white/20 flex items-center justify-center hover:bg-primary hover:border-primary transition-all duration-300">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                        </a>
                        <a href="#" class="w-10 h-10 rounded-full border border-white/20 flex items-center justify-center hover:bg-primary hover:border-primary transition-all duration-300">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                            </svg>
                        </a>
                        <a href="#" class="w-10 h-10 rounded-full border border-white/20 flex items-center justify-center hover:bg-primary hover:border-primary transition-all duration-300">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Newsletter -->
        <div class="border-t border-white/10 py-12">
            <div class="max-w-2xl mx-auto text-center">
                <h4 class="text-2xl font-display text-white mb-4">Join Our Newsletter</h4>
                <p class="text-white/70 text-sm mb-6">Subscribe to receive exclusive offers and wellness tips</p>
                <form class="flex flex-col sm:flex-row gap-3 max-w-md mx-auto">
                    <input type="email" 
                           placeholder="Your email address" 
                           class="flex-1 px-6 py-3 bg-white/10 border border-white/20 text-white placeholder-white/50 focus:outline-none focus:border-primary transition-colors">
                    <button type="submit" 
                            class="px-8 py-3 bg-primary text-white text-sm tracking-wider uppercase font-medium hover:bg-secondary transition-all duration-300">
                        Subscribe
                    </button>
                </form>
            </div>
        </div>

        <!-- Bottom Bar -->
        <div class="border-t border-white/10 pt-8">
            <div class="flex flex-col md:flex-row justify-between items-center text-sm text-white/60">
                <p>© 2025 {{config('app.name')}}. All Rights Reserved.</p>
                <div class="flex space-x-6 mt-4 md:mt-0">
                    <a href="#" class="hover:text-primary transition-colors">Privacy Policy</a>
                    <a href="#" class="hover:text-primary transition-colors">Terms of Service</a>
                    <a href="#" class="hover:text-primary transition-colors">Sitemap</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Back to Top Button -->
    <a href="#" 
       class="fixed bottom-8 right-8 w-12 h-12 bg-primary text-white rounded-full flex items-center justify-center hover:bg-secondary transition-all duration-300 shadow-lg z-50"
       x-data="{ show: false }"
       @scroll.window="show = window.scrollY > 500"
       x-show="show"
       x-transition
       style="display: none;">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
        </svg>
    </a>
</footer>
