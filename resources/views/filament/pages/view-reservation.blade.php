<x-filament::page>
    @php
        $start = $this->record->start_date;
        $end = $this->record->end_date;
        $duration = '-';

        if ($start && $end) {
            $startCarbon = \Carbon\Carbon::parse($start);
            $endCarbon = \Carbon\Carbon::parse($end);
            $diffInHours = $startCarbon->diffInHours($endCarbon);

            if ($diffInHours < 24) {
                $duration = '1 Day';
            } else {
                $days = floor($diffInHours / 24);
                $extendHours = $diffInHours % 24;
                $duration = $days . ' Day' . ($days > 1 ? 's' : '');
                if ($extendHours > 0) {
                    $duration .= " + {$extendHours} Hr" . ($extendHours > 1 ? 's' : '');
                }
            }
        }
    @endphp

    <div class="space-y-8">
        {{-- Executive Header --}}
        <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between border-b border-gray-200 dark:border-gray-800 pb-8">
            <div>
             
                <h1 class="text-4xl font-extrabold text-gray-900 dark:text-white leading-none">
                    #{{ $this->record->reservation_number }}
                </h1>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Created on {{ $this->record->created_at->format('F d, Y • h:i A') }}</p>
            </div>
            
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-bold  shadow-sm border
                    @if($this->record->status === 'approved') bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20
                    @elseif($this->record->status === 'pending') bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/20
                    @elseif($this->record->status === 'declined') bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-500/10 dark:text-rose-400 dark:border-rose-500/20
                    @endif
                ">
                    {{ strtoupper($this->record->status) }}
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            {{-- Primary Details Column --}}
            <div class="lg:col-span-2 space-y-8">
                
                {{-- Customer Profile --}}
                <section class="bg-white dark:bg-gray-900 rounded-xl  overflow-hidden shadow-md">
                    <div class="px-6 py-4 bg-gray-50/50 dark:bg-transparent border-b border-gray-200/50 dark:border-transparent">
                        <h2 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <x-heroicon-s-user class="w-4 h-4 text-gray-400" />
                            Customer Profile
                        </h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-x-12 gap-y-6">
                            <div class="space-y-1">
                                <span class="text-sm font-bold text-gray-400 ">Renter Name</span>
                                <p class="text-base font-semibold text-gray-900 dark:text-white">{{ $this->record->customer->customer_name }}</p>
                            </div>
                            <div class="space-y-1">
                                <span class="text-sm font-bold text-gray-400 ">Contact Number</span>
                                <p class="text-base font-semibold text-gray-900 dark:text-white">{{ $this->record->customer->contact_number }}</p>
                            </div>
                            <div class="space-y-1">
                                <span class="text-sm font-bold text-gray-400 ">Email</span>
                                <p class="text-base font-semibold text-gray-900 dark:text-white">{{ $this->record->customer->email }}</p>
                            </div>
                            <div class="md:col-span-3 space-y-1 border-t border-gray-200 dark:border-white/10 pt-4">
                                <span class="text-sm font-bold text-gray-400 ">Billing Address</span>
                                <p class="text-sm text-gray-600 dark:text-gray-300">{{ $this->record->customer->address }}</p>
                            </div>
                            <div class="md:col-span-3 space-y-1 border-t border-gray-200 dark:border-white/10 pt-2">
                                <span class="text-sm font-bold text-gray-400 ">Facebook Profile</span><br/>
                                <a href="{{ $this->record->customer->facebook_name }}" target="_blank" class="text-sm text-gray-600 dark:text-gray-300">{{ $this->record->customer->facebook_name }}</a>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- Vehicle Assignment --}}
                <section class="bg-white dark:bg-gray-900 rounded-xl  overflow-hidden shadow-md">
                    <div class="px-6 py-4 bg-gray-50/50 dark:bg-transparent border-b border-gray-200/50 dark:border-transparent">
                        <h2 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <x-heroicon-s-truck class="w-5 h-5 text-gray-400" />
                            Vehicle Details
                        </h2>
                    </div>
                    <div class="p-6 flex flex-col md:flex-row gap-8 items-start">
                        <div class="w-full md:w-48 flex-shrink-0">
                            @if($this->record->car && $this->record->car->image)
                                    <img src="{{ Storage::url($this->record->car->image) }}" class="w-full h-auto object-contain rounded shadow-sm">
                            @endif
                        </div>
                        <div class="flex-1 grid grid-cols-2 gap-6">
                            <div>
                                <span class="text-sm font-bold text-gray-400 ">Car Model</span>
                                <p class="text-base font-bold text-gray-900 dark:text-white">{{ $this->record->car->name }}</p>
                                <p class="text-sm text-gray-500">{{ $this->record->car->brand }} • {{ $this->record->car->year }}</p>
                            </div>
                            <div>
                                <span class="text-sm font-bold text-gray-400 ">Identification</span>
                                <p class="text-base font-mono font-bold text-primary-600 ">{{ $this->record->car->plate_number ?? 'No Plate' }}</p>
                                <p class="text-sm text-gray-500">{{ $this->record->car->color }}</p>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- Reservation Fee Section - Grid Row below Vehicle Details --}}
                @if($this->record->reservation_fee > 0 || $this->record->fund_type_id)
                    <section class="bg-white dark:bg-gray-900 rounded-xl overflow-hidden shadow-md border border-gray-200 dark:border-gray-700">
                        <div class="px-6 py-4 bg-gray-50/50 dark:bg-transparent border-b border-gray-200/50 dark:border-transparent">
                            <h2 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                <x-heroicon-s-credit-card class="w-5 h-5 text-[var(--tw-primary)]" />
                                Reservation Fee
                            </h2>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                {{-- Account/Fund Type --}}
                                <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Payment Method</span>
                                    <p class="mt-1 text-base font-semibold text-gray-900 dark:text-white">
                                        {{ $this->record->fundType->name ?? 'N/A' }}
                                    </p>
                                    @if($this->record->fundType)
                                        <div class="mt-2 space-y-1 text-sm text-gray-600 dark:text-gray-400">
                                            @if($this->record->fundType->account_name)
                                                <p><span class="font-medium">Account:</span> {{ $this->record->fundType->account_name }}</p>
                                            @endif
                                            @if($this->record->fundType->account_number)
                                                <p><span class="font-medium">Number:</span> <span class="font-mono">{{ $this->record->fundType->account_number }}</span></p>
                                            @endif
                                        </div>
                                    @endif
                                </div>

                                {{-- Amount --}}
                                <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Amount Paid</span>
                                    <p class="mt-1 text-2xl font-black text-[var(--tw-primary)]">
                                        ₱{{ number_format($this->record->reservation_fee, 2) }}
                                    </p>
                                </div>

                                {{-- View Receipt --}}
                                <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg flex flex-col justify-center">
                                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Payment Receipt</span>
                                    @if($this->record->reservation_fee_receipt)
                                        <div class="mt-2">
                                            <button 
                                                x-data="{}"
                                                @click="$dispatch('open-receipt-modal', { 
                                                    url: '{{ Storage::disk('s3')->temporaryUrl($this->record->reservation_fee_receipt, now()->addMinutes(15)) }}' 
                                                })"
                                                class="inline-flex items-center gap-2 px-4 py-2.5 bg-[var(--tw-primary)]/10 text-[var(--tw-primary)] rounded-lg hover:bg-[var(--tw-primary)]/20 transition-colors text-sm font-medium">
                                                <x-heroicon-s-eye class="w-4 h-4" />
                                                View Receipt
                                            </button>
                                        </div>
                                    @else
                                        <p class="mt-2 text-sm text-gray-400">No receipt uploaded</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </section>
                @endif
            </div>

            {{-- Sidebar Details --}}
            <div class="space-y-8">
                {{-- Logistics Card --}}
                <section class="bg-white dark:bg-gray-900 rounded-2xl p-6 text-white shadow-xl relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-4 opacity-10">
                        <x-heroicon-o-calendar class="w-20 h-20 text-gray-500 dark:text-white" />
                    </div>
                    
                    <h3 class="text-sm font-bold text-primary-400  mb-6">Itinerary Summary</h3>
                    
                    <div class="space-y-6">
                        <div class="relative pl-6 border-l border-white/20">
                            <div class="absolute -left-[5px] top-1 w-2 h-2 rounded-full bg-primary-500 shadow-[0_0_10px_rgba(59,130,246,0.5)]"></div>
                            <p class="text-sm font-bold text-gray-400  mb-1">Pickup</p>
                            <p class="text-sm text-gray-400 mt-1 flex items-center gap-1">
                                <x-heroicon-m-calendar class="w-5 h-5" />
                                {{ $this->record->start_date?->format('M d, Y • h:i A') }}
                            </p>
                            <p class="text-sm text-gray-400 mt-1 flex items-center gap-1">
                                <x-heroicon-m-map-pin class="w-5 h-5" />
                                {{ $this->record->pickup_address ?? 'Office Garage' }}
                            </p>
                        </div>

                        <div class="relative pl-6 border-l border-white/20">
                            <div class="absolute -left-[5px] top-1 w-2 h-2 rounded-full bg-rose-500 shadow-[0_0_10px_rgba(244,63,94,0.5)]"></div>
                            <p class="text-sm font-bold text-gray-400  mb-1">Return</p>
                            <p class="text-sm text-gray-400 mt-1 flex items-center gap-1">
                                <x-heroicon-m-calendar class="w-5 h-5" />
                                {{ $this->record->end_date?->format('M d, Y • h:i A') }}
                            </p>
                            <p class="text-sm text-gray-400 mt-1 flex items-center gap-1">
                                <x-heroicon-m-map-pin class="w-5 h-5" />
                                {{ $this->record->return_address ?? 'Office Garage' }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-8 pt-6 border-t border-gray-200 dark:border-white/10 flex justify-between items-center">
                        <span class="text-sm font-bold text-gray-400 ">Destination</span>
                        <span class="text-sm font-bold text-gray-700 dark:text-gray-300">{{ $this->record->destination ?? '-' }}</span>
                    </div>
                    <div class=" flex justify-between items-center">
                        <span class="text-sm font-bold text-gray-400 ">Duration</span>
                        <span class="text-lg font-black text-gray-500 dark:text-white">{{ $duration }}</span>
                    </div>
                </section>

                {{-- Quick Specs --}}
                <section class="bg-white dark:bg-gray-900 rounded-xl  p-5 space-y-4 shadow-md">
                  
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-bold text-gray-400 ">Chauffeur</span>
                        <span class="text-sm font-bold px-2 py-1 rounded bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300">
                            {{ $this->record->with_driver ? 'Included' : 'Self-Drive' }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-bold text-gray-400 ">Booking Source</span>
                        <span class="text-sm font-bold text-gray-700 dark:text-gray-300">{{ $this->record->source?->source ?? 'Standard' }}</span>
                    </div>
                    <div class="pt-2 border-t border-gray-50 dark:border-gray-800">
                        <span class="text-sm font-bold text-gray-400 ">Additional Drivers</span>
                        <p class="text-sm mt-1 text-gray-600 dark:text-gray-400 font-medium">{{ $this->record->other_drivers ?? 'None' }}</p>
                    </div>
                </section>
            </div>
        </div>

        {{-- Verification Section --}}
        <div class="bg-white dark:bg-gray-900 rounded-xl  overflow-hidden shadow-md">
            <div class="px-6 py-4 bg-gray-50/50 dark:bg-transparent border-b border-gray-200/50 dark:border-transparent flex justify-between items-center">
                <h2 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <x-heroicon-s-shield-check class="w-4 h-4 text-emerald-500" />
                    Verification Documents
                </h2>
                <span class="text-sm font-bold text-gray-400 ">Required for release</span>
            </div>
            
            <div class="p-6">
                @if($this->record->customer->requirements?->isEmpty())
                    <div class="flex flex-col items-center justify-center py-10 border-2 border-dashed border-gray-100 dark:border-gray-800 rounded-xl">
                        <p class="text-sm text-gray-400 italic font-medium">No documentation has been uploaded yet.</p>
                    </div>
                @else
                    <div x-data="{ showModal: false, imageUrl: '' }" class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-4">
                        @foreach($this->record->customer->requirements as $requirement)
                            @php
                                $url = Storage::disk('s3')->temporaryUrl($requirement->path, now()->addMinutes(15));
                                $extension = pathinfo($requirement->path, PATHINFO_EXTENSION);
                                $isImage = in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'webp']);
                            @endphp

                            <div class="group relative aspect-square bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden transition-all hover:ring-2 hover:ring-primary-500/50 cursor-pointer"
                                 @click="imageUrl='{{ $url }}'; showModal = true">
                                @if($isImage)
                                    <img src="{{ $url }}" class="w-full h-full object-cover transition duration-300 group-hover:scale-110">
                                @else
                                    <div class="w-full h-full flex flex-col items-center justify-center">
                                        <x-heroicon-o-document class="w-6 h-6 text-gray-300" />
                                        <span class="text-sm font-bold text-gray-400 mt-1 ">{{ $extension }}</span>
                                    </div>
                                @endif
                                <div class="absolute inset-x-0 bottom-0 bg-white/90 dark:bg-gray-900/90 p-2 backdrop-blur-sm">
                                    <p class="text-[9px] font-bold text-gray-700 dark:text-gray-300 truncate ">
                                        {{ $requirement->requirementType->label }}
                                    </p>
                                </div>
                            </div>
                        @endforeach

                        {{-- Professional Modal --}}
                        <div x-show="showModal" 
                             class="fixed inset-0 z-[100] flex items-center justify-center bg-gray-950/95 backdrop-blur-md p-6"
                             x-transition.opacity @click="showModal = false" x-cloak x-on:keydown.escape.window="showModal = false">
                            <img :src="imageUrl" class="max-w-full max-h-full rounded shadow-2xl ring-1 ring-white/10" @click.stop>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Receipt Modal --}}
    <div x-data="{ 
        showReceipt: false, 
        receiptUrl: '',
        downloadReceipt() {
            if (!this.receiptUrl) return;
            
            // Fetch the image and download it
            fetch(this.receiptUrl)
                .then(response => response.blob())
                .then(blob => {
                    const link = document.createElement('a');
                    link.href = URL.createObjectURL(blob);
                    link.download = 'receipt-{{ $this->record->reservation_number }}.jpg';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    URL.revokeObjectURL(link.href);
                })
                .catch(() => {
                    // Fallback: open in new tab if fetch fails
                    window.open(this.receiptUrl, '_blank');
                });
        }
    }"
    x-on:open-receipt-modal.window="
        receiptUrl = $event.detail.url;
        showReceipt = true;
    "
    x-on:keydown.escape.window="showReceipt = false">
    
    <div x-show="showReceipt" 
         class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-black/90 backdrop-blur-md"
         x-transition.opacity
         x-cloak>
        
        {{-- Close button --}}
        <button @click="showReceipt = false" 
                class="absolute top-6 right-6 text-white/70 hover:text-white transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
        
        {{-- Modal content --}}
        <div class="relative max-w-4xl max-h-[90vh] w-full" @click.away="showReceipt = false">
            <div class="bg-white dark:bg-gray-900 rounded-2xl overflow-hidden shadow-2xl">
                {{-- Header --}}
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center gap-3">
                        <x-heroicon-s-document-text class="w-5 h-5 text-[var(--tw-primary)]" />
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Payment Receipt</h3>
                    </div>
                    <button @click="showReceipt = false" 
                            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                {{-- Image/Content --}}
                <div class="p-6 flex items-center justify-center min-h-[300px] bg-gray-50 dark:bg-gray-800">
                    <template x-if="receiptUrl">
                        <img :src="receiptUrl" 
                             alt="Payment Receipt" 
                             class="max-w-full max-h-[70vh] object-contain rounded-lg shadow-lg">
                    </template>
                    <template x-if="!receiptUrl">
                        <div class="flex flex-col items-center justify-center text-gray-400">
                            <svg class="w-16 h-16 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span class="text-sm">No receipt available</span>
                        </div>
                    </template>
                </div>
                
                {{-- Footer with actions --}}
                <div class="flex items-center justify-between px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                    <span class="text-xs text-gray-500 dark:text-gray-400">
                        Receipt uploaded on {{ $this->record->updated_at->format('F d, Y • h:i A') }}
                    </span>
                    <div class="flex gap-3">
                        <button @click="showReceipt = false" 
                                class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors">
                            Close
                        </button>
                        <button @click="downloadReceipt" 
                                class="px-4 py-2 text-sm font-medium text-white bg-[var(--tw-primary)] hover:opacity-90 rounded-lg transition-colors flex items-center gap-2">
                            <x-heroicon-s-arrow-down-tray class="w-4 h-4" />
                            Download
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

    <style>
        [x-cloak] { display: none !important; }
    </style>
</x-filament::page>