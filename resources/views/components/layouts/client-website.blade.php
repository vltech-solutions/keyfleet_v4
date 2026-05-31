{{-- <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DriveEase — Book Your Perfect Ride</title>
    <meta name="description" content="Fast, reliable, hassle-free car rentals. Book your perfect ride today with DriveEase.">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'system-ui', 'sans-serif'] },
                    colors: {
                        brand: {
                            DEFAULT: 'hsl(220 60% 12%)',
                            light: 'hsl(220 50% 25%)',
                            foreground: 'hsl(0 0% 100%)',
                        },
                        surface: 'hsl(220 20% 98%)',
                    },
                    borderRadius: { '2xl': '1rem', '3xl': '1.25rem' },
                    keyframes: {
                        'fade-up': {
                            '0%': { opacity: '0', transform: 'translateY(20px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        },
                    },
                    animation: { 'fade-up': 'fade-up 0.6s ease-out forwards' },
                },
            },
        }
    </script> --}}
    <!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
{{-- <html lang="{{ str_replace('_', '-', app()->getLocale()) }}"  class="dark"> --}}
    {{-- <html lang="{{ str_replace('_', '-', app()->getLocale()) }}" 
      class="{{ $company?->booking_form_dark_mode ? 'dark' : '' }}"> --}}

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <title>
        @yield('title', 'Smarter Car Rentals')
    </title>
    <meta name="description" content="@yield('description', 'Run your rental business smarter with Keyfleet.')">
    

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @filamentStyles

    <style>
        .keyfleet-gradient {
            background-image: linear-gradient(to right, #0047AB, #0a66c2);
        }
        .card-lift { transition: all 0.3s ease-out; }
        .card-lift:hover { box-shadow: 0 20px 40px -12px rgba(15,23,42,0.15); transform: translateY(-4px); }
        .hero-shadow { box-shadow: 0 25px 50px -12px rgba(15,23,42,0.2); }
        [x-cloak] { display: none !important; }
        /* Hide scrollbar for Chrome, Safari and Opera */
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        /* Hide scrollbar for IE, Edge and Firefox */
        .no-scrollbar {
            -ms-overflow-style: none;  /* IE and Edge */
            scrollbar-width: none;  /* Firefox */
        }
        .datepicker {
            z-index: 100 !important;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.js"></script>
</head>
<body class="flex flex-col min-h-screen text-gray-900 bg-white-100 dark:bg-gray-900 dark:text-gray-100">

    {{-- Main Content --}}
    <main class="flex-grow">
        {{ $slot }}
    </main>


    {{-- Livewire Notifications --}}
    @livewire('notifications')

    @livewireScripts
    @filamentScripts

</body>
</html>
