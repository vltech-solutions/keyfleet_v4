@php
    // HEX to RGB Conversion para sa Tailwind Opacity Support
    $primaryHex = $company->primary_color ?? '#2563eb';
    $hex = str_replace('#', '', $primaryHex);
    if(strlen($hex) == 3) {
        $r = hexdec(str_repeat(substr($hex, 0, 1), 2));
        $g = hexdec(str_repeat(substr($hex, 1, 1), 2));
        $b = hexdec(str_repeat(substr($hex, 2, 1), 2));
    } else {
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
    }
    $rgb = "$r, $g, $b";
@endphp

<style>
    :root {
        --primary-color: {{ $primaryHex }};
        --primary-rgb: {{ $rgb }};
    }
    [x-cloak] { display: none !important; }

    /* Custom overrides para sa mga libraries na hindi Tailwind-driven */
    .filepond--label-action { color: var(--primary-color) !important; }
    .datepicker-cell.selected { background-color: var(--primary-color) !important; }
</style>

@props([
    'car' => null,
    'busyDates' => [],
    'company' => null,
    'selectedFundType' => null,
    'reservationFeeAmount' => 0,
])

<div x-show="bookingOpen" 
    class="fixed inset-0 z-[150]" 
    x-cloak
    style="display: none;">

    <div 
        x-data="{ 
            show: false, 
            message: '', 
            type: 'success',
            get colors() {
                return {
                    'success': 'bg-emerald-600 shadow-emerald-500/20',
                    'error': 'bg-red-600 shadow-red-500/20',
                    'warning': 'bg-amber-500 shadow-amber-500/20'
                }[this.type]
            }
        }" 
        x-on:notify.window="
            show = true; 
            message = $event.detail.message; 
            type = $event.detail.type || 'success';
            setTimeout(() => show = false, 5000)
        "
        x-show="show"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 -translate-x-10"
        x-transition:enter-end="opacity-100 translate-x-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-x-0"
        x-transition:leave-end="opacity-0 -translate-x-10"
        x-cloak
        :class="colors"
        class="fixed bottom-10 left-10 z-[100] text-white p-4 rounded-2xl shadow-2xl flex items-center gap-3 min-w-[300px]"
    >
        <div class="flex-shrink-0">
            <template x-if="type === 'success'">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            </template>
            <template x-if="type === 'error'">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </template>
        </div>

        <div>
            <p x-text="message" class="font-bold text-sm"></p>
        </div>

        <button @click="show = false" class="ml-auto hover:opacity-70">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
    </div>

    {{-- Backdrop --}}
    <div x-show="bookingOpen" 
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="bookingOpen = false"
        class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>

    {{-- Form Container --}}
    <div x-show="bookingOpen" 
        x-transition:enter="transition ease-out duration-300 transform"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-200 transform"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="absolute right-0 top-0 h-full w-full max-w-md bg-white dark:bg-gray-900 shadow-2xl overflow-y-auto">
        
        {{-- Header --}}
        <div class="p-6 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between sticky top-0 bg-white dark:bg-gray-900 z-10">
            <div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Complete Booking</h3>
                <p class="text-sm text-gray-500">{{ $car['name'] ?? 'Car Rental' }}</p>
            </div>
            <button @click="bookingOpen = false" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-full transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        {{-- Multi-step Logic --}}
        <div x-data="{ step: 1 }" class="flex flex-col h-full">
            
            <div class="px-6 pt-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-bold dark:text-white transition-all duration-300" 
                        x-text="step === 1 ? 'Trip Details' : (step === 2 ? 'Renter Information' : (step === 3 ? 'Requirements' : 'Reservation Fee'))">
                    </h3>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-lg tracking-wider"
                        style="background-color: rgba(var(--primary-rgb), 0.1); color: var(--primary-color);">
                        STEP <span x-text="step"></span> OF 4
                    </span>
                </div>

                <div class="w-full bg-gray-100 rounded-full h-1.5 mb-6 dark:bg-gray-800 overflow-hidden">
                    <div class="h-1.5 rounded-full transition-all duration-500 ease-out" 
                        :style="`width: ${(step / 4) * 100}%; background-color: var(--primary-color); box-shadow: 0 0 8px rgba(var(--primary-rgb), 0.4);` ">
                    </div>
                </div>
            </div>

            <form wire:submit.prevent="saveBooking" class="flex flex-col flex-1">
                

                <div class="flex-1 px-6 space-y-6">
                    {{-- STEP 1: TRIP DETAILS --}}
                    <div x-show="step === 1" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-x-4">
                        <x-booking.trip-details :company="$company" :busyDates="$busyDates" />
                    </div>

                    {{-- STEP 2: RENTER INFO --}}
                    <div x-show="step === 2" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-x-4">
                        <x-booking.renter-info :company="$company" />

                        
                    </div>

                    <div x-show="step === 3" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-x-4">
                        <x-booking.requirements :enabledRequirements="$company->enabled_requirements ?? []" />
                    </div>

                    {{-- STEP 4: RESERVATION FEE --}}
                    <div x-show="step === 4" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-x-4">
                        <div class="space-y-4">
                            <div class="p-4 rounded-xl" style="background-color: rgba(var(--primary-rgb), 0.05); border: 1px solid rgba(var(--primary-rgb), 0.1);">
                                <div class="flex items-center gap-3 mb-2">
                                    <svg class="w-5 h-5" style="color: var(--primary-color);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v1"/>
                                    </svg>
                                    <h4 class="font-semibold text-gray-900 dark:text-white">Reservation Fee</h4>
                                </div>
                                
                                @if($this->company->is_reservation_fee_enabled && $this->getReservationFeeAmount() > 0)
                                    <p class="text-sm text-gray-600 dark:text-gray-300 mb-2">
                                        A reservation fee of <span class="font-bold" style="color: var(--primary-color);">₱{{ number_format($this->getReservationFeeAmount(), 2) }}</span> 
                                        is required to secure this booking. This will be deducted from your total bill.
                                    </p>
                                    
                                    <div class="mt-4 space-y-3">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Select Payment Method
                                        </label>
                                        
                                        @php
                                            // Use the company from props
                                            $funds = \App\Models\FundType::where('company_id', $company->id)
                                                ->whereNotNull('account_number')
                                                ->whereNotNull('account_name')
                                                ->get();
                                        @endphp
                                        
                                        @if($funds->count() > 0)
                                            <div class="space-y-3">
                                                <!-- Dropdown Selection -->
                                                <div class="relative">
                                                    <select wire:model.live="selectedFundType" 
                                                        class="w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-xl px-4 py-3.5 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-[var(--tw-primary)] focus:border-transparent appearance-none transition-all">
                                                        <option value="">Select a payment method...</option>
                                                        @foreach($funds as $fund)
                                                            <option value="{{ $fund->id }}">{{ $fund->name }}</option>
                                                        @endforeach
                                                    </select>
                                                    <svg class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                    </svg>
                                                </div>
                                                
                                                @error('selectedFundType')
                                                    <p class="text-sm text-red-500">{{ $message }}</p>
                                                @enderror

                                                <!-- Selected Fund Details -->
                                                @if($selectedFundType)
                                                    @php
                                                        $selectedFund = $funds->firstWhere('id', $selectedFundType);
                                                    @endphp
                                                    
                                                    @if($selectedFund)
                                                        <div class="mt-4 p-4 bg-white dark:bg-gray-800 rounded-xl border-2 border-[var(--tw-primary)] shadow-lg animate-fade-in">
                                                            <div class="flex items-center gap-2 mb-3">
                                                                <svg class="w-5 h-5 text-[var(--tw-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                                                </svg>
                                                                <h5 class="font-bold text-gray-900 dark:text-white">{{ $selectedFund->name }}</h5>
                                                            </div>
                                                            
                                                            <div class="grid grid-cols-2 gap-3 text-sm">
                                                                <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                                                    <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">Account Name</p>
                                                                    <p class="font-semibold text-gray-900 dark:text-white">{{ $selectedFund->account_name }}</p>
                                                                </div>
                                                                <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                                                    <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">Account Number</p>
                                                                    <p class="font-semibold text-gray-900 dark:text-white">{{ $selectedFund->account_number }}</p>
                                                                </div>
                                                            </div>
                                                            
                                                            @if($selectedFund->qr_code)
                                                                <div class="mt-4 text-center">
                                                                    <p class="text-xs text-gray-500 dark:text-gray-400 font-medium mb-2">Scan to Pay</p>
                                                                    <div class="inline-block p-2 bg-white rounded-lg border border-gray-200 dark:border-gray-600">
                                                                        <img src="{{ \Storage::url($selectedFund->qr_code) }}" 
                                                                            alt="QR Code for {{ $selectedFund->name }}" 
                                                                            class="w-48 h-48 object-contain">
                                                                    </div>
                                                                    <p class="text-xs text-gray-400 mt-2">Scan this QR code with your banking app to pay</p>
                                                                </div>
                                                            @endif

                                                            <!-- Amount to pay -->
                                                            <div class="mt-4 p-3 bg-[var(--tw-primary)]/10 rounded-lg border border-[var(--tw-primary)]/20">
                                                                <div class="flex items-center justify-between">
                                                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Amount to Pay:</span>
                                                                    <span class="text-xl font-bold text-[var(--tw-primary)]">₱{{ number_format($reservationFeeAmount, 2) }}</span>
                                                                </div>
                                                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">This is the reservation fee for your booking</p>
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endif
                                            </div>
                                        @else
                                            <div class="p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-xl border border-yellow-200 dark:border-yellow-800">
                                                <p class="text-sm text-yellow-800 dark:text-yellow-200">
                                                    No payment methods available. Please contact the company for payment instructions.
                                                </p>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Upload Receipt -->
                                    <div class="mt-4">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Upload Payment Receipt
                                            <span class="text-red-500">*</span>
                                        </label>
                                        <div class="relative">
                                            <input type="file" 
                                                wire:model="reservation_fee_receipt" 
                                                accept="image/*"
                                                class="block w-full text-sm text-gray-500 file:mr-4 file:py-3 file:px-6 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-[var(--tw-primary)] file:text-white hover:file:opacity-90 transition-all cursor-pointer">
                                            <div wire:loading wire:target="reservation_fee_receipt" class="mt-2 text-sm text-gray-500">
                                                Uploading...
                                            </div>
                                        </div>
                                        @error('reservation_fee_receipt')
                                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                        @enderror
                                        
                                        @if($this->reservation_fee_receipt)
                                            <div class="mt-2 p-2 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-800">
                                                <p class="text-sm text-green-700 dark:text-green-300 flex items-center gap-2">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                    Receipt uploaded: {{ $this->reservation_fee_receipt->getClientOriginalName() }}
                                                </p>
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        No reservation fee required for this booking.
                                    </p>
                                @endif
                            </div>

                            <!-- Privacy Policy moved here -->
                            <div x-data="{ showPolicy: false }" class="mt-4 ml-3">
                                <label class="flex items-start space-x-2">
                                    <input
                                        type="checkbox"
                                        wire:model.live="agreeToPrivacy"
                                        class="mt-1 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                    />
                                    <span class="text-sm text-gray-700 dark:text-gray-300">
                                        I agree to the
                                        <button type="button" @click="showPolicy = true" class="text-blue-600 hover:underline">
                                            Privacy Policy
                                        </button>.
                                    </span>
                                </label>
                                
                                @error('agreeToPrivacy')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror

                                <div 
                                    x-show="showPolicy" 
                                    x-transition 
                                    x-cloak 
                                    class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm"
                                >
                                    <div @click.away="showPolicy = false"
                                        class="w-full max-w-2xl p-6 bg-white rounded-lg shadow-lg dark:bg-gray-800">
                                        <h3 class="mb-2 text-lg font-semibold text-gray-800 dark:text-white">
                                            Privacy Policy
                                        </h3>
                                        <div class="overflow-y-auto max-h-[60vh] text-sm text-gray-700 dark:text-gray-300 space-y-3">
                                            <x-booking-form-policy />
                                        </div>
                                        <div class="flex justify-end mt-6">
                                            <button type="button" @click="showPolicy = false"
                                                    class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700">
                                                I Understand
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                

                {{-- Footer Buttons --}}
                <div class="p-6 bg-white dark:bg-gray-900 border-t dark:border-gray-800 sticky bottom-0 mt-6" 
                    x-data="{ loading: false }">
                    <div class=" mb-4">
                        <div class="border rounded-2xl p-4 shadow-sm" 
                            style="background-color: rgba(var(--primary-rgb), 0.04); border-color: rgba(var(--primary-rgb), 0.1);">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-bold uppercase tracking-wider" style="color: var(--primary-color);">Price Estimate</span>
                                <div class="flex items-center text-xs opacity-70" style="color: var(--primary-color);">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Final price may vary
                                </div>
                            </div>
                            
                            <div class="flex justify-between items-end">
                                <div>
                                    <p class="text-2xl font-black text-gray-900 dark:text-white">
                                        @if($this->estimate)
                                            ₱{{ number_format($this->estimate['total'], 2) }}
                                        @else
                                            ₱0.00
                                        @endif
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">
                                        Total for <span class="font-bold" style="color: var(--primary-color);">{{ $this->estimate['days'] ?? 0 }} day(s)</span>
                                    </p>
                                </div>
                                
                                <div class="text-right">
                                    <p class="text-xs text-gray-400 uppercase font-bold">Daily Rate</p>
                                    <p class="text-sm font-bold text-gray-700 dark:text-gray-300">₱{{ number_format($car['price_starts_at'] ?? 0, 2) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        {{-- Back Button --}}
                        <button x-show="step > 1" 
                                @click="step--" 
                                type="button" 
                                class="flex-1 h-14 rounded-xl border border-gray-300 text-gray-700 dark:text-gray-300 font-semibold hover:bg-gray-50 transition-all">
                            Back
                        </button>

                        {{-- Next Steps with Validation --}}
                        <template x-if="step < 4">
                            <button @click="
                                    loading = true;
                                    $wire.validateStep(step)
                                        .then((result) => {
                                            if (result === true) {
                                                step++;
                                                window.scrollTo({ top: 0, behavior: 'smooth' });
                                            }
                                        })
                                        .catch(() => {
                                            console.log('Validation Failed');
                                        })
                                        .finally(() => { 
                                            loading = false; 
                                        });
                                " 
                                type="button" 
                                :disabled="loading"
                                class="flex-[2] h-14 rounded-xl text-white font-semibold shadow-lg hover:opacity-90 transition-all disabled:opacity-50 flex items-center justify-center"
                                :style="`background-color: var(--primary-color);` ">
                                
                                <span x-show="!loading" x-text="step === 1 ? 'Next: Contact Info' : (step === 2 ? 'Next: Upload Docs' : 'Next: Payment')"></span>
                                
                                <svg x-show="loading" class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </button>
                        </template>

                        {{-- Final Confirm --}}
                        <button x-show="step === 4" 
                            type="button" 
                            @click="
                                loading = true;
                                $wire.validateStep(step)
                                    .then((result) => {
                                        if (result === true) {
                                            $wire.saveBooking();
                                        }
                                    })
                                    .finally(() => { loading = false; });
                            "
                            :disabled="loading"
                            class="flex-[2] h-14 rounded-xl text-white font-semibold shadow-lg hover:opacity-90 transition-all flex items-center justify-center disabled:opacity-50"
                            :style="`background-color: var(--primary-color);` ">

                            <span x-show="!loading">Confirm Booking</span>
                            <span x-show="loading" class="flex items-center gap-2">
                                <svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Processing...
                            </span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>