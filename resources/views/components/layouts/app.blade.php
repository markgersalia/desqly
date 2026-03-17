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
        :root {
            --color-primary: #B8936D;
            --color-secondary: #8B7355;
            --color-dark: #2C2C2C;
            --color-light: #F8F6F3;
            --color-accent: #D4AF7A;
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
    </style>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#B8936D',
                        secondary: '#8B7355',
                        dark: '#2C2C2C',
                        light: '#F8F6F3',
                        accent: '#D4AF7A',
                    },
                    fontFamily: {
                        serif: ['Cormorant Garamond', 'serif'],
                        display: ['Lora', 'serif'],
                        sans: ['Montserrat', 'sans-serif'],
                    },
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
