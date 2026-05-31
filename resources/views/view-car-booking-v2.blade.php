<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $car['name'] ?? 'Toyota Fortuner 2024' }} — DriveEase</title>
    <meta name="description" content="Book the {{ $car['name'] ?? 'Toyota Fortuner 2024' }}. Premium car rental with hassle-free booking.">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'system-ui', 'sans-serif'] },
                    borderRadius: { '2xl': '1rem', '3xl': '1.25rem' },
                    keyframes: {
                        'fade-up': {
                            '0%': { opacity: '0', transform: 'translateY(16px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        },
                        'fade-in': {
                            '0%': { opacity: '0' },
                            '100%': { opacity: '1' },
                        },
                    },
                    animation: {
                        'fade-up': 'fade-up 0.5s ease-out forwards',
                        'fade-in': 'fade-in 0.3s ease-out forwards',
                    },
                },
            },
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }
        .card-shadow { box-shadow: 0 1px 3px 0 rgba(15,23,42,0.06), 0 1px 2px -1px rgba(15,23,42,0.06); }
        .card-shadow-lg { box-shadow: 0 10px 40px -10px rgba(15,23,42,0.12); }
        .card-shadow-xl { box-shadow: 0 25px 50px -12px rgba(15,23,42,0.18); }
        input[type="datetime-local"]::-webkit-calendar-picker-indicator { cursor: pointer; opacity: 0.5; }
        input[type="datetime-local"]::-webkit-calendar-picker-indicator:hover { opacity: 1; }

        /* Custom scrollbar */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 3px; }
    </style>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-white text-gray-900 font-sans antialiased">

    {{-- ===== NAVBAR ===== --}}
    <nav class="fixed top-0 left-0 right-0 z-50 bg-white/80 backdrop-blur-lg border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 md:px-8 flex items-center justify-between h-16">
            <a href="/" class="flex items-center gap-2 font-bold text-xl tracking-tight text-gray-900">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"/><circle cx="7" cy="17" r="2"/><path d="M9 17h6"/><circle cx="17" cy="17" r="2"/></svg>
                DriveEase
            </a>
            <div class="hidden md:flex items-center gap-8 text-sm font-medium text-gray-500">
                <a href="/" class="hover:text-gray-900 transition-colors">Home</a>
                <a href="/#cars" class="hover:text-gray-900 transition-colors">Fleet</a>
                <a href="#" class="hover:text-gray-900 transition-colors">About</a>
                <a href="#" class="hover:text-gray-900 transition-colors">Contact</a>
            </div>
            {{-- Mobile back --}}
            <a href="/booking-v2" class="md:hidden flex items-center gap-1 text-sm text-gray-500 hover:text-gray-900 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
                Back
            </a>
        </div>
    </nav>

    {{-- ===== MAIN CONTENT ===== --}}
    @php
        // Sample car data — replace with your Eloquent model
        $car = $car ?? [
            'name' => 'Toyota Fortuner 2024',
            'brand' => 'Toyota',
            'model' => 'Fortuner',
            'year' => 2024,
            'color' => 'Pearl White',
            'plate_number' => 'ABC-1234',
            'seats' => 7,
            'fuel' => 'Diesel',
            'transmission' => 'Automatic',
            'coding' => '3 (Wednesday)',
            'price' => 89,
            'car_type' => 'SUV',
            'images' => [
                'images/car-fortuner.jpg',
                'images/car-fortuner-2.jpg',
                'images/car-fortuner-3.jpg',
                'images/car-fortuner-4.jpg',
            ],
        ];
    @endphp

    <main class="pt-20 pb-32 md:pb-16" x-data="bookingForm({{ json_encode($car['price'] ?? 89) }})">

        <div class="max-w-7xl mx-auto px-4 md:px-8">

            {{-- ===== 1. IMAGE GALLERY ===== --}}
            <section class="mb-8 animate-fade-up" x-data="{ mainImage: 0, lightbox: false }">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                    {{-- Main Image --}}
                    <div class="md:col-span-3 aspect-[16/10] rounded-2xl overflow-hidden bg-gray-100 cursor-pointer group relative"
                         @click="lightbox = true">
                        @foreach(($car['images'] ?? []) as $i => $img)
                            <img
                                x-show="mainImage === {{ $i }}"
                                x-transition:enter="transition ease-out duration-300"
                                x-transition:enter-start="opacity-0"
                                x-transition:enter-end="opacity-100"
                                src="{{ asset($img) }}"
                                alt="{{ $car['name'] }} view {{ $i + 1 }}"
                                class="absolute inset-0 w-full h-full object-cover group-hover:scale-[1.02] transition-transform duration-500"
                            >
                        @endforeach
                        {{-- Zoom hint --}}
                        <div class="absolute bottom-4 right-4 bg-black/50 backdrop-blur-sm text-white text-xs font-medium px-3 py-1.5 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity flex items-center gap-1.5">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/><path d="M11 8v6"/><path d="M8 11h6"/></svg>
                            Click to enlarge
                        </div>
                    </div>

                    {{-- Thumbnails --}}
                    <div class="flex md:flex-col gap-3">
                        @foreach(($car['images'] ?? []) as $i => $img)
                            <button
                                @click="mainImage = {{ $i }}"
                                class="flex-1 rounded-xl overflow-hidden transition-all duration-200"
                                :class="mainImage === {{ $i }}
                                    ? 'ring-2 ring-gray-900 ring-offset-2'
                                    : 'ring-1 ring-gray-200 hover:ring-gray-400'"
                            >
                                <img src="{{ asset($img) }}" alt="{{ $car['name'] }} thumb {{ $i + 1 }}"
                                     class="w-full h-full object-cover aspect-[16/10] md:aspect-[4/3]" loading="lazy">
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Lightbox --}}
                <template x-if="lightbox">
                    <div class="fixed inset-0 z-[100] bg-black/90 flex items-center justify-center p-4 animate-fade-in"
                         @click.self="lightbox = false" @keydown.escape.window="lightbox = false">
                        {{-- Close --}}
                        <button @click="lightbox = false" class="absolute top-6 right-6 text-white/70 hover:text-white transition-colors z-10">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                        </button>
                        {{-- Prev --}}
                        <button @click="mainImage = mainImage === 0 ? {{ count($car['images'] ?? []) - 1 }} : mainImage - 1"
                                class="absolute left-4 md:left-8 text-white/70 hover:text-white transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                        </button>
                        {{-- Image --}}
                        @foreach(($car['images'] ?? []) as $i => $img)
                            <img x-show="mainImage === {{ $i }}"
                                 x-transition
                                 src="{{ asset($img) }}"
                                 alt="{{ $car['name'] }}"
                                 class="max-w-full max-h-[85vh] rounded-2xl object-contain">
                        @endforeach
                        {{-- Next --}}
                        <button @click="mainImage = mainImage === {{ count($car['images'] ?? []) - 1 }} ? 0 : mainImage + 1"
                                class="absolute right-4 md:right-8 text-white/70 hover:text-white transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                        </button>
                        {{-- Counter --}}
                        <div class="absolute bottom-6 text-white/60 text-sm font-medium">
                            <span x-text="mainImage + 1"></span> / {{ count($car['images'] ?? []) }}
                        </div>
                    </div>
                </template>
            </section>

            {{-- ===== 2 + 3. DETAILS + BOOKING SIDEBAR ===== --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 lg:gap-12">

                {{-- LEFT: Car Details --}}
                <div class="lg:col-span-2 space-y-8 animate-fade-up" style="animation-delay: 0.1s">

                    {{-- Header --}}
                    <div>
                        <div class="flex items-center gap-3 mb-2">
                            <span class="inline-flex items-center px-3 py-1 rounded-lg bg-gray-100 text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                {{ $car['car_type'] ?? 'SUV' }}
                            </span>
                            <span class="inline-flex items-center px-3 py-1 rounded-lg bg-emerald-50 text-xs font-semibold text-emerald-700 uppercase tracking-wider">
                                Available
                            </span>
                        </div>
                        <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-1">{{ $car['name'] ?? 'Toyota Fortuner 2024' }}</h1>
                        <p class="text-gray-500 text-lg">{{ $car['brand'] ?? 'Toyota' }} · {{ $car['model'] ?? 'Fortuner' }} · {{ $car['year'] ?? 2024 }}</p>
                    </div>

                    {{-- Quick Specs Row --}}
                    <div class="flex flex-wrap gap-3">
                        @php
                            $quickSpecs = [
                                ['icon' => '<path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"/><circle cx="7" cy="17" r="2"/><path d="M9 17h6"/><circle cx="17" cy="17" r="2"/>', 'label' => $car['transmission'] ?? 'Automatic'],
                                ['icon' => '<line x1="3" x2="15" y1="22" y2="22"/><line x1="4" x2="14" y1="9" y2="9"/><path d="M14 22V4a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v18"/><path d="M14 13h2a2 2 0 0 1 2 2v2a2 2 0 0 0 2 2a2 2 0 0 0 2-2V9.83a2 2 0 0 0-.59-1.42L18 5"/>', 'label' => $car['fuel'] ?? 'Diesel'],
                                ['icon' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>', 'label' => ($car['seats'] ?? 7) . ' Seats'],
                                ['icon' => '<rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/>', 'label' => $car['year'] ?? 2024],
                            ];
                        @endphp
                        @foreach($quickSpecs as $spec)
                            <div class="flex items-center gap-2 px-4 py-2.5 bg-gray-50 rounded-xl text-sm font-medium text-gray-700">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $spec['icon'] !!}</svg>
                                {{ $spec['label'] }}
                            </div>
                        @endforeach
                    </div>

                    {{-- Detailed Specs Grid --}}
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">Vehicle Specifications</h2>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                            @php
                                $specs = [
                                    ['label' => 'Brand', 'value' => $car['brand'] ?? 'Toyota'],
                                    ['label' => 'Model', 'value' => $car['model'] ?? 'Fortuner'],
                                    ['label' => 'Year', 'value' => $car['year'] ?? 2024],
                                    ['label' => 'Color', 'value' => $car['color'] ?? 'Pearl White'],
                                    ['label' => 'Plate Number', 'value' => $car['plate_number'] ?? 'ABC-1234'],
                                    ['label' => 'Seats', 'value' => $car['seats'] ?? 7],
                                    ['label' => 'Fuel Type', 'value' => $car['fuel'] ?? 'Diesel'],
                                    ['label' => 'Transmission', 'value' => $car['transmission'] ?? 'Automatic'],
                                    ['label' => 'Coding Day', 'value' => $car['coding'] ?? '3 (Wednesday)'],
                                    ['label' => 'Car Type', 'value' => $car['car_type'] ?? 'SUV'],
                                    ['label' => 'Price Starts At', 'value' => '$' . ($car['price'] ?? 89) . '/day'],
                                ];
                            @endphp
                            @foreach($specs as $spec)
                                <div class="bg-gray-50 rounded-xl p-4">
                                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-1">{{ $spec['label'] }}</p>
                                    <p class="text-sm font-semibold text-gray-900">{{ $spec['value'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Description --}}
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900 mb-3">About This Vehicle</h2>
                        <p class="text-gray-500 leading-relaxed">
                            Experience the perfect blend of rugged capability and refined comfort with the {{ $car['name'] ?? 'Toyota Fortuner 2024' }}.
                            This premium {{ $car['car_type'] ?? 'SUV' }} features a powerful {{ $car['fuel'] ?? 'Diesel' }} engine with {{ $car['transmission'] ?? 'Automatic' }} transmission,
                            comfortably seating {{ $car['seats'] ?? 7 }} passengers. Ideal for both city driving and weekend adventures.
                        </p>
                    </div>
                </div>

                {{-- RIGHT: Booking Sidebar --}}
                <div class="lg:col-span-1 animate-fade-up" style="animation-delay: 0.2s">
                    <div class="lg:sticky lg:top-24">
                        <form action="#" method="POST"
                              class="bg-white rounded-2xl border border-gray-200 card-shadow-lg overflow-hidden">
                            @csrf

                            {{-- Price Header --}}
                            <div class="p-6 border-b border-gray-100">
                                <div class="flex items-baseline gap-1">
                                    <span class="text-3xl font-bold text-gray-900">${{ $car['price'] ?? 89 }}</span>
                                    <span class="text-gray-500 text-sm font-medium">/ day</span>
                                </div>
                            </div>

                            <div class="p-6 space-y-4">
                                {{-- Start Date & Time --}}
                                <div>
                                    <label for="start_datetime" class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-2 block">Start Date & Time</label>
                                    <div class="relative">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                                        <input type="datetime-local" id="start_datetime" name="start_datetime"
                                               x-model="startDate" @change="compute()"
                                               min="{{ now()->format('Y-m-d\TH:i') }}"
                                               class="w-full border border-gray-200 rounded-xl pl-10 pr-4 py-3 text-sm text-gray-900 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-900/10 focus:border-gray-400 transition-colors"
                                               required>
                                    </div>
                                </div>

                                {{-- End Date & Time --}}
                                <div>
                                    <label for="end_datetime" class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-2 block">End Date & Time</label>
                                    <div class="relative">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                                        <input type="datetime-local" id="end_datetime" name="end_datetime"
                                               x-model="endDate" @change="compute()"
                                               :min="startDate || '{{ now()->format('Y-m-d\TH:i') }}'"
                                               class="w-full border border-gray-200 rounded-xl pl-10 pr-4 py-3 text-sm text-gray-900 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-900/10 focus:border-gray-400 transition-colors"
                                               required>
                                    </div>
                                </div>

                                {{-- Auto-computed summary --}}
                                <template x-if="totalDays > 0">
                                    <div class="bg-gray-50 rounded-xl p-4 space-y-2 text-sm">
                                        <div class="flex justify-between text-gray-500">
                                            <span>${{ $car['price'] ?? 89 }} × <span x-text="totalDays"></span> day(s)</span>
                                            <span class="text-gray-900 font-semibold" x-text="'$' + totalPrice"></span>
                                        </div>
                                        <div class="border-t border-gray-200 pt-2 flex justify-between font-semibold text-gray-900">
                                            <span>Estimated Total</span>
                                            <span x-text="'$' + totalPrice"></span>
                                        </div>
                                    </div>
                                </template>

                                <hr class="border-gray-100">

                                {{-- Full Name --}}
                                <div>
                                    <label for="full_name" class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-2 block">Full Name</label>
                                    <input type="text" id="full_name" name="full_name" placeholder="Juan Dela Cruz"
                                           class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm text-gray-900 placeholder-gray-400 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-900/10 focus:border-gray-400 transition-colors"
                                           required>
                                </div>

                                {{-- Email --}}
                                <div>
                                    <label for="email" class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-2 block">Email</label>
                                    <input type="email" id="email" name="email" placeholder="you@example.com"
                                           class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm text-gray-900 placeholder-gray-400 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-900/10 focus:border-gray-400 transition-colors"
                                           required>
                                </div>

                                {{-- Phone --}}
                                <div>
                                    <label for="phone" class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-2 block">Phone Number</label>
                                    <input type="tel" id="phone" name="phone" placeholder="+63 9XX XXX XXXX"
                                           class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm text-gray-900 placeholder-gray-400 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-900/10 focus:border-gray-400 transition-colors"
                                           required>
                                </div>

                                {{-- Notes --}}
                                <div>
                                    <label for="notes" class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-2 block">Notes <span class="text-gray-300 normal-case">(optional)</span></label>
                                    <textarea id="notes" name="notes" rows="3" placeholder="Any special requests..."
                                              class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm text-gray-900 placeholder-gray-400 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-900/10 focus:border-gray-400 transition-colors resize-none"></textarea>
                                </div>
                            </div>

                            {{-- Submit --}}
                            <div class="p-6 pt-0">
                                <button type="submit"
                                        class="w-full h-14 rounded-xl bg-gray-900 text-white text-base font-semibold shadow-lg hover:shadow-xl hover:scale-[1.01] active:scale-[0.99] transition-all duration-200 flex items-center justify-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/><path d="m9 16 2 2 4-4"/></svg>
                                    Confirm Booking
                                </button>
                                <p class="text-center text-xs text-gray-400 mt-3">No payment required now · Free cancellation</p>
                            </div>

                            {{-- Hidden car info --}}
                            <input type="hidden" name="car_id" value="{{ $car['id'] ?? 1 }}">
                            <input type="hidden" name="total_days" :value="totalDays">
                            <input type="hidden" name="total_price" :value="totalPrice">
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    {{-- ===== MOBILE STICKY BOTTOM BAR ===== --}}
    <div class="fixed bottom-0 left-0 right-0 bg-white/95 backdrop-blur-lg border-t border-gray-200 p-4 lg:hidden z-40">
        <div class="flex items-center justify-between max-w-7xl mx-auto">
            <div>
                <span class="text-2xl font-bold text-gray-900">${{ $car['price'] ?? 89 }}</span>
                <span class="text-sm text-gray-500"> / day</span>
            </div>
            <a href="#start_datetime"
               class="h-12 px-8 rounded-xl bg-gray-900 text-white text-sm font-semibold shadow-lg hover:shadow-xl active:scale-[0.98] transition-all duration-200 flex items-center justify-center gap-2">
                Book Now
            </a>
        </div>
    </div>

    {{-- ===== FOOTER ===== --}}
    <footer class="border-t border-gray-100 py-8 hidden lg:block">
        <div class="max-w-7xl mx-auto px-4 md:px-8 text-center text-sm text-gray-400">
            &copy; {{ date('Y') }} DriveEase. All rights reserved.
        </div>
    </footer>

    {{-- ===== ALPINE BOOKING LOGIC ===== --}}
    <script>
        function bookingForm(pricePerDay) {
            return {
                startDate: '',
                endDate: '',
                totalDays: 0,
                totalPrice: 0,
                pricePerDay: pricePerDay,
                compute() {
                    if (this.startDate && this.endDate) {
                        const start = new Date(this.startDate);
                        const end = new Date(this.endDate);
                        const diffMs = end - start;
                        const diffDays = Math.ceil(diffMs / (1000 * 60 * 60 * 24));
                        this.totalDays = diffDays > 0 ? diffDays : 0;
                        this.totalPrice = this.totalDays * this.pricePerDay;
                    } else {
                        this.totalDays = 0;
                        this.totalPrice = 0;
                    }
                }
            }
        }
    </script>

</body>
</html>
