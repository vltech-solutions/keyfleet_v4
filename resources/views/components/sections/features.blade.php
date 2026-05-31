@php
$features = [
    ['image' => Storage::url('images/website/svg/dashboard.svg'), 'title' => 'Track Your Money', 'desc' => 'Stay on top of every peso — income, expenses, and cash flow in one clear view.'],
    ['image' => Storage::url('images/website/svg/visual-reports.svg'), 'title' => 'Visual Reports', 'desc' => 'Turn raw data into clear insights with charts that make trends easy to spot.'],
    ['image' => Storage::url('images/website/svg/funds.svg'), 'title' => 'Account Overview', 'desc' => 'See cash, bank, and digital balances instantly — no more guessing.'],
    ['image' => Storage::url('images/website/svg/upcoming.svg'), 'title' => 'Upcoming Bookings', 'desc' => 'Always know which cars are going out, when they return, and who’s driving.'],
    ['image' => Storage::url('images/website/svg/contract.svg'), 'title' => 'Contract Builder', 'desc' => 'Generate professional rental agreements in minutes — drag, drop, and send.'],
    ['image' => Storage::url('images/website/svg/customers.svg'), 'title' => 'Customer Management', 'desc' => 'Keep renter details, documents, and full booking history at your fingertips.'],
    ['image' => Storage::url('images/website/svg/invoice.svg'), 'title' => 'Invoicing That Works', 'desc' => 'Send branded, accurate invoices automatically with every confirmed booking.'],
    ['image' => Storage::url('images/website/svg/calendar.svg'), 'title' => 'Booking Calendar', 'desc' => 'View your entire fleet schedule in a clean, shareable calendar.'],
    ['image' => Storage::url('images/website/svg/car-availability.svg'), 'title' => 'Car Availability', 'desc' => 'Know exactly which cars are free, booked, or due back — at a glance.'],
];

@endphp

<section 
    x-data="{ modalOpen: false, modalImage: '', modalTitle: '' }"
    @keydown.escape.window="modalOpen = false"
    class="px-6 py-24 bg-white" 
    id="features"
>
    <div class="mx-auto max-w-7xl">
        <div class="mb-16 text-center">
            <h2 class="mb-4 text-4xl font-extrabold text-gray-900 sm:text-5xl">What You Can Do with Keyfleet</h2>
            <p class="max-w-2xl mx-auto text-lg text-gray-600">
                Everything you need to run your rental business — in one place. Easy to use. Packed with power.
            </p>
        </div>

        <div class="grid grid-cols-1 gap-10 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($features as $card)
                <div 
                    class="p-6 text-left transition duration-300 bg-white border border-gray-200 shadow-sm group rounded-2xl hover:shadow-xl"
                >
                    <div 
                        class="p-1 mb-5 overflow-hidden rounded-lg cursor-pointer" 
                        {{-- @click="modalImage = '{{ $card['image'] }}'; modalTitle = '{{ $card['title'] }}'; modalOpen = true;" --}}
                    >
                        <img 
                            src="{{ $card['image'] }}"
                            alt="{{ $card['title'] }}"
                            class="object-contain w-full h-48 transition-transform duration-300 rounded-lg group-hover:scale-105"
                        >
                    </div>
                    <h3 class="mb-2 text-xl font-semibold text-gray-800">{{ $card['title'] }}</h3>
                    <p class="text-sm leading-relaxed text-gray-600">{{ $card['desc'] }}</p>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Modal -->
    <div 
        x-cloak
        x-show="modalOpen" 
        x-transition 
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/70"
    >
        <div 
            class="relative w-full max-w-2xl p-4 bg-white rounded-xl"
            @click.outside="modalOpen = false"
        >
            <button 
                class="absolute text-gray-500 top-2 right-2 hover:text-gray-800" 
                @click="modalOpen = false"
            >
                ✕
            </button>
            <h2 class="mb-4 text-xl font-semibold text-gray-700" x-text="modalTitle"></h2>
            <img :src="modalImage" alt="" class="w-full rounded-md">
        </div>
    </div>
</section>
