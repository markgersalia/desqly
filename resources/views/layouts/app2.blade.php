<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{config('app.name')}} - Beauty & Spa Center</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Google Fonts - Elegant Serif and Sans combinations -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;500;600;700&family=Lora:wght@400;500;600;700&family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    @livewireStyles
    
    <style>
        /* :root {
            --color-primary: #B8936D;
            --color-secondary: #8B7355;
            --color-dark: #2C2C2C;
            --color-light: #F8F6F3;
            --color-accent: #D4AF7A;
        } */

        :root {
        /* Main brand (sage green) */
        --color-primary: #7e8f7f;

        /* Slightly darker for hover / emphasis */
        --color-secondary: #6f806f;

        /* Text, sidebar, strong contrast - INVERTED for dark theme */
        --color-dark: #f3f5f3;

        /* Page background / cards - DARK sage */
        --color-light: #1a1f1a;

        /* Accent (soft gold-sage highlight) */
        --color-accent: #a9b5a9;
    }

        body {
            font-family: 'Montserrat', sans-serif;
            color: var(--color-dark);
            background-color: var(--color-light);
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: 'Cormorant Garamond', serif;
            font-weight: 500;
        }

        .font-display {
            font-family: 'Lora', serif;
        }

        /* Smooth scroll */
        html {
            scroll-behavior: smooth;
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 10px;
        }

        ::-webkit-scrollbar-track {
            background: var(--color-light);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--color-primary);
            border-radius: 5px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--color-secondary);
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes scaleIn {
            from {
                opacity: 0;
                transform: scale(0.95);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .animate-fade-in-up {
            animation: fadeInUp 0.8s ease-out forwards;
        }

        .animate-fade-in {
            animation: fadeIn 1s ease-out forwards;
        }

        .animate-scale-in {
            animation: scaleIn 0.6s ease-out forwards;
        }

        /* Decorative elements */
        .decorative-line {
            position: relative;
            display: inline-block;
        }

        .decorative-line::before,
        .decorative-line::after {
            content: '';
            position: absolute;
            top: 50%;
            width: 60px;
            height: 1px;
            background: var(--color-primary);
        }

        .decorative-line::before {
            right: 100%;
            margin-right: 20px;
        }

        .decorative-line::after {
            left: 100%;
            margin-left: 20px;
        }

        /* Overlay effects */
        .overlay-gradient {
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(0,0,0,0.3) 0%, rgba(0,0,0,0.6) 100%);
        }

        /* Transition utilities */
        .transition-all-slow {
            transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Parallax effect */
        .parallax-bg {
            background-attachment: fixed;
            background-position: center;
            background-repeat: no-repeat;
            background-size: cover;
        }

        /* Mobile fallback - disable fixed attachment on small screens */
        @media (max-width: 768px) {
            .parallax-bg {
                background-attachment: scroll;
            }
        }

        /* Image hover effects */
        .hover-zoom {
            overflow: hidden;
        }

        .hover-zoom img {
            transition: transform 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .hover-zoom:hover img {
            transform: scale(1.1);
        }

        /* Border decorations */
        .border-ornament {
            position: relative;
        }

        .border-ornament::before {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 2px;
            background: var(--color-primary);
        }

        /* Dark theme overrides for hardcoded Livewire colors */
        .bg-white {
            background-color: var(--color-light) !important;
        }

        #about .bg-white {
            background-color: #242924 !important;
        }

        #treatments .bg-white {
            background-color: #242924 !important;
        }

        .hover\:bg-white:hover {
            background-color: var(--color-light) !important;
        }

        .text-white {
            color: var(--color-dark) !important;
        }

        /* Specific overrides for buttons */
        .hover\:bg-white:hover.text-primary {
            color: var(--color-primary) !important;
        }

        .hover\:bg-white:hover.text-dark {
            color: var(--color-dark) !important;
        }

        /* Override treatment cards hover effect */
        #treatments .hover\:bg-white:hover {
            background-color: #2a302a !important;
        }

        /* Override floating card background */
        .absolute .bg-white {
            background-color: #242924 !important;
        }

        /* Comprehensive dark theme overrides */
        #about {
            background-color: var(--color-light) !important;
        }

        #treatments {
            background-color: var(--color-light) !important;
        }

        #services {
            background-color: var(--color-light) !important;
        }

        /* Override treatment cards */
        .bg-light.p-8 {
            background-color: #242924 !important;
        }

        /* Override wellness package CTA */
        .bg-gradient-to-r {
            background: linear-gradient(to right, var(--color-primary), var(--color-secondary)) !important;
        }

        .bg-gradient-to-r .text-white {
            color: var(--color-dark) !important;
        }

        .bg-gradient-to-r .text-white\/90 {
            color: rgba(243, 245, 243, 0.9) !important;
        }

        /* Testimonials component overrides */
        #testimonials {
            background: linear-gradient(to bottom, var(--color-light), #242924) !important;
        }

        #testimonials .bg-white {
            background-color: #242924 !important;
        }

        #testimonials .hover\:bg-primary:hover.text-white {
            color: var(--color-dark) !important;
        }

        /* Booking form input overrides */
        input[type="text"],
        input[type="email"], 
        input[type="tel"],
        input[type="date"],
        textarea,
        select {
            background-color: #2a302a !important;
            border-color: #3a403a !important;
            color: var(--color-dark) !important;
        }

        input:focus,
        textarea:focus,
        select:focus {
            background-color: #2a302a !important;
            border-color: var(--color-primary) !important;
            color: var(--color-dark) !important;
        }

        input::placeholder,
        textarea::placeholder {
            color: rgba(243, 245, 243, 0.5) !important;
        }

        /* Booking form card and success message */
        .bg-white.shadow-2xl {
            background-color: #242924 !important;
        }

        /* Footer component is already dark, but ensure consistency */
        #contact {
            background-color: var(--color-dark) !important;
        }

        #contact input[type="email"] {
            background-color: rgba(126, 143, 127, 0.1) !important;
            border-color: rgba(255, 255, 255, 0.2) !important;
            color: var(--color-dark) !important;
        }

        #contact input[type="email"]::placeholder {
            color: rgba(243, 245, 243, 0.5) !important;
        }

        /* Enhanced footer overrides - more specific targeting */
        #contact {
            background-color: #0f1410 !important;
        }

        #contact .text-white {
            color: var(--color-dark) !important;
        }

        #contact .text-white\/90 {
            color: rgba(243, 245, 243, 0.9) !important;
        }

        #contact .text-white\/80 {
            color: rgba(243, 245, 243, 0.8) !important;
        }

        #contact .text-white\/70 {
            color: rgba(243, 245, 243, 0.7) !important;
        }

        #contact .text-white\/60 {
            color: rgba(243, 245, 243, 0.6) !important;
        }

        #contact .border-white\/10 {
            border-color: rgba(243, 245, 243, 0.1) !important;
        }

        #contact .border-white\/20 {
            border-color: rgba(243, 245, 243, 0.2) !important;
        }

        #contact .bg-white\/10 {
            background-color: rgba(243, 245, 243, 0.1) !important;
        }

        #contact .placeholder-white\/50::placeholder {
            color: rgba(243, 245, 243, 0.5) !important;
        }

        /* Social media icons in footer */
        #contact svg {
            color: var(--color-primary) !important;
        }

        /* Back to top button */
        .fixed.bottom-8.right-8 {
            background-color: var(--color-primary) !important;
        }

        .fixed.bottom-8.right-8:hover {
            background-color: var(--color-secondary) !important;
        }

        /* Header theme toggle styling for dark theme */
        header .bg-gray-200 {
            background-color: #3a403a !important;
        }

        header .bg-white\/20 {
            background-color: rgba(243, 245, 243, 0.2) !important;
        }

        header .toggle-slider {
            background-color: var(--color-primary) !important;
        }

        /* Mobile layout fixes for dark theme */
        @media (max-width: 640px) {
            .container {
                padding-left: 1rem !important;
                padding-right: 1rem !important;
            }
            
            header .container {
                padding-left: 1rem !important;
                padding-right: 1rem !important;
            }
            
            .flex {
                max-width: 100%;
            }
        }
    </style>
 
<script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    primary: '#7e8f7f',
                    secondary: '#6f806f',
                    dark: '#f3f5f3',
                    light: '#1a1f1a',
                    accent: '#a9b5a9',
                }
            }
        }
    }
</script>
</head>
<body class="antialiased">
    @livewire('header')
    
    <main>
        @yield('content')
    </main>
    
    @livewire('footer')
    
    @livewireScripts

    <!-- Alpine.js for interactivity -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script>
        // Scroll animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -100px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.scroll-animate').forEach(el => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(30px)';
                el.style.transition = 'opacity 0.8s ease-out, transform 0.8s ease-out';
                observer.observe(el);
            });
        });
    </script>
</body>
</html>