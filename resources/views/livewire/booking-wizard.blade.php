<div>
    <style>
        :root {--tw-primary: {{ $company->primary_color }};}
        .filepond--label-action{
        color: var(--tw-primary) !important;
        }
        .datepicker-cell.selected{
        background-color: var(--tw-primary) !important;
        }
        .next-button{
        background-color: var(--tw-primary) !important;
        }
    </style>
    @php
        $times = [];
        for ($hour = 0; $hour < 24; $hour++) {
            foreach ([0, 30] as $minute) {
            $timeValue = sprintf('%02d:%02d', $hour, $minute);
            $ampm = date('g:i A', strtotime($timeValue));
            $times[$timeValue] = $ampm;
            }
        }
    @endphp
    
    <nav class="flex items-center justify-between px-6 py-3 bg-white shadow-lg dark:bg-gray-900 dark:text-white">
        <!-- Logo + Name -->
        <div class="flex items-center space-x-3">
            <img src="{{ $companyLogo }}" alt="Company Logo" class="object-contain w-10 h-10 rounded-md shadow-sm">
            <span class="text-lg font-semibold text-gray-800 dark:text-white">{{ $company->name }}</span>
        </div>

        <!-- Navbar Links -->
        <div x-data="{ dark: document.documentElement.classList.contains('dark'), openTrace: false, openRepeat: @entangle('openRepeat'), openMobile: false,repeat_token: @entangle('repeat_token') }" class="flex items-center">
            
            <!-- Desktop Links -->
            <div class="hidden space-x-6 sm:flex">
                <a href="#" 
                @click.prevent="openRepeat = true"
                class="text-sm font-medium text-gray-700 dark:text-gray-200 hover:text-[var(--tw-primary)] dark:hover:text-[var(--tw-primary)]">
                Repeat Renter?
                </a>

                <a href="#"
                @click.prevent="openTrace = true"
                class="text-sm font-medium text-gray-700 dark:text-gray-200 hover:text-[var(--tw-primary)] dark:hover:text-[var(--tw-primary)]">
                Track Booking
                </a>
            </div>

            <!-- Mobile Menu Toggle -->
            <button @click="openMobile = !openMobile" class="text-gray-600 sm:hidden dark:text-gray-300 focus:outline-none">
                <!-- Menu icon -->
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                        d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>

            <!-- Mobile Links -->
            <div x-show="openMobile" x-cloak 
                class="absolute z-50 p-4 space-y-2 bg-white rounded-lg shadow-lg top-14 right-6 dark:bg-gray-800 sm:hidden">
                <a href="#" 
                @click.prevent="openRepeat = true; openMobile = false;"
                class="block text-sm font-medium text-gray-700 dark:text-gray-200 hover:text-[var(--tw-primary)]">
                Repeat Renter?
                </a>

                <a href="#" 
                @click.prevent="openTrace = true; openMobile = false;"
                class="block text-sm font-medium text-gray-700 dark:text-gray-200 hover:text-[var(--tw-primary)]">
                Track Booking
                </a>
            </div>

            <!-- Repeat renter Modal -->
            <div x-show="openRepeat" x-cloak 
                class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
                <div @click.away="openRepeat = false" 
                    class="w-full max-w-md p-6 bg-white shadow-lg rounded-xl dark:bg-gray-800">

                    <h2 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white">Repeat Renter?</h2>
                    <span class="block mb-4 text-sm text-gray-500 dark:text-gray-400">
                        Upload the QR code you received from <strong>{{ $company->name }}</strong> to automatically retrieve your previous booking details.  
                        If you don’t have one, please contact them to request your QR code.
                    </span>

                    <div>
                        {{-- <label for="repeat_token" class="block text-sm font-medium text-gray-700 dark:text-gray-500">
                            Upload QR Code
                        </label> --}}
                        <input type="hidden" id="repeat_token" wire:model="repeat_token"
                            class="w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-[var(--tw-primary)] focus:ring-[var(--tw-primary)] dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>

                        <div class="mt-3">
                            <input type="file" accept="image/*" id="qr-file-input" class="hidden" onchange="readQRfromFile(event)">
                            <button type="button" id="qr-upload-btn"
                                onclick="document.getElementById('qr-file-input').click()" 
                                class="px-4 py-2 mt-3 text-sm font-semibold text-white rounded-lg shadow bg-[var(--tw-primary)] hover:opacity-90 w-full">
                                Upload QR Image
                            </button>
                        </div>
                        @error('repeat_token') 
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="flex justify-end mt-4 space-x-2">
                        <button type="button" 
                                @click="openRepeat = false;repeat_token = ''"
                                class="hidden px-4 py-2 text-sm font-medium text-gray-600 bg-gray-200 rounded-lg hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                            Cancel
                        </button>
                        <button type="button" id="submitTokenBtn" wire:click="submitRepeatToken"
                                class="hidden px-4 py-2 text-sm font-semibold text-white rounded-lg shadow bg-[var(--tw-primary)] hover:opacity-90">
                            Submit
                        </button>
                    </div>
                </div>
            </div>

            <!-- Trace Booking Modal -->
            <div x-show="openTrace" x-cloak 
                class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
                <div @click.away="openTrace = false" 
                    class="w-full max-w-md p-6 bg-white shadow-lg rounded-xl dark:bg-gray-800">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-white">
                        Track Your Booking
                    </h2>
                    <span class="block mb-4 text-sm text-gray-500 dark:text-gray-400">
                        Enter your reservation number to view your booking details and securely download your invoice.
                    </span>
                    <livewire:trace-booking />
                </div>
            </div>
        </div>
    </nav>


    <div class="max-w-6xl px-6 py-10 mx-auto">

        <!-- Page Header -->
        <div class="mt-2 mb-4 text-center ">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Book Your Ride</h1>
            <p class="mt-1 text-gray-500 dark:text-gray-400">Complete the steps below to reserve your vehicle</p>
        </div>
       
        <!-- Desktop / Tablet Stepper -->
        <div class="items-center justify-center hidden mb-4 sm:flex">
            <!-- Stepper Container -->
            <div class="flex items-center justify-center w-full max-w-4xl mx-auto space-x-4">
                @foreach ([1 => 'Renter Info', 2 => 'Booking Details', 3 => 'Car Selection', 4 => 'Requirements'] as $step => $label)
                    <div class="flex items-center">
                        <!-- Step Circle -->
                        <div class="relative flex items-center justify-center w-8 h-8 rounded-full text-white font-semibold transition-all
                            {{ $currentStep > $step ? 'bg-[var(--tw-primary)]' : ($currentStep === $step ? 'bg-[var(--tw-primary)] scale-110 shadow-lg' : 'bg-gray-300 dark:bg-gray-600') }}">
                            @if($currentStep > $step)
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                            @else
                                {{ $step }}
                            @endif
                        </div>
                        <!-- Step Label -->
                        <span class="ml-2 text-xs font-medium 
                            {{ $currentStep === $step ? 'text-[var(--tw-primary)]' : 'text-gray-500 dark:text-gray-400' }}">
                            {{ $label }}
                        </span>
                        <!-- Connector Line -->
                        @if($step !== 4)
                            <div class="flex-1 h-1 mx-2 rounded-full transition-all
                                {{ $currentStep > $step ? 'bg-[var(--tw-primary)]' : 'bg-gray-300 dark:bg-gray-600' }}">
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

        </div>

        <!-- Mobile Progress -->
        <div class="flex items-center justify-center mb-6 sm:hidden"> 
            @foreach ([1 => 'Renter', 2 => 'Booking', 3 => 'Car', 4 => 'Reqs'] as $step => $label)
            <div class="flex flex-col items-center flex-1">
                <div class="w-8 h-8 flex items-center justify-center rounded-full text-white font-semibold
                    {{ $currentStep >= $step ? 'bg-[var(--tw-primary)]' : 'bg-gray-300 dark:bg-gray-600' }}">
                    {{ $step }}
                </div>
                <span class="mt-1 text-[10px] text-center 
                    {{ $currentStep === $step ? 'text-[var(--tw-primary)]' : 'text-gray-500 dark:text-gray-400' }}">
                    {{ $label }}
                </span>
            </div>
            @endforeach
        </div>

        <div>
            <!-- Step 1 -->
            <div class="{{ $currentStep === 1 ? 'block' : 'hidden' }}">
                <h2 class="mb-2 text-xl font-bold dark:text-white">Step 1: Renter Information</h2>
                <p class="mb-6 text-sm text-gray-600 dark:text-gray-400">
                    Please provide your personal details so we can process your booking accurately.
                </p>
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <label class="block mb-1 text-sm font-medium dark:text-white">Full Name</label>
                        <input type="text" wire:model.defer="name" 
                            class="w-full px-4 py-2 text-base transition-all duration-200 border border-gray-300 shadow-sm dark:text-white rounded-xl bg-white/70 focus:border-blue-500 focus:ring-4 focus:ring-blue-200 dark:bg-gray-700 dark:border-gray-600 dark:focus:border-blue-400 dark:focus:ring-blue-900 focus:shadow-md">
                        @error('name') 
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block mb-1 text-sm font-medium dark:text-white">Contact</label>
                        {{-- <div class="relative flex items-center"> --}}
                            {{-- <span class="absolute text-gray-600 left-3 dark:text-white">+63</span> --}}
                            {{-- <input 
                                type="text" 
                                wire:model.defer="contact"  --}}
                                {{-- placeholder="9XXXXXXXXX"  --}}
                                {{-- inputmode="numeric" --}}
                                {{-- maxlength="10" --}}
                                {{-- pattern="[0-9]{10}" --}}
                                {{-- oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0,10)" --}}
                                {{-- class="w-full py-2 text-base transition-all duration-200 border border-gray-300 shadow-sm dark:text-white rounded-xl bg-white/70 focus:border-blue-500 focus:ring-4 focus:ring-blue-200 dark:bg-gray-700 dark:border-gray-600 dark:focus:border-blue-400 dark:focus:ring-blue-900 focus:shadow-md"> --}}
                                {{-- class="w-full px-4 py-2 pl-12 text-base transition-all duration-200 border border-gray-300 shadow-sm dark:text-white rounded-xl bg-white/70 focus:border-blue-500 focus:ring-4 focus:ring-blue-200 dark:bg-gray-700 dark:border-gray-600 dark:focus:border-blue-400 dark:focus:ring-blue-900 focus:shadow-md"> --}}
                        {{-- </div> --}}
                        <div class="relative flex items-center">
                            <input 
                                type="text" 
                                wire:model.defer="contact"
                                inputmode="numeric"
                                maxlength="11" 
                                pattern="[0-9]{10,11}" 
                                oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0,11)" 
                                class="w-full py-2 text-base transition-all duration-200 border border-gray-300 shadow-sm dark:text-white rounded-xl bg-white/70 focus:border-blue-500 focus:ring-4 focus:ring-blue-200 dark:bg-gray-700 dark:border-gray-600 dark:focus:border-blue-400 dark:focus:ring-blue-900 focus:shadow-md">
                        </div>

                        @error('contact') 
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="block mb-1 text-sm font-medium dark:text-white">Address</label>
                        <input type="text" wire:model.defer="address" placeholder="Street, City, Province" 
                            class="w-full px-4 py-2 text-base transition-all duration-200 border border-gray-300 shadow-sm dark:text-white rounded-xl bg-white/70 focus:border-blue-500 focus:ring-4 focus:ring-blue-200 dark:bg-gray-700 dark:border-gray-600 dark:focus:border-blue-400 dark:focus:ring-blue-900 focus:shadow-md">
                        @error('address') 
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block mb-1 text-sm font-medium dark:text-white">Email</label>
                        <input type="email" wire:model.defer="email" placeholder="you@email.com" 
                            class="w-full px-4 py-2 text-base transition-all duration-200 border border-gray-300 shadow-sm dark:text-white rounded-xl bg-white/70 focus:border-blue-500 focus:ring-4 focus:ring-blue-200 dark:bg-gray-700 dark:border-gray-600 dark:focus:border-blue-400 dark:focus:ring-blue-900 focus:shadow-md">
                        @error('email') 
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block mb-1 text-sm font-medium dark:text-white">Facebook Link</label>
                        <input type="url" wire:model.defer="facebook" 
                            class="w-full px-4 py-2 text-base transition-all duration-200 border border-gray-300 shadow-sm dark:text-white rounded-xl bg-white/70 focus:border-blue-500 focus:ring-4 focus:ring-blue-200 dark:bg-gray-700 dark:border-gray-600 dark:focus:border-blue-400 dark:focus:ring-blue-900 focus:shadow-md">
                        @error('facebook') 
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                            Please provide the link of your facebook profile for background check.
                        </p>
                    </div>
                </div>
                <div class="flex justify-end mt-8">
                    <button wire:click="firstStepSubmit" 
                        onclick="window.scrollTo({ top: 0, behavior: 'smooth' })"
                        class="px-6 py-2 font-medium text-white transition bg-blue-600 rounded-lg dark:text-white next-button hover:bg-blue-700">
                    Next
                    </button>
                </div>
            </div>
            <!-- Step 2 -->
            <div class="{{ $currentStep === 2 ? 'block' : 'hidden' }}">
                <h2 class="mb-2 text-xl font-bold dark:text-white">Step 2: Booking Details</h2>
                <p class="mb-6 text-sm text-gray-600 dark:text-gray-400">
                    Select your trip dates, times, and delivery method
                </p>
                <div class="mb-4" id="date-range-picker" date-rangepicker date-rangepicker-min-date="{{ date('m/d/Y') }}">
                    <div class="mb-2">
                        <label class="block mb-1 text-sm font-medium dark:text-white">Trip Start <span class="text-red-500">*</span></label>
                        <div class="grid grid-cols-2 gap-2">
                            <div class="flex flex-col">
                                <div class="relative">
                                    <!-- calendar icon -->
                                    <div class="absolute inset-y-0 flex items-center pointer-events-none start-0 ps-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 2.994v2.25m10.5-2.25v2.25m-14.252 13.5V7.491a2.25 2.25 0 0 1 2.25-2.25h13.5a2.25 2.25 0 0 1 2.25 2.25v11.251m-18 0a2.25 2.25 0 0 0 2.25 2.25h13.5a2.25 2.25 0 0 0 2.25-2.25m-18 0v-7.5a2.25 2.25 0 0 1 2.25-2.25h13.5a2.25 2.25 0 0 1 2.25 2.25v7.5m-6.75-6h2.25m-9 2.25h4.5m.002-2.25h.005v.006H12v-.006Zm-.001 4.5h.006v.006h-.006v-.005Zm-2.25.001h.005v.006H9.75v-.006Zm-2.25 0h.005v.005h-.006v-.005Zm6.75-2.247h.005v.005h-.005v-.005Zm0 2.247h.006v.006h-.006v-.006Zm2.25-2.248h.006V15H16.5v-.005Z" />
                                        </svg>

                                    </div>
                                    <!-- input field -->
                                    <input 
                                        id="startDate" 
                                        wire:model.defer="start_date" 
                                        datepicker 
                                        datepicker-min-date="{{ date('m/d/Y') }}" 
                                        name="start_date" 
                                        type="text" 
                                        autocomplete="off"
                                        readonly
                                        class="border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full ps-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400  dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" 
                                        placeholder="Select date">
                                </div>
                                <!-- error message under input -->
                                @error('start_date') 
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                            <!-- Start Time -->
                            <div class="flex flex-col">
                                <div 
                                    x-data="{ openStart: false}" 
                                    class="relative w-full"
                                    >
                                    <!-- Toggle Button -->
                                    <button 
                                        type="button" 
                                        @click="openStart = true" 
                                        class="inline-flex items-center justify-center w-full px-5 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-lg me-2 focus:outline-none dark:bg-gray-800 dark:text-white dark:border-gray-600 timepicker-toggle"
                                        >
                                    <span x-text="$wire.start_time ? $wire.start_time : 'Pick start time'"></span>
                                    </button>
                                    <!-- Modal -->
                                    <div 
                                        x-show="openStart" 
                                        x-transition.opacity 
                                        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
                                        >
                                        <div class="w-full max-w-md p-5 bg-white shadow-lg dark:bg-gray-800 rounded-xl">
                                            <!-- Header -->
                                            <div class="flex items-center justify-between pb-2 mb-4 border-b">
                                                <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Select Start Time</h2>
                                                <button @click="openStart = false" class="text-gray-500 hover:text-gray-800 dark:hover:text-gray-300">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            </div>
                                            <!-- Time Options -->
                                            <div class="grid grid-cols-3 gap-2 overflow-y-auto max-h-80">
                                                <template 
                                                    x-for="time in Array.from({length:48},(_,i)=>{
                                                    let h = (i/2|0);
                                                    let m = i%2?'30':'00';
                                                    let displayH = h==0?12:(h>12?h-12:h);
                                                    let ampm = h < 12 ? 'AM' : 'PM';
                                                    return {time: `${displayH.toString().padStart(2,'0')}:${m} ${ampm}`, hour: h, minute: m};
                                                    })" 
                                                    :key="time.time"
                                                    >
                                                    <button 
                                                        type="button" 
                                                        :hidden="(new Date().toDateString() === new Date($wire.start_date).toDateString()) && (time.hour < new Date().getHours() || (time.hour === new Date().getHours() && parseInt(time.minute) <= new Date().getMinutes()))"
                                                        @click="$wire.start_time = time.time; openStart = false" 
                                                        class="px-2 py-2 text-sm font-medium border rounded-lg transition text-[color:var(--tw-primary)] border-[color:var(--tw-primary)] hover:bg-[color:var(--tw-primary)] hover:text-white dark:text-white dark:border-[color:white] dark:bg-[color:var(--tw-primary)] dark:hover:bg-white dark:hover:text-[color:var(--tw-primary)] disabled:bg-gray-200 disabled:text-gray-400 disabled:border-gray-300"
                                                    >
                                                        <span x-text="time.time"></span>
                                                    </button>

                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @error('start_time')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror 
                            </div>
                        </div>
                    </div>
                    <div class="mt-2">
                        <label class="block mb-1 text-sm font-medium dark:text-white">Trip Ends <span class="text-red-500">*</span></label>
                        <div class="grid grid-cols-2 gap-2">
                            <!-- End Date -->
                            <div class="flex flex-col">
                                <div class="relative">
                                    <div class="absolute inset-y-0 flex items-center pointer-events-none start-0 ps-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 2.994v2.25m10.5-2.25v2.25m-14.252 13.5V7.491a2.25 2.25 0 0 1 2.25-2.25h13.5a2.25 2.25 0 0 1 2.25 2.25v11.251m-18 0a2.25 2.25 0 0 0 2.25 2.25h13.5a2.25 2.25 0 0 0 2.25-2.25m-18 0v-7.5a2.25 2.25 0 0 1 2.25-2.25h13.5a2.25 2.25 0 0 1 2.25 2.25v7.5m-6.75-6h2.25m-9 2.25h4.5m.002-2.25h.005v.006H12v-.006Zm-.001 4.5h.006v.006h-.006v-.005Zm-2.25.001h.005v.006H9.75v-.006Zm-2.25 0h.005v.005h-.006v-.005Zm6.75-2.247h.005v.005h-.005v-.005Zm0 2.247h.006v.006h-.006v-.006Zm2.25-2.248h.006V15H16.5v-.005Z" />
                                        </svg>
                                    </div>
                                    <input id="endDate" wire:model.defer="end_date"
                                        datepicker datepicker-min-date="{{ date('m/d/Y') }}" 
                                        name="end_date" type="text" autocomplete="off" readonly
                                        class=" border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full ps-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                        placeholder="Select date">
                                </div>
                                <!-- error message under input -->
                                @error('end_date')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                            <!-- End Time -->
                            <div class="flex flex-col">
                                <div 
                                    x-data="{ openEnd: false}" 
                                    class="relative w-full"
                                    >
                                    <!-- Toggle Button -->
                                    <button 
                                        type="button" 
                                        @click="openEnd = true" 
                                        class="inline-flex items-center justify-center w-full px-5 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-lg me-2 focus:outline-none dark:bg-gray-800 dark:text-white dark:border-gray-600 timepicker-toggle"
                                        >
                                    <span x-text="$wire.end_time ? $wire.end_time : 'Pick end time'"></span>
                                    </button>
                                    <!-- Modal -->
                                    <div 
                                        x-show="openEnd" 
                                        x-transition.opacity 
                                        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
                                        >
                                        <div class="w-full max-w-md p-5 bg-white shadow-lg dark:bg-gray-800 rounded-xl">
                                            <!-- Header -->
                                            <div class="flex items-center justify-between pb-2 mb-4 border-b">
                                                <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Select End Time</h2>
                                                <button @click="openEnd = false" class="text-gray-500 hover:text-gray-800 dark:hover:text-gray-300">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            </div>
                                            <!-- Time Options -->
                                            <div class="grid grid-cols-3 gap-2 overflow-y-auto max-h-80">
                                                <template 
                                                    x-for="time in Array.from({length:48},(_,i)=>{
                                                    let h = i/2|0;
                                                    let m = i%2?'30':'00';
                                                    let displayH = h==0?12:(h>12?h-12:h);
                                                    let ampm = h<12?'AM':'PM';
                                                    return {time: `${displayH.toString().padStart(2,'0')}:${m} ${ampm}`, hour: h, minute: parseInt(m)};
                                                    })" 
                                                    :key="time.time"
                                                    >
                                                    <button 
                                                        type="button" 
                                                        :hidden="(new Date($wire.end_date).toDateString() === new Date().toDateString() && (time.hour < new Date().getHours() || 
                                                        (time.hour === new Date().getHours() && time.minute <= new Date().getMinutes()))) ||
                                                        ($wire.start_date && $wire.start_time && ( new Date($wire.end_date + ' ' + time.time) <= new Date($wire.start_date + ' ' + $wire.start_time) ))"
                                                        @click="$wire.end_time = time.time; openEnd = false" 
                                                        class="px-2 py-2 text-sm font-medium border rounded-lg transition text-[color:var(--tw-primary)] border-[color:var(--tw-primary)] hover:bg-[color:var(--tw-primary)] hover:text-white dark:text-white dark:border-[color:white] dark:bg-[color:var(--tw-primary)] dark:hover:bg-white dark:hover:text-[color:var(--tw-primary)] disabled:bg-gray-200 disabled:text-gray-400 disabled:border-gray-300"
                                                    >
                                                        <span x-text="time.time"></span>
                                                    </button>

                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @error('end_time')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror 
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mb-4">
                    <div x-data="{ open: false }" class="relative">
                        <label class="block mb-1 text-sm font-medium dark:text-white">
                            Delivery Method <span class="text-red-500">*</span>
                        </label>

                        <!-- Trigger -->
                        <button
                            type="button"
                            @click="open = !open"
                            class="w-full px-3 py-2 text-left border border-gray-300 rounded-lg dark:text-white dark:bg-gray-700 dark:color-gray-400 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <span x-text="{
                                'renter_pickup_renter_return': 'Renter Pickup & Renter Return',
                                'renter_pickup_owner_collection': 'Renter Pickup & Owner Collection',
                                'owner_delivery_renter_return': 'Owner Delivery & Renter Return',
                                'owner_delivery_owner_collection': 'Owner Delivery & Owner Collection'
                            }[$wire.pickup_option] || 'Select Delivery Method'"></span>
                        </button>

                        <!-- Dropdown -->
                        <div
                            x-show="open"
                            @click.outside="open = false"
                            class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700">

                            @php $companyName = addslashes($company->name); @endphp

                            <template x-for="(desc, key) in {
                                'renter_pickup_renter_return': 'You will pick up the car from {{ $companyName }} garage and return it to the garage after use.',
                                'renter_pickup_owner_collection': 'You will pick up the car from {{ $companyName }} garage, and {{ $companyName }} will collect it from you after the rental.',
                                'owner_delivery_renter_return': '{{ $companyName }} will deliver the car to your address, and you will return it to {{ $companyName }} garage after use.',
                                'owner_delivery_owner_collection': '{{ $companyName }} will deliver the car to your address and also collect it from you after the rental.'
                            }">
                                <div 
                                    @click="$wire.set('pickup_option', key); open = false"
                                    class="px-3 py-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700">
                                    <div class="font-medium text-gray-800 dark:text-gray-400 dark:hover:text-gray-900"
                                        x-text="{
                                            'renter_pickup_renter_return': 'Renter Pickup & Renter Return',
                                            'renter_pickup_owner_collection': 'Renter Pickup & Owner Collection',
                                            'owner_delivery_renter_return': 'Owner Delivery & Renter Return',
                                            'owner_delivery_owner_collection': 'Owner Delivery & Owner Collection'
                                        }[key]">
                                    </div>
                                    <div class="mt-1 text-xs text-blue-600 dark:text-blue-400" x-text="desc"></div>
                                </div>
                            </template>
                        </div>
                    </div>



                    @error('pickup_option') 
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                        Note: Delivery options may include additional fees depending on distance.
                    </p>
                </div>

                @php
                    $showDelivery = in_array($pickup_option, ['owner_delivery_renter_return','owner_delivery_owner_collection']);
                    $showReturn   = in_array($pickup_option, ['renter_pickup_owner_collection','owner_delivery_owner_collection']);
                    $colClass     = ($showDelivery && $showReturn) ? 'md:grid-cols-2' : 'md:grid-cols-1';
                @endphp

                <div class="grid grid-cols-1 gap-4 {{ $colClass }}">
                    <!-- Show Delivery Address -->
                    @if($showDelivery)
                        <div class="mb-4">
                            <label class="block mb-1 text-sm font-medium dark:text-white">
                                Delivery Address
                            </label>
                            <input type="text" wire:model.defer="pickup_address"
                                class="w-full px-4 py-2 text-base transition-all duration-200 border border-gray-300 shadow-sm rounded-xl bg-white/70 focus:border-blue-500 focus:ring-4 focus:ring-blue-200 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:focus:border-blue-400 dark:focus:ring-blue-900 focus:shadow-md">
                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                Enter the address where the owner should deliver the vehicle.
                            </p>
                            @error('pickup_address') 
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif

                    <!-- Show Return Address -->
                    @if($showReturn)
                        <div class="mb-4">
                            <label class="block mb-1 text-sm font-medium dark:text-white">
                                Return Address
                            </label>
                            <input type="text" wire:model.defer="return_address"
                                class="w-full px-4 py-2 text-base transition-all duration-200 border border-gray-300 shadow-sm rounded-xl bg-white/70 focus:border-blue-500 focus:ring-4 focus:ring-blue-200 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:focus:border-blue-400 dark:focus:ring-blue-900 focus:shadow-md">
                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                Specify the drop-off location for the vehicle return.
                            </p>
                            @error('return_address') 
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif
                </div>

                <div class="mb-4">
                    <label class="block mb-1 text-sm font-medium dark:text-white">Destinations <span class="text-red-500">*</span></label>
                    <input type="text" wire:model.defer="destination"
                        class="w-full px-4 py-2 text-base transition-all duration-200 border border-gray-300 shadow-sm rounded-xl bg-white/70 focus:border-blue-500 focus:ring-4 focus:ring-blue-200 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:focus:border-blue-400 dark:focus:ring-blue-900 focus:shadow-md">
                    @error('destination') 
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                        Please declare all your destinations.
                    </p>
                </div>

                <div class="mb-4">
                    <label class="block mb-1 text-sm font-medium dark:text-white">Other Driver</label>
                    <input type="text" wire:model.defer="other_drivers"
                        class="w-full px-4 py-2 text-base transition-all duration-200 border border-gray-300 shadow-sm rounded-xl bg-white/70 focus:border-blue-500 focus:ring-4 focus:ring-blue-200 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:focus:border-blue-400 dark:focus:ring-blue-900 focus:shadow-md">
                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                        Optional: If driver is different from the renter.
                    </p>
                    @error('other_drivers') 
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="mb-4">
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" wire:model.defer="with_driver" value="1" class="sr-only peer">
                        <div
                            class="relative w-11 h-6 bg-gray-200 rounded-full 
                            peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 
                            dark:peer-focus:ring-blue-800 dark:bg-gray-700
                            peer-checked:bg-blue-600 dark:peer-checked:bg-blue-600
                            peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full
                            peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] 
                            after:start-[2px] after:bg-white after:border-gray-300 after:border 
                            after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600">
                        </div>
                        <span class="text-sm font-medium text-gray-900 ms-3 dark:text-white">
                        Book with Driver
                        </span>
                    </label>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Add a professional driver to your booking for convenience and safety. <br>
                        <span class="font-medium text-blue-600 dark:text-blue-400">Extra fees apply.</span>
                    </p>
                </div>

                <div class="mb-4">
                    <label class="block mb-1 text-sm font-medium dark:text-white">How did you find us? <span class="text-red-500">*</span></label>
                    <select wire:model.live="source"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg dark:text-white focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:focus:border-blue-400">
                        <option value="">Please select one</option>
                        @if(!empty($bookingSources))
                            @foreach($bookingSources as $source)
                                <option value="{{ $source->id }}">{{ $source->source }}</option>
                            @endforeach
                        @endif
                    </select>
                    @error('source') 
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-between mt-8">
                    <button wire:click="back" 
                        class="px-6 py-2 text-gray-700 transition bg-gray-200 rounded-lg hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 dark:text-white">
                    Back
                    </button>
                    <button wire:click="secondStepSubmit" 
                        onclick="window.scrollTo({ top: 0, behavior: 'smooth' })"
                        class="px-6 py-2 font-medium text-white transition bg-blue-600 rounded-lg next-button hover:bg-blue-700">
                    Next
                    </button>
                </div>
            </div>
            <!-- Step 3 -->
            <div class="{{ $currentStep === 3 ? 'block' : 'hidden' }}">
                <h2 class="mb-2 text-xl font-bold dark:text-white">Step 3: Car Selection</h2>
                <p class="mb-6 text-sm text-gray-600 dark:text-gray-400">
                    Choose the car you’d like to book from the available options below
                </p>
                <div class="flex gap-2 pb-2 mb-4 overflow-x-auto flex-nowrap"> 
                    <button wire:click="$set('selectedCarType', null)"
                        class="px-4 py-2 rounded-full border text-sm font-medium transition
                        {{ $selectedCarType 
                        ? 'bg-gray-100 text-gray-700 hover:bg-gray-200' 
                        : 'bg-[var(--tw-primary)] text-white border-[var(--tw-primary)]' }}">
                    All
                    </button>
                    @foreach($carTypes as $type)
                        @if($type)
                            <button wire:click="$set('selectedCarType', {{ $type?->id }})"
                                    class="px-4 py-2 rounded-full border text-sm font-medium transition
                                    {{ $selectedCarType == $type?->id
                                    ? 'bg-[var(--tw-primary)] text-white border-[var(--tw-primary)]' 
                                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                                {{ $type?->car_type }}
                            </button>
                        @endif
                    @endforeach
                </div>
                <div class="max-h-[80vh] overflow-y-auto p-2">
                    <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                        @foreach($cars as $car)
                            <label class="cursor-pointer {{ $selectedCarType && $car->car_type_id !== $selectedCarType ? 'hidden' : '' }}">
                                <input 
                                    type="radio" 
                                    name="selected_car" 
                                    value="{{ $car->id }}" 
                                    class="hidden peer"
                                    wire:model="selectedCarId"
                                    >
                                <div class="overflow-hidden border shadow-sm rounded-xl peer-checked:ring-2 peer-checked:ring-blue-500 peer-checked:border-blue-500">
                                    <img src="{{ Storage::url($car->image) }}" 
                                        alt="{{ $car->name }}" 
                                        class="object-contain w-full p-2 bg-gray-100 h-44 dark:bg-gray-800">
                                    <div class="p-3 space-y-1">
                                        <div class="flex items-center justify-between w-full">
                                            <!-- Left: Name + Type -->
                                            <div class="flex items-center space-x-2">
                                                <h3 class="text-base font-semibold truncate dark:text-gray-400">{{ $car->name }}</h3>
                                                @if(isset($car->carType->car_type))
                                                    <span class="inline-block px-2 py-0.5 text-xs font-medium text-blue-500 border border-blue-500 rounded-full">
                                                        {{ $car->carType->car_type }}
                                                    </span>                                            
                                                @endif
                                            </div>

                                            <!-- Right: Price -->
                                            <div class="text-right">
                                                <span class="block text-xs text-gray-500">Starts at</span>
                                                <span class="font-bold text-green-600 text-md dark:text-green-400">
                                                    {{ $car->price_starts_at ? '₱'.number_format($car->price_starts_at, 2) : '-' }}
                                                </span>
                                            </div>
                                        </div>

                                        <div class="mt-1 text-xs leading-snug text-gray-700 dark:text-gray-400">
                                            <p class="text-md">
                                                <span class="font-semibold ">{{ ucfirst(strtolower($car->brand)) }}</span>
                                                {{ ucfirst(strtolower($car->model)) }} {{ $car->year }}
                                            </p>
                                            <div class="grid grid-cols-2 gap-2 mt-2">
                                                @if($car->fuel_type)
                                                    <div class="flex items-center space-x-1">
                                                        <svg viewBox="0 0 26 24" fill="none" class="w-4 h-4">
                                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M4.667 0A2.667 2.667 0 002 2.667v18.666A1.333 1.333 0 002 24h16a1.333 1.333 0 100-2.667v-6.666h1.333V18A3.333 3.333 0 1026 18V9.885c0-.707-.281-1.385-.782-1.885L21.61 4.39a1.334 1.334 0 10-1.885 1.886l1.466 1.468A2.665 2.665 0 0023.334 12l.002 6A.666.666 0 1122 18v-3.333A2.667 2.667 0 0019.334 12h-1.333V2.667A2.667 2.667 0 0015.334 0H4.668zm10.666 9.333V2.667H4.666v6.666h10.667z" fill="#696969"></path>
                                                        </svg>
                                                        <span class="text-xs text-gray-600 dark:text-gray-300">{{ $car->fuel_type }}</span>
                                                    </div>
                                                @endif

                                                @if($car->seat_count)
                                                    <div class="flex items-center space-x-1">
                                                        <svg version="1.1" class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" preserveAspectRatio="xMidYMid meet">
                                                            <path d="M0 0 C1.229 -0.006 1.229 -0.006 2.483 -0.012 C4.205 -0.014 5.927 -0.007 7.649 0.01 C10.274 0.031 12.896 0.01 15.521 -0.016 C17.202 -0.013 18.882 -0.008 20.562 0 C21.73 -0.012 21.73 -0.012 22.921 -0.025 C28.904 0.078 32.498 1.892 36.859 6.129 C40.492 10.684 41.338 13.607 41.392 19.344 C41.411 21.137 41.411 21.137 41.431 22.966 C41.438 24.26 41.446 25.554 41.453 26.887 C41.457 27.547 41.461 28.207 41.465 28.887 C41.486 32.385 41.501 35.883 41.51 39.38 C41.521 42.977 41.555 46.573 41.595 50.169 C41.621 52.948 41.63 55.727 41.633 58.506 C41.64 60.471 41.667 62.436 41.694 64.401 C41.673 72.813 40.86 78.458 35.09 84.906 C31.03 87.201 26.884 88.349 22.215 88.531 C19.245 87.541 19.245 87.541 16.215 86.531 C16.215 92.471 16.215 98.411 16.215 104.531 C17.906 104.882 19.597 105.233 21.34 105.594 C32.644 108.436 42.443 113.934 51.215 121.531 C52.002 122.167 52.002 122.167 52.804 122.816 C89.417 152.903 100.787 214.94 114.15 258.063 C119.678 275.895 125.55 293.588 131.695 311.216 C132 312.096 132.305 312.975 132.62 313.882 C133.188 315.518 133.76 317.152 134.336 318.785 C135.391 321.824 136.215 324.29 136.215 327.531 C137.119 327.224 138.023 326.917 138.955 326.601 C157.163 320.445 175.185 314.618 194.215 311.531 C195.418 311.333 196.62 311.134 197.859 310.93 C203.903 310.231 210.017 310.307 216.094 310.291 C218.248 310.281 220.402 310.25 222.557 310.219 C245.879 310.04 267.536 316.916 284.798 333.083 C301.61 350.266 307.168 374.611 307.652 397.844 C307.704 399.241 307.704 399.241 307.757 400.666 C307.791 406.243 306.963 410.04 303.977 414.766 C299.411 419.34 294.967 420.98 288.669 421.071 C287.441 421.059 287.441 421.059 286.189 421.046 C285.302 421.053 284.416 421.06 283.502 421.067 C280.544 421.085 277.586 421.075 274.627 421.065 C272.501 421.072 270.375 421.081 268.249 421.091 C262.483 421.113 256.717 421.11 250.95 421.101 C246.134 421.096 241.318 421.104 236.501 421.111 C225.139 421.128 213.776 421.124 202.414 421.108 C190.696 421.091 178.978 421.107 167.26 421.139 C157.194 421.166 147.129 421.173 137.063 421.164 C131.053 421.159 125.044 421.162 119.034 421.182 C113.383 421.201 107.732 421.195 102.081 421.17 C100.009 421.165 97.937 421.168 95.865 421.181 C93.032 421.197 90.201 421.182 87.368 421.159 C86.552 421.171 85.735 421.183 84.894 421.195 C78.951 421.099 74.782 419.373 70.215 415.531 C66.62 410.946 64.993 405.454 63.289 399.953 C63.025 399.126 62.761 398.299 62.489 397.447 C61.919 395.662 61.353 393.875 60.789 392.088 C59.256 387.236 57.693 382.393 56.129 377.551 C55.806 376.551 55.484 375.551 55.151 374.521 C51.835 364.288 48.336 354.12 44.777 343.969 C44.468 343.085 44.158 342.2 43.839 341.289 C38.547 326.205 33.164 311.153 27.776 296.103 C21.159 277.612 14.619 259.096 8.215 240.531 C7.813 239.367 7.813 239.367 7.403 238.179 C-1.79 211.503 -10.603 184.589 -16.785 157.031 C-16.969 156.228 -17.152 155.424 -17.342 154.596 C-21.067 136.863 -21.123 118.835 -21.156 100.801 C-21.165 98.269 -21.174 95.737 -21.184 93.205 C-21.2 87.938 -21.208 82.671 -21.211 77.404 C-21.214 71.336 -21.244 65.269 -21.286 59.202 C-21.325 53.318 -21.336 47.435 -21.337 41.551 C-21.342 39.066 -21.354 36.58 -21.375 34.094 C-21.402 30.627 -21.397 27.162 -21.385 23.695 C-21.399 22.678 -21.414 21.661 -21.429 20.614 C-21.362 14.072 -20.139 9.533 -15.785 4.531 C-10.872 0.467 -6.227 -0.065 0 0 Z " fill="#696969" transform="translate(112.78515625,45.46875)"/>
                                                        </svg>
                                                        <span class="text-xs text-gray-600 dark:text-gray-300">{{ $car->seat_count }}-seaters</span>
                                                    </div>
                                                @endif

                                                @if($car->transmission)
                                                    <div class="flex items-center space-x-1">
                                                        <svg viewBox="0 0 28 28" fill="none" class="w-4 h-4">
                                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M27.333 14.001c0 7.364-5.97 13.334-13.333 13.334C6.636 27.335.667 21.365.667 14 .667 6.638 6.636.668 14 .668s13.333 5.97 13.333 13.333zM12.35 24.541c-.878-2.156-3.39-7.997-5.017-8.54-1.138-.38-2.709-.327-3.85-.211a10.675 10.675 0 008.867 8.751zM3.772 10.962C5.082 6.552 9.165 3.335 14 3.335s8.919 3.217 10.227 7.627c-2.105-.394-5.883-.96-10.227-.96s-8.122.566-10.228.96zm20.745 4.828c-1.142-.116-2.712-.168-3.85.211-1.628.543-4.139 6.384-5.017 8.54a10.675 10.675 0 008.867-8.751z" fill="#696969"></path>
                                                        </svg>
                                                        <span class="text-xs text-gray-600 dark:text-gray-300">{{ $car->transmission }}</span>
                                                    </div>
                                                @endif

                                                {{-- Coding column --}}
                                                <div class="flex items-center space-x-1">
                                                    <svg version="1.1" class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" preserveAspectRatio="xMidYMid meet">
                                                        <path d="M0 0 C1.018 0.002 2.036 0.004 3.086 0.007 C17.71 0.056 32.003 0.37 46.375 3.312 C47.372 3.506 48.368 3.699 49.395 3.898 C88.014 11.529 124.14 27.25 155.375 51.312 C156.187 51.933 156.999 52.553 157.836 53.191 C174.901 66.515 191.261 82.132 204.101 99.594 C205.398 101.343 206.728 103.061 208.066 104.777 C215.931 114.992 222.316 125.95 228.375 137.312 C229.038 138.553 229.038 138.553 229.715 139.819 C241.232 161.722 248.903 186.013 253.375 210.312 C253.536 211.113 253.697 211.914 253.862 212.739 C259.354 240.817 259.109 274.302 253.375 302.312 C253.182 303.309 252.988 304.306 252.789 305.333 C244.67 346.421 227.301 383.515 201.375 416.312 C200.563 417.35 199.751 418.388 198.914 419.457 C186.246 435.023 171.693 449.601 155.375 461.312 C154.053 462.29 152.731 463.268 151.41 464.246 C120.177 487.098 84.427 502.31 46.375 509.312 C45.574 509.473 44.774 509.634 43.949 509.8 C15.882 515.29 -17.627 515.062 -45.625 509.312 C-46.638 509.114 -47.65 508.915 -48.693 508.71 C-82.086 502.033 -115.865 489.413 -143.625 469.312 C-144.234 468.872 -144.843 468.431 -145.471 467.977 C-150.25 464.501 -154.959 460.94 -159.625 457.312 C-160.167 456.892 -160.709 456.471 -161.268 456.038 C-177.503 443.275 -192.572 428.085 -204.625 411.312 C-205.489 410.143 -206.353 408.973 -207.219 407.805 C-222.362 387.179 -234.444 364.57 -242.625 340.312 C-243.038 339.09 -243.038 339.09 -243.459 337.843 C-246.698 328.072 -249.23 318.19 -251.375 308.125 C-251.574 307.2 -251.773 306.276 -251.977 305.323 C-255.27 289.116 -256 273.182 -255.938 256.688 C-255.935 255.669 -255.933 254.651 -255.931 253.602 C-255.881 238.978 -255.567 224.684 -252.625 210.312 C-252.335 208.818 -252.335 208.818 -252.039 207.292 C-244.41 168.684 -228.698 132.531 -204.625 101.312 C-203.982 100.477 -203.339 99.642 -202.676 98.781 C-188.573 80.827 -172.219 63.639 -153.625 50.312 C-153.083 49.921 -152.542 49.529 -151.983 49.125 C-121.069 26.841 -86.694 11.74 -49.375 4.062 C-48.644 3.911 -47.914 3.76 -47.161 3.604 C-31.467 0.574 -15.945 -0.061 0 0 Z M-134.625 75.312 C-135.248 75.77 -135.871 76.227 -136.512 76.699 C-141.32 80.223 -141.32 80.223 -145.625 84.312 C-145.625 89.118 -143.952 91.333 -141.275 95.085 C-140.8 95.757 -140.325 96.428 -139.836 97.121 C-133.346 106.141 -126.275 114.661 -118.938 123 C-118.224 123.815 -117.51 124.629 -116.774 125.468 C-98.116 146.439 -79.329 159.569 -50.881 161.446 C-31.683 162.517 -12.551 161.731 6.624 160.583 C7.305 160.543 7.986 160.503 8.687 160.462 C15.827 160.038 22.945 159.524 30.06 158.788 C47.519 157.151 47.519 157.151 51.855 160.347 C54.182 162.902 55.757 165.275 57.375 168.312 C58.366 169.466 59.385 170.597 60.457 171.676 C60.908 172.163 61.358 172.65 61.823 173.153 C62.273 173.638 62.723 174.124 63.188 174.625 C64.214 175.734 65.24 176.844 66.266 177.953 C66.779 178.508 67.293 179.063 67.822 179.635 C74.248 186.578 80.665 193.53 87.062 200.5 C88.32 201.87 89.581 203.239 90.874 204.576 C92.375 206.312 92.375 206.312 93.991 208.866 C96.582 212.537 98.929 215.669 103.452 216.859 C109.547 217.691 115.679 217.47 121.812 217.312 C124.574 217.284 127.335 217.27 130.096 217.263 C131.805 217.255 133.514 217.231 135.222 217.19 C147.85 216.992 160.398 220.687 169.785 229.5 C180.664 241.903 181.826 254.996 181.887 270.793 C181.904 272.459 181.922 274.125 181.941 275.79 C181.988 280.139 182.018 284.487 182.045 288.835 C182.075 293.287 182.121 297.738 182.166 302.189 C182.252 310.897 182.319 319.605 182.375 328.312 C181.814 328.288 181.254 328.264 180.676 328.238 C174.82 327.994 168.966 327.829 163.106 327.708 C160.922 327.653 158.739 327.578 156.556 327.483 C144.308 326.373 144.308 326.373 133.476 330.669 C131.701 333.502 130.566 336.202 129.375 339.312 C125.958 343.487 122.196 346.829 117.375 349.312 C118.944 353.096 121.306 355.485 124.192 358.356 C124.68 358.845 125.168 359.333 125.671 359.836 C127.278 361.442 128.893 363.041 130.508 364.641 C131.626 365.755 132.745 366.871 133.862 367.987 C136.801 370.917 139.745 373.842 142.69 376.765 C145.697 379.751 148.698 382.743 151.699 385.734 C157.586 391.599 163.478 397.458 169.375 403.312 C173.489 401.458 175.737 398.776 178.438 395.25 C178.916 394.63 179.394 394.01 179.887 393.371 C200.2 366.621 214.289 336.136 221.375 303.312 C221.69 301.895 221.69 301.895 222.012 300.449 C228.257 271.22 227.866 238.449 221.375 209.312 C221.188 208.458 221.001 207.604 220.808 206.723 C219.554 201.18 218.055 195.74 216.375 190.312 C216.134 189.528 215.894 188.743 215.646 187.935 C204.815 153.316 185.567 123.17 160.375 97.312 C159.477 96.374 158.578 95.436 157.652 94.469 C133.632 69.802 102.02 51.766 69.375 41.312 C68.703 41.091 68.031 40.87 67.339 40.642 C-0.963 18.274 -77.26 33.079 -134.625 75.312 Z M-171.93 110.465 C-196.419 138.304 -212.726 173.263 -220.625 209.312 C-220.823 210.199 -221.02 211.086 -221.224 212 C-227.452 241.322 -227.138 274.08 -220.625 303.312 C-220.344 304.594 -220.344 304.594 -220.058 305.902 C-218.804 311.445 -217.305 316.885 -215.625 322.312 C-215.384 323.097 -215.144 323.882 -214.896 324.69 C-204.353 358.388 -185.779 388.439 -161.218 413.673 C-159.801 415.131 -158.403 416.607 -157.008 418.086 C-150.44 424.912 -143.19 430.65 -135.625 436.312 C-135.058 436.741 -134.49 437.169 -133.906 437.61 C-86.971 472.834 -27.24 488.066 30.865 480.115 C69.162 474.463 104.181 460.279 135.375 437.312 C135.998 436.855 136.621 436.398 137.262 435.926 C142.07 432.402 142.07 432.402 146.375 428.312 C146.375 423.906 146.057 423.418 143.417 420.187 C142.757 419.374 142.097 418.56 141.416 417.722 C140.679 416.839 139.941 415.956 139.181 415.046 C138.407 414.109 137.632 413.173 136.834 412.208 C126.504 399.833 115.739 387.854 104.75 376.062 C103.959 375.211 103.959 375.211 103.153 374.343 C77.509 346.792 54.04 323.097 14.423 321.178 C7.509 321.006 0.651 321.271 -6.25 321.688 C-7.375 321.749 -8.501 321.811 -9.66 321.874 C-17.176 322.314 -24.634 323.002 -32.102 323.961 C-33.066 324.084 -34.03 324.207 -35.023 324.334 C-45.42 325.528 -45.42 325.528 -54.095 330.725 C-55.621 333.228 -56.667 335.551 -57.625 338.312 C-62.991 346.553 -71.863 351.133 -81.133 353.652 C-93.821 356.051 -105.429 354.357 -116.438 347.562 C-123.858 342.212 -127.514 336.535 -131.625 328.312 C-148.125 328.312 -164.625 328.312 -181.625 328.312 C-181.536 310.116 -181.536 310.116 -181.465 302.287 C-181.417 296.97 -181.373 291.654 -181.354 286.337 C-181.339 282.044 -181.304 277.752 -181.257 273.46 C-181.242 271.832 -181.234 270.204 -181.233 268.577 C-181.218 253.652 -179.184 240.691 -168.625 229.312 C-156.458 218.637 -143.708 217.267 -128.188 216.75 C-126.979 216.707 -125.771 216.664 -124.525 216.619 C-121.559 216.514 -118.592 216.411 -115.625 216.312 C-113.599 211.122 -111.587 205.926 -109.589 200.724 C-108.907 198.955 -108.221 197.188 -107.531 195.422 C-106.54 192.884 -105.564 190.341 -104.59 187.797 C-104.278 187.008 -103.967 186.22 -103.646 185.407 C-103.366 184.666 -103.085 183.926 -102.797 183.163 C-102.546 182.515 -102.295 181.866 -102.036 181.198 C-101.525 178.855 -101.869 177.581 -102.625 175.312 C-104.616 173.036 -106.619 170.92 -108.765 168.8 C-109.406 168.155 -110.047 167.511 -110.707 166.847 C-112.827 164.719 -114.958 162.601 -117.09 160.484 C-118.564 159.011 -120.037 157.537 -121.51 156.062 C-124.599 152.974 -127.692 149.892 -130.789 146.812 C-134.767 142.857 -138.733 138.891 -142.697 134.921 C-145.737 131.877 -148.783 128.838 -151.83 125.801 C-153.295 124.34 -154.759 122.877 -156.221 121.413 C-158.261 119.373 -160.307 117.339 -162.355 115.308 C-162.964 114.697 -163.572 114.086 -164.199 113.457 C-167.651 109.293 -167.651 109.293 -171.93 110.465 Z M-44.625 188.312 C-43.809 189.244 -42.992 190.175 -42.151 191.134 C-40.262 193.313 -38.488 195.502 -36.795 197.838 C-30.799 206.085 -23.855 215.176 -13.39 217.426 C-2.689 219.069 8.286 218.339 19.048 217.754 C23.491 217.537 27.937 217.443 32.385 217.338 C41.055 217.115 49.713 216.759 58.375 216.312 C56.849 213.096 55.277 211.025 52.688 208.562 C49.95 205.894 47.322 203.197 44.812 200.312 C44.263 199.698 43.714 199.084 43.148 198.451 C41.375 196.312 41.375 196.312 40.14 194.199 C38.436 191.577 36.994 190.055 34.375 188.312 C28.385 187.174 22.485 187.38 16.41 187.531 C14.649 187.546 12.888 187.558 11.127 187.566 C6.508 187.596 1.891 187.674 -2.727 187.763 C-7.446 187.845 -12.165 187.882 -16.885 187.922 C-26.132 188.007 -35.379 188.144 -44.625 188.312 Z M-77.625 201.312 C-79.275 206.262 -80.925 211.213 -82.625 216.312 C-76.025 216.312 -69.425 216.312 -62.625 216.312 C-67.647 210.436 -67.647 210.436 -72.938 204.875 C-73.614 204.196 -74.291 203.516 -74.988 202.816 C-76.377 201.2 -76.377 201.2 -77.625 201.312 Z M-149.625 254.312 C-150.527 257.019 -150.747 258.551 -150.739 261.325 C-150.738 262.126 -150.738 262.926 -150.738 263.751 C-150.733 264.61 -150.728 265.47 -150.723 266.355 C-150.721 267.239 -150.72 268.123 -150.718 269.033 C-150.713 271.855 -150.7 274.678 -150.688 277.5 C-150.682 279.413 -150.678 281.327 -150.674 283.24 C-150.664 287.931 -150.644 292.622 -150.625 297.312 C-144.685 297.312 -138.745 297.312 -132.625 297.312 C-131.759 295.642 -130.892 293.971 -130 292.25 C-123.844 281.057 -115.94 274.148 -103.625 270.312 C-92.16 267.446 -80.541 269.306 -70.086 274.598 C-60.848 280.296 -55.849 287.909 -50.625 297.312 C-27.855 297.312 -5.085 297.312 18.375 297.312 C16.395 295.002 14.415 292.692 12.375 290.312 C11.36 288.763 10.386 287.184 9.476 285.571 C0.094 269.835 -12.599 253.355 -30.625 247.312 C-48.992 242.996 -68.714 243.964 -87.438 244.438 C-88.692 244.468 -89.947 244.498 -91.24 244.529 C-104.045 244.862 -116.84 245.41 -129.62 246.29 C-130.551 246.346 -131.481 246.403 -132.439 246.461 C-139.546 247.008 -144.873 248.68 -149.625 254.312 Z M14.375 247.312 C17.594 251.117 20.814 254.875 24.329 258.412 C25.043 259.132 25.757 259.852 26.492 260.594 C27.242 261.346 27.993 262.097 28.766 262.871 C29.551 263.66 30.337 264.449 31.147 265.262 C32.796 266.917 34.447 268.57 36.099 270.221 C38.636 272.761 41.166 275.309 43.695 277.857 C45.302 279.467 46.908 281.075 48.516 282.684 C49.274 283.449 50.032 284.215 50.814 285.003 C51.515 285.701 52.216 286.4 52.938 287.119 C53.555 287.737 54.172 288.356 54.808 288.993 C56.289 290.527 56.289 290.527 58.375 290.312 C59.449 289 60.459 287.635 61.438 286.25 C68.551 276.902 77.73 271.2 89.375 269.312 C101.271 268.327 112.541 270.405 122.312 277.555 C129.019 283.27 133.244 289.094 136.375 297.312 C141.325 297.312 146.275 297.312 151.375 297.312 C151.449 291.707 151.504 286.103 151.54 280.497 C151.555 278.591 151.575 276.685 151.601 274.78 C151.638 272.037 151.655 269.294 151.668 266.551 C151.683 265.702 151.699 264.852 151.715 263.978 C151.716 259.244 151.435 256.166 148.375 252.312 C141.13 246.385 130.162 247.026 121.316 247.085 C120.154 247.085 118.992 247.085 117.794 247.085 C113.985 247.086 110.176 247.101 106.367 247.117 C103.713 247.121 101.06 247.124 98.406 247.126 C92.155 247.132 85.905 247.148 79.654 247.168 C72.532 247.191 65.41 247.202 58.288 247.212 C43.65 247.233 29.013 247.268 14.375 247.312 Z M-103.625 306.062 C-104.886 310.16 -104.874 313.218 -103.625 317.312 C-101.477 320.719 -99.512 323.091 -95.625 324.312 C-90.589 324.77 -87.686 324.358 -83.625 321.312 C-80.38 318.531 -79.718 316.295 -79.312 312 C-79.689 307.554 -80.723 305.673 -83.625 302.312 C-91.252 297.444 -98.262 298.688 -103.625 306.062 Z M85.562 303.938 C83.716 307.631 83.139 311.192 83.375 315.312 C85.502 319.729 87.832 322.411 92.375 324.312 C96.635 324.855 98.796 324.613 102.625 322.625 C106.595 319.287 108.153 317.055 108.812 311.875 C108.284 307.57 107.202 305.585 104.375 302.312 C97.431 297.88 91.786 298.358 85.562 303.938 Z " fill="#696969" transform="translate(255.625,-0.3125)"/>
                                                    </svg>
                                                    <span class="text-xs text-gray-600 dark:text-gray-300">{{ $car->coding  ? 'Coding every '.ucfirst(strtolower($car->coding)) : 'No Coding' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
                <div class="flex justify-between mt-8">
                    <button wire:click="back" 
                        class="px-6 py-2 text-gray-700 transition bg-gray-200 rounded-lg dark:text-white hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                    Back
                    </button>
                    <button wire:click="thirdStepSubmit" 
                        onclick="window.scrollTo({ top: 0, behavior: 'smooth' })"
                        class="px-6 py-2 font-medium text-white transition bg-blue-600 rounded-lg next-button hover:bg-blue-700">
                    Next
                    </button>
                </div>
            </div>
            <!-- Step 4 -->
            <div class="{{ $currentStep === 4 ? 'block' : 'hidden' }}" 
                x-data="{ showPolicy: false, agreeToPrivacy: @entangle('agreeToPrivacy').live }">

                <h2 class="mb-2 text-xl font-bold dark:text-white">Step 4: Requirements</h2>
                <p class="mb-6 text-sm text-gray-600 dark:text-gray-400">
                    Upload all required renter documents below...
                </p>

                {{ $form }}

                <!-- Checkbox -->
                <div class="mt-6">
                    <label class="flex items-start space-x-2">
                        <input
                            type="checkbox"
                            value="1"
                            x-model="agreeToPrivacy"
                            class="mt-1 text-[var(--tw-primary)] border-gray-300 rounded focus:ring-[var(--tw-primary)]"
                        />
                        <span class="text-sm text-gray-700 dark:text-gray-300">
                            I agree to the
                            <button type="button" @click="showPolicy = true" class="text-[var(--tw-primary)] hover:underline">
                                Privacy Policy
                            </button>.
                        </span>
                    </label>
                    @error('agreeToPrivacy')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Modal -->
                <div x-show="showPolicy" x-transition
                    class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm">
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

                <!-- Buttons -->
                <div class="flex justify-between mt-8">
                    <button wire:click="back"
                            class="px-6 py-2 text-gray-700 transition bg-gray-200 rounded-lg hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                        Back
                    </button>

                    <button wire:click="submitForm" wire:loading.attr="disabled" 
                            :disabled="!agreeToPrivacy"
                            class="px-6 py-2 font-medium text-white transition bg-green-600 rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span wire:loading.remove>Submit Booking</span>
                        <span wire:loading> Please wait...</span>
                    </button>
                </div>
            </div>


        </div>
    </div>
    <div id="qr-temp-reader" style="display: none"></div>
    <script src="https://unpkg.com/html5-qrcode"></script>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
        
             const startDateInput = document.getElementById("startDate");
             const startTimeSelect = document.getElementById("startTime");
             const endDateInput   = document.getElementById("endDate");
             const endTimeSelect  = document.getElementById("endTime");
        
             startDateInput.addEventListener("changeDate", function(e) {
                @this.set('start_date', startDateInput.value); 

                if (!endDateInput.value || new Date(endDateInput.value) < new Date(startDateInput.value)) {
                    endDateInput.value = startDateInput.value;
                    @this.set('end_date', startDateInput.value);

                    if (endDateInput.datepicker) {
                        endDateInput.datepicker.setDate(new Date(startDateInput.value));
                    }
                }
            });
        
             endDateInput.addEventListener("changeDate", function(e) {
                 @this.set('end_date', endDateInput.value); 
             });
             
         });

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