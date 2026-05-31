<div x-data="{ 
        filterOpen: false, 
        scrolled: false,
        darkMode: localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)
    }" 
    x-init="
        window.addEventListener('scroll', () => { scrolled = window.scrollY > 50 });
        $watch('darkMode', val => {
            if (val) {
                document.documentElement.classList.add('dark');
                localStorage.setItem('theme', 'dark');
            } else {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('theme', 'light');
            }
        });
        if (darkMode) document.documentElement.classList.add('dark');
    "
    x-on:filter-applied.window="filterOpen = false"
    style="--primary-color: {{ $primaryColor }};"
    class="antialiased selection:bg-[var(--tw-primary)] selection:text-white bg-white dark:bg-gray-950 transition-colors duration-300"
>
    <style>
        :root { --tw-primary: {{ $primaryColor }}; }
        .hide-scrollbar::-webkit-scrollbar { display: none; }
        .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        [x-cloak] { display: none !important; }
    </style>
    
    <x-booking.navbar :company="$company" :companyLogo="$companyLogo" />

    {{-- <div class="fixed bottom-6 right-6 z-50">
        <button @click="darkMode = !darkMode" 
            class="flex items-center justify-center w-12 h-12 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-full shadow-xl hover:scale-110 transition-all active:scale-95 group">
            <svg x-show="darkMode" x-cloak xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m12.728 0l-.707-.707M6.343 6.343l-.707-.707M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <svg x-show="!darkMode" x-cloak xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
            </svg>
        </button>
    </div> --}}

    <section id="cars" class="py-12 md:py-24 bg-[#F9FAFB] dark:bg-gray-900 transition-colors duration-300">
        <div class="max-w-[1440px] mx-auto px-4 md:px-10">
            
            <div class="flex flex-col md:flex-row md:items-end justify-between mb-12 gap-6">
                <div class="max-w-2xl">
                    <span class="mt-10 md:mt-2 inline-block text-[var(--tw-primary)] font-bold text-xs uppercase mb-4 bg-[var(--tw-primary)]/10 px-3 py-1 rounded">
                        Premium Fleet
                    </span>
                    <h3 class="text-4xl md:text-5xl font-black text-gray-900 dark:text-white leading-tight">
                        Find your <span class="text-[var(--tw-primary)]">perfect</span> journey
                    </h3>
                </div>
                <p class="text-gray-500 dark:text-gray-400 text-lg max-w-xs md:text-right border-l-2 md:border-l-0 md:border-r-2 border-[var(--tw-primary)] pl-4 md:pl-0 md:pr-4">
                    Hand-picked vehicles for business, travel, and style.
                </p>
            </div>

            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6 mb-10 mt-5">
                <div class="flex flex-wrap items-center gap-3 lg:justify-end">
                    <div class="flex flex-wrap items-center gap-2">
                        @if($selectedBrand !== 'All')
                            <button wire:click="selectBrand('All')" class="flex items-center gap-2 px-4 py-2 text-[10px] font-black bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 dark:text-gray-300 rounded-xl hover:border-red-500 transition-all group">
                                <span class="text-gray-400">Brand:</span> {{ $selectedBrand }}
                                <svg class="h-3 w-3 text-gray-400 group-hover:text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        @endif

                        @if($selectedType !== 'All')
                            <button wire:click="selectType('All')" class="flex items-center gap-2 px-4 py-2 text-[10px] font-black bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 dark:text-gray-300 rounded-xl hover:border-red-500 transition-all group">
                                <span class="text-gray-400">Type:</span> {{ $selectedType }}
                                <svg class="h-3 w-3 text-gray-400 group-hover:text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        @endif
                    </div>

                    @if($selectedBrand !== 'All' || $selectedType !== 'All')
                        <div class="h-6 w-px bg-gray-200 dark:bg-gray-700 mx-1 hidden lg:block"></div>
                    @endif

                    <button @click="filterOpen = true" 
                        class="group flex items-center gap-3 px-6 py-3.5 bg-gray-900 dark:bg-[var(--tw-primary)] text-white rounded-2xl shadow-lg hover:shadow-[var(--tw-primary)]/20 transition-all active:scale-95 font-bold text-sm w-full sm:w-auto justify-center">
                        <div class="relative">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 group-hover:rotate-12 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                            </svg>
                            @if($selectedBrand !== 'All' || $selectedType !== 'All')
                                <span class="absolute -top-1 -right-1 flex h-2.5 w-2.5">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-red-500 border-2 border-white"></span>
                                </span>
                            @endif
                        </div>
                        <span>Filter Options</span>
                    </button>
                </div>

                <div class="flex items-center gap-4">
                    <h2 class="text-2xl md:text-3xl font-black text-gray-900 dark:text-white">
                        Available Fleet 
                        <span class="ml-2 text-sm font-bold text-gray-400 bg-gray-100 dark:bg-gray-800 px-3 py-1 rounded-full">
                            {{ count($cars) }} results
                        </span>
                    </h2>
                </div>
            </div>

            <div class="relative">
                <div class="flex gap-6 overflow-x-auto pb-10 hide-scrollbar snap-x snap-mandatory md:grid md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 md:gap-8 md:overflow-visible">
                    @forelse($cars as $car)
                        <div class="snap-center shrink-0 w-[88%] md:w-full group">
                            <div class="hover:-translate-y-2 transition-transform duration-500 ease-out">
                                <x-booking.car-tile :car="$car" :company="$company" />
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full py-24 text-center bg-white dark:bg-gray-800 rounded-[2.5rem] border border-gray-100 dark:border-gray-700 shadow-sm transition-colors duration-300">
                            <div class="mb-6 inline-flex p-6 bg-gray-50 dark:bg-gray-900 rounded-full text-gray-300 dark:text-gray-700">
                                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 9.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <h4 class="text-xl font-bold text-gray-900 dark:text-white mb-2">No matches found</h4>
                            <p class="text-gray-500 dark:text-gray-400">Try adjusting your filters to find more cars.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </section>

    <x-booking.sliding-filter 
        :filterOpen="false" 
        :brands="$brands"
        :types="$types"
        :transmissions="$transmissions"
        :selectedBrand="$selectedBrand"
        :selectedType="$selectedType"
        :selectedTransmission="$selectedTransmission"
    />

    <x-booking.footer :company="$company" />
</div>