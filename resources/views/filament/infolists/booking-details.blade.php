@php
    $record = $getRecord();
    
    // Financial Calculation
    $totalAmount = ($record->days_rented * $record->daily_rate) 
                 + ($record->extend_fee ?? 0) 
                 + ($record->delivery_fee ?? 0) 
                 + ($record->driver_fee ?? 0) 
                 + ($record->fuel_charge ?? 0)
                 + ($record->out_of_bounds ?? 0)
                 + ($record->rfid ?? 0)
                 + ($record->damages ?? 0)
                 + ($record->carwash_fee ?? 0)
                 + ($record->insurance ?? 0)
                 - ($record->discount ?? 0);

    $extras = [
        'Fuel' => $record->fuel_charge,
        'Out of Bounds' => $record->out_of_bounds,
        'RFID' => $record->rfid,
        'Damage' => $record->damages,
        'Car Wash' => $record->carwash_fee, 
        'Insurance' => $record->insurance,
    ];
@endphp

<div class="space-y-6">
    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 items-stretch">
        {{-- Vehicle Identity Card --}}
        <div class="xl:col-span-3 relative group overflow-hidden bg-transparent dark:gray-900 rounded-3xl shadow-2xl transition-all duration-500">
            {{-- Decorative Gradient --}}
            <div class="absolute inset-0 bg-gradient-to-br from-primary-600/20 via-transparent to-black/60 pointer-events-none"></div>
            
            <div class="relative p-6 h-full flex flex-col justify-between z-10">
                <div class="flex justify-between items-start">
                    <div>
                        <span class="text-sm font-bold uppercase  text-primary-400">Assigned Fleet</span>
                        <h4 class="text-xl font-bold text-primary dark:text-white font-bold uppercase er mt-1">
                            {{ $record->car?->brand }} <span class="text-primary-500">{{ $record->car?->model }}</span>
                        </h4>
                    </div>
                    <div class="flex flex-col items-end">
                        <span class="px-2 py-1 rounded-md bg-white/10 backdrop-blur-md text-primary dark:text-white text-sm font-bold ring-1 ring-white/20">
                            {{ $record->car?->plate_number }}
                        </span>
                    </div>
                </div>

                <div class="py-8 flex justify-center">
                    @if($record->car?->image)
                        <img src="{{ asset('storage/' . $record->car->image) }}" 
                             class="w-full h-24 object-contain dark:drop-shadow-[0_20px_30px_rgba(0,0,0,0.8)] group-hover:scale-110 transition-transform duration-700" 
                             alt="Car">
                    @else
                        <x-heroicon-o-camera class="w-16 h-16 text-white/10" />
                    @endif
                </div>

                <div class="flex items-center justify-between border-t border-white/10 pt-4">
                    <span class="text-sm font-bold text-gray-400">{{ $record->car?->year }} • {{ $record->car?->color }}</span>
                    <div class="flex items-center gap-1.5">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-success-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-success-500"></span>
                        </span>
                        <span class="text-sm font-bold text-gray-400 uppercase ">Active</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main Details Panel --}}
        <div class="xl:col-span-9 bg-white dark:bg-gray-900 ring-1  rounded-3xl shadow-md overflow-hidden">
            {{-- Top: Customer Bar --}}
            <div class="p-8 border-b border-gray-100 dark:border-white/5">
                <div class="flex flex-wrap items-center gap-y-6">
                    <div class="flex items-center gap-4 w-full md:w-1/3">
                        <div class="relative">
                            <div class="p-3 bg-gray-100 dark:bg-white/5 rounded-2xl text-gray-400">
                                <x-heroicon-s-user class="w-6 h-6" />
                            </div>
                            <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-green-500 border-2 border-white dark:border-gray-900 rounded-full"></div>
                        </div>
                        <div>
                            <label class="text-sm font-bold uppercase  text-gray-400 block mb-0.5">Renter / Client</label>
                            <p class="text-base font-bold text-gray-700 dark:text-white ">{{ $record->renter_name }}</p>
                            <p class="text-sm font-medium text-gray-500 mt-1"> {{ $record->contact_number }}</p>
                        </div>
                    </div>

                    <div class="w-full md:w-2/3 md:pl-8 md:border-l border-gray-100 dark:border-white/5">
                        <label class="text-sm font-bold uppercase  text-gray-400 block mb-1">Address</label>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400  ">
                            {{ $record->renter_address }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Bottom: Logistics Timeline --}}
            <div class="p-8 grid grid-cols-1 md:grid-cols-3 gap-12 relative">
                {{-- Pickup --}}
                <div class="relative pl-3">
                    
                    <label class="text-sm font-bold uppercase  text-primary-500 dark:text-white block mb-3">Departure / Pickup</label>
                    <div class="space-y-3">
                        <div class="flex items-center gap-3">
                            <x-heroicon-o-calendar-days class="w-5 h-5 text-gray-400" />
                            <span class="text-sm font-bold text-gray-700 dark:text-white">
                                {{ $record->start_datetime ? \Carbon\Carbon::parse($record->start_datetime)->format('M d, Y • h:i A') : '---' }}
                            </span>
                        </div>
                        <div class="flex items-start gap-3">
                            <x-heroicon-o-map-pin class="w-5 h-5 text-gray-400 shrink-0" />
                            <span class="text-sm font-bold text-gray-500   ">
                                {{ $record->delivery_address ?: 'Garage Headquarters' }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Return --}}
                <div class="relative pl-3">
                    
                    <label class="text-sm font-bold uppercase  text-red-500 block mb-3">Arrival / Return</label>
                    <div class="space-y-3">
                        <div class="flex items-center gap-3">
                            <x-heroicon-o-arrow-path-rounded-square class="w-5 h-5 text-gray-400" />
                            <span class="text-sm font-bold text-gray-700 dark:text-white">
                                {{ $record->end_datetime ? \Carbon\Carbon::parse($record->end_datetime)->format('M d, Y • h:i A') : '---' }}
                            </span>
                        </div>
                        <div class="flex items-start gap-3">
                            <x-heroicon-o-map-pin class="w-5 h-5 text-gray-400 shrink-0" />
                            <span class="text-sm font-bold text-gray-500   ">
                                {{ $record->return_address ?: 'Garage Headquarters' }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Trip Stats --}}
                <div class="relative">
                    <label class="text-sm font-bold uppercase  text-gray-400 dark:text-white block mb-3">Trip Overview</label>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-bold text-gray-500">Duration</span>
                            <span class="px-2 py-1 bg-primary-500 text-white text-sm font-bold rounded-lg">
                                {{ \Carbon\Carbon::parse($record->start_datetime)->diffInDays(\Carbon\Carbon::parse($record->end_datetime)) }} DAYS
                            </span>
                        </div>
                        <div class="flex items-center ">
                            <span class="text-sm font-bold text-gray-700 dark:text-white  ">
                                <small>Destination:</small> {{ $record->destination ?: 'Within Metro' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Financial Ledger --}}
    <div class="w-full bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 rounded-3xl shadow-xl overflow-hidden">
        <div class="p-8 flex flex-col md:flex-row justify-between items-center bg-gray-50/50 dark:bg-gray-800 gap-6">
            <div class="flex items-center gap-4">
                <div class="p-4 bg-white dark:bg-gray-800 rounded-2xl shadow-sm ring-1 ring-gray-200 dark:ring-white/10">
                    <x-heroicon-s-banknotes class="w-8 h-8 text-primary-500" />
                </div>
                <div>
                    <h3 class="text-xl font-bold text-gray-700 dark:text-white er uppercase">Financial Ledger</h3>
                    <p class="text-sm font-bold text-gray-400 uppercase ">Billing & Adjustments</p>
                </div>
            </div>

            <div class="text-center md:text-right">
                <span class="text-sm font-bold text-primary-500 uppercase  block mb-1">Grand Statement Total</span>
                <span class="text-4xl font-bold text-gray-700 dark:text-white er">
                    ₱{{ number_format($totalAmount, 2) }}
                </span>
            </div>
        </div>

        <div class="p-8">
            {{-- Primary Calculations --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-8 pb-8 border-b border-gray-100 dark:border-white/5 mb-8">
                @foreach([
                    ['Standard Rate', '₱' . number_format($record->daily_rate, 2), $record->days_rented . ' Rental Days'],
                    ['Overtime/Ext', '₱' . number_format($record->extend_fee ?? 0, 2), ($record->extend_hours ?? 0) . ' Excess Hours'],
                    ['Logistics Fee', '₱' . number_format(($record->driver_fee ?? 0) + ($record->delivery_fee ?? 0), 2), 'Driver & Drop-off'],
                    ['Incentives', '-₱' . number_format($record->discount ?? 0, 2), 'Promo Discount', 'text-danger-500'],
                ] as $fee)
                <div>
                    <label class="text-sm uppercase  text-gray-400 block mb-2">{{ $fee[0] }}</label>
                    <p class="text-2xl font-bold {{ $fee[3] ?? 'text-gray-700 dark:text-white' }} ">{{ $fee[1] }}</p>
                    <span class="text-xs  text-gray-400 uppercase er italic">{{ $fee[2] }}</span>
                </div>
                @endforeach
            </div>

            {{-- Surcharge Grid --}}
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                @foreach($extras as $label => $val)
                <div class="group p-4 rounded-2xl bg-gray-50/50 dark:bg-gray-800 border border-gray-100 dark:border-white/5 hover:border-primary-500/50 transition-all duration-300">
                    <span class="text-sm text-gray-400 block mb-2 uppercase  group-hover:text-primary-500">{{ $label }}</span>
                    <span class="text-sm font-bold text-gray-700 dark:text-white">₱{{ number_format($val ?? 0, 2) }}</span>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Deposit Secure Bar --}}
        <div class="px-8 py-5 bg-amber-500/5 dark:bg-amber-500/10 border-t border-amber-500/20 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-amber-500/20 rounded-lg text-amber-600">
                    <x-heroicon-s-shield-check class="w-5 h-5" />
                </div>
                <span class="text-sm text-amber-700 dark:text-amber-500  ">Security Deposit Held (Refundable)</span>
            </div>
            <span class="text-xl font-bold text-amber-700 dark:text-amber-500 er">₱{{ number_format($record->security_deposit ?? 0, 2) }}</span>
        </div>
    </div>
</div>