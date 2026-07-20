<div x-data="{ 
    bookingOpen: false,
    currentImage: 0,
    totalImages: {{ count($this->images) }},
}">

<style>
    :root {
        --tw-primary: {{ $primaryColor }};
    }
    
    /* Smooth scroll behavior */
    html {
        scroll-behavior: smooth;
    }
</style>

@section('title')
    {{ $company->name }} — {{ $car->name }} 
@endsection

@section('description')
    Book the {{ $car->name }}. Premium car rental with hassle-free booking from {{ $company->name }}.
@endsection

<main class="min-h-screen bg-gray-50 dark:bg-gray-900 pt-6 pb-32 md:pb-16 transition-colors duration-300">
    <div class="max-w-7xl mx-auto px-4 md:px-8">
        
        <!-- Back Button -->
        <div class="mb-6">
            <a href="{{ route('client.page', ['tenant' => $this->company->slug]) }}" 
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-sm font-semibold text-gray-600 dark:text-gray-400 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white transition-all group">
                <svg xmlns="http://www.w3.org/2000/svg" 
                    class="h-4 w-4 transition-transform group-hover:-translate-x-1" 
                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                </svg>
                Back to Fleet
            </a>
        </div>

        <!-- Gallery -->
        <x-booking.gallery :images="$this->images" :carName="$car->name" :key="'gallery-'.$car->id" />

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 lg:gap-12 mt-8">
            
            <!-- LEFT: Car Details -->
            <div class="lg:col-span-2 space-y-8">
                
                <!-- Header -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-gray-700 transition-colors duration-300">
                    <div class="flex flex-wrap items-center gap-3 mb-3">
                        <span class="inline-flex items-center px-3 py-1 rounded-lg text-white text-xs font-bold uppercase tracking-wider" style="background: var(--tw-primary);">
                            {{ $car->carType?->car_type ?? 'SUV' }}
                        </span>
                        <span class="inline-flex items-center px-3 py-1 rounded-lg bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 text-xs font-bold uppercase tracking-wider">
                            ● Available
                        </span>
                    </div>
                    <h1 class="text-3xl md:text-4xl font-black text-gray-900 dark:text-white">{{ $car->name }}</h1>
                    <p class="text-gray-500 dark:text-gray-400 text-lg mt-1">{{ $car->brand }} · {{ $car->model }} · {{ $car->year }}</p>
                </div>

                <!-- Quick Specs Row -->
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
                        <div class="flex items-center gap-2 px-4 py-2.5 bg-white dark:bg-gray-800 rounded-xl text-sm font-medium text-gray-700 dark:text-gray-300 shadow-sm border border-gray-100 dark:border-gray-700 transition-colors duration-300">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" style="color: var(--tw-primary);" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $spec['icon'] !!}</svg>
                            {{ $spec['label'] }}
                        </div>
                    @endforeach
                </div>

                <!-- Detailed Specs -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-gray-700 transition-colors duration-300">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5" style="color: var(--tw-primary);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        Vehicle Specifications
                    </h2>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                        @php
                            $specs = [
                                ['label' => 'Brand', 'value' => $car->brand ?? 'Toyota'],
                                ['label' => 'Model', 'value' => $car->model ?? 'Fortuner'],
                                ['label' => 'Year', 'value' => $car->year ?? 2024],
                                ['label' => 'Color', 'value' => $car->color ?? 'Pearl White'],
                                ['label' => 'Plate Number', 'value' => $car->plate_number ?? 'ABC-1234'],
                                ['label' => 'Seats', 'value' => $car->seat_count ?? 7],
                                ['label' => 'Fuel Type', 'value' => $car->fuel_type ?? 'Diesel'],
                                ['label' => 'Transmission', 'value' => $car->transmission ?? 'Automatic'],
                                ['label' => 'Coding Day', 'value' => $car->coding ?? '3 (Wednesday)'],
                                ['label' => 'Car Type', 'value' => $car->carType?->car_type ?? 'SUV'],
                            ];
                        @endphp
                        @foreach($specs as $spec)
                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3 transition-colors duration-300">
                                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-0.5">{{ $spec['label'] }}</p>
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $spec['value'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Description -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-gray-700 transition-colors duration-300">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                        <svg class="w-5 h-5" style="color: var(--tw-primary);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        About This Vehicle
                    </h2>
                    <div class="prose prose-sm max-w-none dark:prose-invert">
                        @if($car->description)
                            <p class="text-gray-600 dark:text-gray-300 leading-relaxed">
                                {!! nl2br(e($car->description)) !!}
                            </p>
                        @else
                            <p class="text-gray-600 dark:text-gray-300 leading-relaxed">
                                Experience the perfect blend of rugged capability and refined comfort with {{ $car->name }}. 
                                This premium {{ $car->carType?->car_type ?? 'SUV' }} features a powerful {{ $car->fuel_type ?? 'Diesel' }} engine with {{ $car->transmission ?? 'Automatic' }} transmission, 
                                comfortably seating {{ $car->seat_count ?? 7 }} passengers. Ideal for both city driving and weekend adventures.
                            </p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- RIGHT: Booking Sidebar -->
            <div class="lg:col-span-1">
                <div class="lg:sticky lg:top-24">
                    <!-- Price Card -->
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm space-y-5 transition-colors duration-300">
                        <div>
                            <div class="flex items-baseline gap-1">
                                <span class="text-4xl font-black text-gray-900 dark:text-white">₱{{ number_format($car->price_starts_at ?? 89, 2) }}</span>
                                <span class="text-gray-500 dark:text-gray-400 text-sm font-medium">/ day</span>
                            </div>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Premium rate • No hidden fees</p>
                        </div>
                        
                        <!-- Features Quick List -->
                        <div class="space-y-2 pt-3 border-t border-gray-100 dark:border-gray-700">
                            <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                                <svg class="w-4 h-4 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span>Quick & easy booking process</span>
                            </div>
                            <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                                <svg class="w-4 h-4 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span>Insurance included</span>
                            </div>
                            <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                                <svg class="w-4 h-4 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span>24/7 roadside assistance</span>
                            </div>
                        </div>
                        
                        <!-- Book Button -->
                        <button @click="bookingOpen = true" 
                            class="w-full h-14 rounded-xl text-white text-base font-bold shadow-lg hover:shadow-xl transition-all duration-300 flex items-center justify-center gap-2 active:scale-[0.98]"
                            style="background: var(--tw-primary); box-shadow: 0 8px 25px rgba(var(--primary-rgb), 0.35);"
                            onmouseover="this.style.boxShadow='0 12px 35px rgba(var(--primary-rgb), 0.5)'"
                            onmouseout="this.style.boxShadow='0 8px 25px rgba(var(--primary-rgb), 0.35)'">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Book This Vehicle
                        </button>
                        
                        <div class="flex items-center justify-center gap-1.5 text-xs text-gray-400 dark:text-gray-500">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            <span>Your booking is secure and encrypted</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Sliding Drawer -->

<x-booking.sliding-drawer 
    :car="$car" 
    :busyDates="$busyDates" 
    :company="$company"
    :selectedFundType="$selectedFundType"
    :reservationFeeAmount="$this->getReservationFeeAmount()"
/>

<!-- Mobile Sticky Bottom Bar -->
<div class="fixed bottom-0 left-0 right-0 bg-white/95 dark:bg-gray-900/95 backdrop-blur-lg border-t border-gray-200 dark:border-gray-700 p-4 lg:hidden z-40 transition-colors duration-300">
    <div class="flex items-center justify-between max-w-7xl mx-auto">
        <div>
            <span class="text-2xl font-black text-gray-900 dark:text-white" style="color: var(--tw-primary);">₱{{ number_format($car->price_starts_at ?? 89, 2) }}</span>
            <span class="text-sm text-gray-500 dark:text-gray-400"> / day</span>
        </div>
        <button @click="bookingOpen = true" 
            class="h-12 px-8 rounded-xl text-white text-sm font-bold shadow-lg hover:shadow-xl transition-all duration-200 flex items-center justify-center gap-2 active:scale-[0.98]"
            style="background: var(--tw-primary); box-shadow: 0 4px 15px rgba(var(--primary-rgb), 0.35);"
            onmouseover="this.style.boxShadow='0 8px 25px rgba(var(--primary-rgb), 0.5)'"
            onmouseout="this.style.boxShadow='0 4px 15px rgba(var(--primary-rgb), 0.35)'">
            Book Now
        </button>
    </div>
</div>

<!-- QR Code Modal -->
@if($showQRModal)
    <div class="fixed inset-0 z-[300] flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm"
         x-data="{ show: true }"
         x-init="setTimeout(() => { show = true }, 100)"
         x-show="show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         @click.away="show = false; $wire.showQRModal = false">
        
        <div class="bg-white dark:bg-gray-900 rounded-3xl max-w-md w-full shadow-2xl overflow-hidden transition-colors duration-300">
            <!-- Header -->
            <div class="px-6 pt-6 pb-4 border-b border-gray-100 dark:border-gray-800">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Booking Confirmed!</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Reservation #{{ $qrReservationNumber }}
                            </p>
                        </div>
                    </div>
                    <button @click="show = false; $wire.showQRModal = false; window.location.reload();" 
                            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
            
            <!-- Body -->
            <div class="p-6 text-center">
                <!-- Success Message -->
                <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/20 rounded-xl border border-green-100 dark:border-green-800">
                    <p class="text-sm text-green-700 dark:text-green-300 flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Your booking has been successfully submitted!
                    </p>
                </div>

                @if($qrIsNewCustomer)
                    <div class="mb-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-100 dark:border-blue-800">
                        <p class="text-sm text-blue-700 dark:text-blue-300 flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Welcome, {{ $qrCustomerName }}! You're now registered.
                        </p>
                    </div>
                @endif
                
                <p class="text-sm text-gray-600 dark:text-gray-300 mb-4">
                    <span class="font-semibold">Save your QR code</span> to view and manage your bookings anytime.
                    You can use this code to check your reservation status.
                </p>

                <div class="p-3 bg-amber-50 dark:bg-amber-900/20 rounded-xl border border-amber-200 dark:border-amber-800 mb-4">
                    <p class="text-xs text-amber-700 dark:text-amber-300 flex items-center justify-center gap-2">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Keep this QR code to access your bookings later</span>
                    </p>
                </div>
                
                <!-- QR Code -->
                @if($qrCodeData)
                    <div class="inline-block p-4 bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700">
                        <img src="{{ $qrCodeData }}" 
                             alt="QR Code for {{ $qrCustomerName }}" 
                             class="w-48 h-48 object-contain"
                             id="qr-code-image"
                             crossorigin="anonymous">
                    </div>
                @else
                    <div class="inline-block p-4 bg-gray-100 dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700">
                        <p class="text-sm text-gray-500 dark:text-gray-400">QR Code not available</p>
                    </div>
                @endif
                
                <div class="mt-4 p-3 bg-gray-50 dark:bg-gray-800 rounded-xl">
                    <p class="text-xs text-gray-500 dark:text-gray-400 font-mono break-all">
                        Token: {{ $qrRepeatToken }}
                    </p>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-100 dark:border-gray-800 flex flex-col sm:flex-row gap-3">
                <button onclick="downloadQR()" 
                        class="flex-1 px-4 py-2.5 bg-[var(--tw-primary)] text-white rounded-xl hover:opacity-90 transition font-medium flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Download QR
                </button>
            </div>
        </div>
    </div>
@endif

<script>
    function downloadQR() {
        const img = document.getElementById('qr-code-image');
        if (!img) {
            alert('QR Code image not found');
            return;
        }
        
        // Create a canvas to handle cross-origin issues
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        canvas.width = img.naturalWidth || 500;
        canvas.height = img.naturalHeight || 500;
        
        ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
        
        const link = document.createElement('a');
        link.download = 'qr-{{ $qrReservationNumber }}.png';
        link.href = canvas.toDataURL('image/png');
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        setTimeout(function() {
            const tenantSlug = '{{ session('tenant_slug') }}';
            window.location.href = '/' + tenantSlug;
        }, 500);
    }
</script>

<!-- QR Reader (hidden) -->
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