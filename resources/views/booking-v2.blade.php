<!DOCTYPE html>
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
    </script>
    <style>
        .card-lift { transition: all 0.3s ease-out; }
        .card-lift:hover { box-shadow: 0 20px 40px -12px rgba(15,23,42,0.15); transform: translateY(-4px); }
        .hero-shadow { box-shadow: 0 25px 50px -12px rgba(15,23,42,0.2); }
        [x-cloak] { display: none !important; }
    </style>
    {{-- Alpine.js for dropdown interactivity --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-white text-gray-900 font-sans antialiased">

    {{-- Navbar --}}
    <nav class="fixed top-0 left-0 right-0 z-50 bg-white/80 backdrop-blur-lg border-b border-gray-200/50">
        <div class="max-w-7xl mx-auto px-4 md:px-8 flex items-center justify-between h-16">
            <a href="/" class="flex items-center gap-2 font-bold text-xl tracking-tight text-gray-900">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"/><circle cx="7" cy="17" r="2"/><path d="M9 17h6"/><circle cx="17" cy="17" r="2"/></svg>
                DriveEase
            </a>
            <div class="hidden md:flex items-center gap-8 text-sm font-medium text-gray-500">
                <a href="#cars" class="hover:text-gray-900 transition-colors">Fleet</a>
            </div>
        </div>
    </nav>

    {{-- Hero Section --}}
    <section class="relative pt-16 overflow-hidden">
        {{-- Hero background - replace src with your image --}}
        <div class="absolute inset-0">
            <img src="{{ asset('images/hero-bg.jpg') }}" alt="Premium car on open road" class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-b from-gray-900/70 via-gray-900/50 to-white"></div>
        </div>

        <div class="relative max-w-7xl mx-auto px-4 md:px-8 pt-20 md:pt-32 pb-12">
            {{-- Headline --}}
            <div class="text-center mb-10 md:mb-14 animate-fade-up">
                <h1 class="text-4xl md:text-6xl font-bold tracking-tight text-white mb-4">
                    Book Your Perfect Ride Today
                </h1>
                <p class="text-lg md:text-xl text-white/70 max-w-xl mx-auto">
                    Fast. Reliable. Hassle-free car rentals.
                </p>
            </div>

            {{-- Booking Card --}}
            <div class="animate-fade-up max-w-4xl mx-auto" style="animation-delay: 0.15s">
                <form action="#" method="GET" class="bg-white rounded-2xl p-6 md:p-8 hero-shadow">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">

                        {{-- Pickup Date & Time --}}
                        <div>
                            <label for="pickup_datetime" class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-2 block">Pickup Date & Time</label>
                            <div class="relative">
                                <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                                <input
                                    type="datetime-local"
                                    id="pickup_datetime"
                                    name="pickup_datetime"
                                    min="{{ now()->format('Y-m-d\TH:i') }}"
                                    class="w-full border border-gray-200 rounded-xl pl-12 pr-4 py-3 text-sm text-gray-900 placeholder-gray-400 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-900/10 focus:border-gray-400 transition-colors bg-white"
                                    required
                                >
                            </div>
                        </div>

                        {{-- Return Date & Time --}}
                        <div>
                            <label for="return_datetime" class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-2 block">Return Date & Time</label>
                            <div class="relative">
                                <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                                <input
                                    type="datetime-local"
                                    id="return_datetime"
                                    name="return_datetime"
                                    min="{{ now()->format('Y-m-d\TH:i') }}"
                                    class="w-full border border-gray-200 rounded-xl pl-12 pr-4 py-3 text-sm text-gray-900 placeholder-gray-400 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-900/10 focus:border-gray-400 transition-colors bg-white"
                                    required
                                >
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="w-full h-14 rounded-xl bg-gray-900 text-white text-lg font-semibold shadow-lg hover:shadow-xl hover:scale-[1.02] active:scale-[0.98] transition-all duration-200 flex items-center justify-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                        Search Available Cars
                    </button>
                </form>
            </div>
        </div>
    </section>

    {{-- Available Cars Section --}}
    <section id="cars" class="max-w-7xl mx-auto px-4 md:px-8 py-16 md:py-24">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-3">Available Cars</h2>
            <p class="text-gray-500 text-lg max-w-md mx-auto">Choose from our premium fleet of well-maintained vehicles</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @php
                $cars = [
                    ['image' => 'car-fortuner.jpg', 'name' => 'Toyota Fortuner 2024', 'transmission' => 'Automatic', 'fuel' => 'Diesel', 'seats' => 7, 'price' => 89],
                    ['image' => 'car-bmw.jpg', 'name' => 'BMW 5 Series', 'transmission' => 'Automatic', 'fuel' => 'Petrol', 'seats' => 5, 'price' => 129],
                    ['image' => 'car-mercedes.jpg', 'name' => 'Mercedes C-Class', 'transmission' => 'Automatic', 'fuel' => 'Petrol', 'seats' => 5, 'price' => 119],
                    ['image' => 'car-tesla.jpg', 'name' => 'Tesla Model 3', 'transmission' => 'Automatic', 'fuel' => 'Electric', 'seats' => 5, 'price' => 109],
                    ['image' => 'car-audi.jpg', 'name' => 'Audi Q7 Premium', 'transmission' => 'Automatic', 'fuel' => 'Diesel', 'seats' => 7, 'price' => 139],
                    ['image' => 'car-honda.jpg', 'name' => 'Honda Civic 2024', 'transmission' => 'Manual', 'fuel' => 'Petrol', 'seats' => 5, 'price' => 59],
                ];
            @endphp

            @foreach($cars as $car)
                <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden card-lift group">
                    <div class="aspect-[16/10] overflow-hidden bg-gray-100">
                        <img src="{{ asset('images/' . $car['image']) }}" alt="{{ $car['name'] }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
                    </div>
                    <div class="p-5">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">{{ $car['name'] }}</h3>
                        <div class="flex items-center gap-4 text-sm text-gray-500 mb-4">
                            <span class="flex items-center gap-1.5">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 6V2H8"/><path d="m8 18-4 4V8a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2Z"/><path d="M2 12h20"/></svg>
                                {{ $car['transmission'] }}
                            </span>
                            <span class="flex items-center gap-1.5">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" x2="15" y1="22" y2="22"/><line x1="4" x2="14" y1="9" y2="9"/><path d="M14 22V4a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v18"/><path d="M14 13h2a2 2 0 0 1 2 2v2a2 2 0 0 0 2 2a2 2 0 0 0 2-2V9.83a2 2 0 0 0-.59-1.42L18 5"/></svg>
                                {{ $car['fuel'] }}
                            </span>
                            <span class="flex items-center gap-1.5">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                {{ $car['seats'] }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="text-2xl font-bold text-gray-900">${{ $car['price'] }}</span>
                                <span class="text-sm text-gray-500"> / day</span>
                            </div>
                            <a href="#" class="inline-flex items-center justify-center h-10 px-5 rounded-xl bg-gray-900 text-white text-sm font-semibold shadow-lg hover:shadow-xl hover:scale-[1.02] active:scale-[0.98] transition-all duration-200">
                                Book Now
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Footer --}}
    <footer class="border-t border-gray-200 py-8">
        <div class="max-w-7xl mx-auto px-4 md:px-8 text-center text-sm text-gray-500">
            &copy; {{ date('Y') }} DriveEase. All rights reserved.
        </div>
    </footer>

</body>
</html>
