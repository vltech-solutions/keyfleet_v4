@props([
    'filterOpen' => false,
    'brands' => [],
    'types' => [],
    'transmissions' => [], 
    'selectedBrand' => 'All',
    'selectedType' => 'All',
    'selectedTransmission' => 'All'
])

<div 
    x-show="filterOpen" 
    x-cloak
    class="fixed inset-0 z-[60] overflow-hidden" 
    style="display: none;"
>
    {{-- Backdrop --}}
    <div 
        x-show="filterOpen" 
        x-transition:opacity 
        class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm" 
        @click="filterOpen = false"
    ></div>
    
    <div class="fixed inset-y-0 left-0 flex max-w-full pr-10">
        <div 
            x-show="filterOpen" 
            x-transition:enter="transition ease-out duration-300 transform" 
            x-transition:enter-start="-translate-x-full" 
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-300 transform" 
            x-transition:leave-start="translate-x-0" 
            x-transition:leave-end="-translate-x-full"
            class="w-screen max-w-xs md:max-w-sm bg-white shadow-2xl flex flex-col h-full"
        >
            {{-- Header --}}
            <div class="px-6 py-4 border-b flex items-center justify-between">
                <h2 class="text-xl font-bold text-gray-900">Filter Options</h2>
                <button @click="filterOpen = false" class="text-gray-400 hover:text-gray-900 transition-colors">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Body --}}
            <div class="flex-1 overflow-y-auto p-6 space-y-8">
                {{-- Brand Section --}}
                <div>
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4">Select Brand</h3>
                    <div class="grid grid-cols-3 gap-3">
                        <button wire:click="selectBrand('All')"
                            class="flex flex-col items-center p-3 rounded-xl border-2 transition-all {{ $selectedBrand === 'All' ? 'border-gray-900 bg-gray-50' : 'border-gray-100 hover:border-gray-200' }}">
                            <div class="w-8 h-8 mb-1 flex items-center justify-center text-gray-400 {{ $selectedBrand === 'All' ? 'text-gray-900' : '' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                            </div>
                            <span class="text-[10px] font-bold">All</span>
                        </button>

                        @foreach($brands as $brand)
                            <button wire:key="brand-{{ $brand }}" wire:click="selectBrand('{{ $brand }}')" type="button"
                                class="flex flex-col items-center p-3 rounded-xl border-2 transition-all {{ strtolower($selectedBrand) === strtolower($brand) ? 'border-gray-900 bg-gray-50 shadow-inner' : 'border-gray-100 hover:border-gray-200' }}">
                                <img src="https://raw.githubusercontent.com/filippofilip95/car-logos-dataset/master/logos/thumb/{{ str_replace(' ', '-', strtolower($brand)) }}.png" 
                                     class="w-8 h-8 object-contain mb-1" 
                                     onerror="this.src='https://ui-avatars.com/api/?name={{ $brand }}&background=f3f4f6&color=a1a1aa&font-size=0.5'">
                                <span class="text-[10px] font-bold truncate w-full text-center">{{ $brand }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Category Section --}}
                <div>
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4">Vehicle Type</h3>
                    <div class="flex flex-wrap gap-2">
                        <button wire:click="selectType('All')"
                            class="px-4 py-2 rounded-full text-xs font-bold border-2 transition-all {{ $selectedType === 'All' ? 'bg-gray-900 text-white border-gray-900' : 'bg-white text-gray-500 border-gray-100 hover:border-gray-200' }}">
                            All Types
                        </button>
                        @foreach($types as $type)
                            <button wire:click="selectType('{{ $type }}')"
                                class="px-4 py-2 rounded-full text-xs font-bold border-2 transition-all {{ strtolower($selectedType) === strtolower($type) ? 'bg-gray-900 text-white border-gray-900' : 'bg-white text-gray-500 border-gray-100 hover:border-gray-200' }}">
                                {{ $type }}
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Transmission Section --}}
                <div class="pt-4">
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4">Transmission</h3>
                    <div class="relative">
                        <select 
                            wire:model.live="selectedTransmission"
                            class="w-full pl-4 pr-10 py-3 bg-gray-50 border-2 border-gray-100 rounded-xl text-sm font-bold text-gray-900 appearance-none focus:border-primary focus:ring-0 transition-all cursor-pointer"
                            style="border-color: {{ strtolower($selectedTransmission) !== 'all' ? 'var(--primary-color)' : '' }}"
                        >
                            <option value="All">All Transmissions</option>
                            @foreach($transmissions as $trans)
                                <option value="{{ $trans }}">{{ $trans }}</option>
                            @endforeach
                        </select>
                        
                        {{-- Custom Arrow --}}
                        <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-gray-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Footer Actions --}}
            <div class="p-6 border-t bg-gray-50 flex gap-3">
                <button wire:click="resetFilters" @click="filterOpen = false" class="flex-1 px-4 py-3 text-sm font-bold text-gray-600 bg-white border border-gray-200 rounded-xl hover:bg-gray-100 transition-colors">
                    Clear
                </button>
                <button wire:click="applyFilter" @click="filterOpen = false" class="flex-1 px-4 py-3 text-sm font-bold text-white bg-gray-900 rounded-xl hover:bg-black transition-all shadow-lg shadow-gray-900/20">
                    Apply
                </button>
            </div>
        </div>
    </div>
</div>