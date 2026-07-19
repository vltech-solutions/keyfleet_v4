@php
$features = [
    [
        'image' => Storage::url('images/website/svg/dashboard.svg'),
        'title' => 'Track Your Money',
        'desc' => 'Stay on top of every peso — income, expenses, and cash flow in one clear view.'
    ],
    [
        'image' => Storage::url('images/website/svg/visual-reports.svg'),
        'title' => 'Visual Reports',
        'desc' => 'Turn raw data into clear insights with charts that make trends easy to spot.'
    ],
    [
        'image' => Storage::url('images/website/svg/funds.svg'),
        'title' => 'Account Overview',
        'desc' => 'See cash, bank, and digital balances instantly — no more guessing.'
    ],
    [
        'image' => Storage::url('images/website/svg/upcoming.svg'),
        'title' => 'Upcoming Bookings',
        'desc' => 'Always know which cars are going out, when they return, and who\'s driving.'
    ],
    [
        'image' => Storage::url('images/website/svg/contract.svg'),
        'title' => 'Contract Builder',
        'desc' => 'Generate professional rental agreements in minutes — drag, drop, and send.'
    ],
    [
        'image' => Storage::url('images/website/svg/customers.svg'),
        'title' => 'Customer Management',
        'desc' => 'Keep renter details, documents, and full booking history at your fingertips.'
    ],
    [
        'image' => Storage::url('images/website/svg/invoice.svg'),
        'title' => 'Invoicing That Works',
        'desc' => 'Send branded, accurate invoices automatically with every confirmed booking.'
    ],
    [
        'image' => Storage::url('images/website/svg/calendar.svg'),
        'title' => 'Booking Calendar',
        'desc' => 'View your entire fleet schedule in a clean, shareable calendar.'
    ],
    [
        'image' => Storage::url('images/website/svg/car-availability.svg'),
        'title' => 'Car Availability',
        'desc' => 'Know exactly which cars are free, booked, or due back — at a glance.'
    ],
    // New Features
    [
        'image' => Storage::url('images/website/svg/booking-form.svg'), 
        'title' => 'Smart Booking Website',
        'desc' => 'A fully customizable booking website with real-time car availability, detailed car views, and an integrated booking form. No coding required.'
    ],
    [
        'image' => Storage::url('images/website/svg/checklist.svg'), 
        'title' => 'Smart Inspection Checklist',
        'desc' => 'Digital inspection checklists with interactive car diagrams. Capture vehicle conditions, identify damages, and maintain detailed inspection records.'
    ],
    [
        'image' => Storage::url('images/website/svg/customer-partner.svg'), 
        'title' => 'Customer & Partner Portal',
        'desc' => 'Dedicated portals for customers and partners. Customers can view bookings, download invoices, and manage their profiles while partners access real-time fleet data.'
    ],
];
@endphp

<section 
    x-data="{ modalOpen: false, modalImage: '', modalTitle: '' }"
    @keydown.escape.window="modalOpen = false"
    class="px-6 py-24 bg-white dark:bg-gray-900 transition-colors duration-300" 
    id="features"
>
    <div class="mx-auto max-w-7xl">
        <div class="mb-16 text-center">
            <span class="inline-block px-4 py-1.5 text-xs font-bold tracking-wider text-indigo-700 dark:text-indigo-300 uppercase bg-indigo-100 dark:bg-indigo-900/30 rounded-full">Features</span>
            <h2 class="mt-4 text-4xl font-extrabold text-gray-900 dark:text-white sm:text-5xl">What You Can Do with Keyfleet</h2>
            <p class="max-w-2xl mx-auto text-lg text-gray-600 dark:text-gray-400">
                Everything you need to run your rental business — in one place. Easy to use. Packed with power.
            </p>
        </div>

        <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($features as $index => $card)
                <div 
                    class="p-6 text-left transition-all duration-300 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-sm group rounded-2xl hover:shadow-xl hover:-translate-y-1  }}"
                >
                    @if($index >= 9)
                        <div class="relative">
                            <span class="absolute -top-2 -right-2 px-2.5 py-0.5 text-[10px] font-bold text-white bg-indigo-600 dark:bg-indigo-500 rounded-full">New</span>
                        </div>
                    @endif
                    <div 
                        class="p-1 mb-5 overflow-hidden rounded-lg cursor-pointer" 
                        @click="modalImage = '{{ $card['image'] }}'; modalTitle = '{{ $card['title'] }}'; modalOpen = true;"
                    >
                        <img 
                            src="{{ $card['image'] }}"
                            alt="{{ $card['title'] }}"
                            class="object-contain w-full h-48 transition-transform duration-500 rounded-lg group-hover:scale-105"
                            loading="lazy"
                        >
                    </div>
                    <h3 class="mb-2 text-xl font-semibold text-gray-800 dark:text-white">
                        {{ $card['title'] }}
                    </h3>
                    <p class="text-sm leading-relaxed text-gray-600 dark:text-gray-400">{{ $card['desc'] }}</p>
                    
                   
                </div>
            @endforeach
        </div>
    </div>

    <!-- Modal -->
    <div 
        x-cloak
        x-show="modalOpen" 
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm"
        @click.away="modalOpen = false"
    >
        <div 
            class="relative w-full max-w-3xl p-6 bg-white dark:bg-gray-900 rounded-2xl shadow-2xl"
            @click.outside="modalOpen = false"
        >
            <button 
                class="absolute text-gray-400 dark:text-gray-500 transition-colors top-4 right-4 hover:text-gray-800 dark:hover:text-gray-300" 
                @click="modalOpen = false"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
            <h2 class="mb-4 text-2xl font-bold text-gray-900 dark:text-white" x-text="modalTitle"></h2>
            <div class="overflow-hidden rounded-lg bg-gray-50 dark:bg-gray-800">
                <img :src="modalImage" alt="" class="w-full rounded-lg" loading="lazy">
            </div>
            <div class="flex justify-end mt-4">
                <button 
                    @click="modalOpen = false" 
                    class="px-6 py-2 text-sm font-medium text-white bg-indigo-600 dark:bg-indigo-500 rounded-lg hover:bg-indigo-700 dark:hover:bg-indigo-600 transition"
                >
                    Close
                </button>
            </div>
        </div>
    </div>
</section>

<style>
    [x-cloak] { display: none !important; }
</style>