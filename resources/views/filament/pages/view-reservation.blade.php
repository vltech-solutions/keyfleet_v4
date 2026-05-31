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
                    {{-- @if($this->record->other_drivers) --}}
                        <div class="pt-2 border-t border-gray-50 dark:border-gray-800">
                            <span class="text-sm font-bold text-gray-400 ">Additional Drivers</span>
                            <p class="text-sm mt-1 text-gray-600 dark:text-gray-400 font-medium">{{ $this->record->other_drivers ?? 'None' }}</p>
                        </div>
                    {{-- @endif --}}
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

    <style>
        [x-cloak] { display: none !important; }
    </style>
</x-filament::page>