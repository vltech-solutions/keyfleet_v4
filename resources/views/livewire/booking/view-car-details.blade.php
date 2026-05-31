{{-- <!DOCTYPE html>
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
 --}}
<div x-data="{ 
    bookingOpen: false, 
}">

<style>
    :root {--tw-primary: {{ $primaryColor }};}
</style>

@section('title')
    {{ $company->name }} — Book Your Perfect Ride
@endsection

@section('description')
    Fast, reliable, hassle-free car rentals. Book your perfect ride today with {{ $company->name }}.
@endsection
    
    <main class="pt-20 pb-32 md:pb-16" x-data="bookingForm({{ json_encode($car['price'] ?? 89) }})">
        <div class="max-w-7xl mx-auto px-4 md:px-8">
            <div class="mb-6">
                <a href="{{ route('client.page', ['tenant' => $this->company->slug]) }}" 
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white border border-gray-200 text-sm font-semibold text-gray-600 shadow-sm hover:bg-gray-50 hover:text-gray-900 transition-all dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:text-white group">
                    <svg xmlns="http://www.w3.org/2000/svg" 
                        class="h-4 w-4 transition-transform group-hover:-translate-x-1" 
                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                    </svg>
                    Return to Listing
                </a>
            </div>

            <x-booking.gallery :images="$this->images" :carName="$car->name" />

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 lg:gap-12">

                {{-- LEFT: Car Details --}}
                <div class="lg:col-span-2 space-y-8 animate-fade-up" style="animation-delay: 0.1s">

                    {{-- Header --}}
                    <div>
                        <div class="flex items-center gap-3 mb-2">
                            <span class="inline-flex items-center px-3 py-1 rounded-lg bg-[var(--tw-primary)] text-white text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                {{ $car->carType?->car_type ?? 'SUV' }}
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
                                ['icon' => '<line x1="3" x2="15" y1="22" y2="22"/><line x1="4" x2="14" y1="9" y2="9"/><path d="M14 22V4a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v18"/><path d="M14 13h2a2 2 0 0 1 2 2v2a2 2 0 0 0 2 2a2 2 0 0 0 2-2V9.83a2 2 0 0 0-.59-1.42L18 5"/>', 'label' => $car['fuel_type'] ?? 'Diesel'],
                                ['icon' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>', 'label' => ($car['seat_count'] ?? 7) . ' Seats'],
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
                                    ['label' => 'Seats', 'value' => $car['seat_count'] ?? 7],
                                    ['label' => 'Fuel Type', 'value' => $car['fuel_type'] ?? 'Diesel'],
                                    ['label' => 'Transmission', 'value' => $car['transmission'] ?? 'Automatic'],
                                    ['label' => 'Coding Day', 'value' => $car['coding'] ?? '3 (Wednesday)'],
                                    ['label' => 'Car Type', 'value' => $car->carType?->car_type ?? 'SUV'],
                                    ['label' => 'Price', 'value' => '₱' . ($car['price_starts_at'] ?? 89) . '/day'],
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
                        <div class="prose prose-blue max-w-none dark:prose-invert">
                            @if($car->description)
                                <p class="text-gray-500 leading-relaxed">
                                    {!! nl2br(e($car->description)) !!}
                                </p>
                            @else
                                <p class="text-gray-500 leading-relaxed">
                                    Experience the perfect blend of rugged capability and refined comfort with {{ $car['name'] ?? 'Toyota Fortuner 2024' }}. 
                                    This premium {{ $car->carType?->car_type ?? 'SUV' }} features a powerful {{ $car['fuel_type'] ?? 'Diesel' }} engine with {{ $car['transmission'] ?? 'Automatic' }} transmission, 
                                    comfortably seating {{ $car['seat_count'] ?? 7 }} passengers. Ideal for both city driving and weekend adventures.
                                </p>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- RIGHT: Booking Sidebar --}}
                <div class="hidden lg:block lg:col-span-1">
                    <div class="lg:sticky lg:top-24 bg-white p-6 rounded-2xl border border-gray-100 shadow-sm space-y-4 dark:bg-gray-800 dark:border-gray-700">
                        <div class="flex items-baseline gap-1">
                            <span class="text-3xl font-bold text-[var(--tw-primary)] dark:text-white">₱{{ $car['price_starts_at'] ?? 89 }}</span>
                            <span class="text-gray-500 text-sm font-medium dark:text-gray-400">/ day</span>
                        </div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Perfect for your next trip in the city.</p>
                        
                        <button @click="bookingOpen = true" 
                            class="w-full h-14 rounded-xl bg-gray-900 dark:bg-blue-600 text-white text-base font-semibold shadow-lg hover:bg-gray-800 dark:hover:bg-blue-700 transition-all flex items-center justify-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Book This Vehicle
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <x-booking.sliding-drawer :car="$car" :busyDates="$busyDates" :company="$company" />

    {{-- ===== MOBILE STICKY BOTTOM BAR ===== --}}
    <div class="fixed bottom-0 left-0 right-0 bg-white/95 backdrop-blur-lg border-t border-gray-200 p-4 lg:hidden z-40">
        <div class="flex items-center justify-between max-w-7xl mx-auto">
            <div>
                <span class="text-2xl font-bold text-[var(--tw-primary)]">₱{{ $car['price_starts_at'] ?? 89 }}</span>
                <span class="text-sm text-gray-500"> / day</span>
            </div>
            <button @click="bookingOpen = true" 
                class="h-12 px-8 rounded-xl bg-gray-900 text-white text-sm font-semibold shadow-lg hover:shadow-xl active:scale-[0.98] transition-all duration-200 flex items-center justify-center gap-2">
                Book Now
            </button>

        </div>
    </div>
    <div id="qr-temp-reader" style="display: none"></div>
    <script src="https://unpkg.com/html5-qrcode"></script>
    <script>
        function readQRfromFile(event) {
            const file = event.target.files[0];
            if (!file) return;

            const uploadBtn = document.getElementById('qr-upload-btn');
            uploadBtn.disabled = true;
            uploadBtn.textContent = 'Please wait...';

            const qrReader = new Html5Qrcode("qr-temp-reader");

            qrReader.scanFile(file, true)
                .then(decodedText => {
                    const component = Livewire.find(document.querySelector('[wire\\:id]').getAttribute('wire:id'));
                    component.set('repeat_token', decodedText);

                    const submitBtn = document.getElementById('submitTokenBtn');
                    if (submitBtn) submitBtn.click();
                })
                .catch(err => {
                    const component = Livewire.find(document.querySelector('[wire\\:id]').getAttribute('wire:id'));
                    component.call('qrDownloadFailed');
                })
                .finally(() => {
                    uploadBtn.disabled = false;
                    uploadBtn.textContent = 'Upload QR Image';
                });
        }
    </script>
    
</div>
