<section class="px-6 py-24 bg-gray-50">
    <div class="grid items-center grid-cols-1 gap-12 mx-auto max-w-7xl md:grid-cols-2">
        
        <!-- Left Content -->
        <div class="text-center md:text-left">
            <h2 class="mb-6 text-4xl font-extrabold leading-tight text-gray-900 sm:text-5xl">
                Everything You Need,<br class="hidden sm:inline"> Nothing You Don’t.
            </h2>
            <p class="mb-6 text-lg text-gray-600">
                Say goodbye to clunky spreadsheets, double-bookings, and disorganized messages.
                With Keyfleet, you get a centralized, intuitive platform to manage your fleet confidently — from anywhere.
            </p>

            <ul class="space-y-4 text-left text-gray-700">
                <li class="flex items-start gap-3">
                    <svg class="w-6 h-6 mt-1 text-green-500" fill="none" stroke="currentColor" stroke-width="2"
                         viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"></path></svg>
                    Real-time availability and calendar sync
                </li>
                <li class="flex items-start gap-3">
                    <svg class="w-6 h-6 mt-1 text-green-500" fill="none" stroke="currentColor" stroke-width="2"
                         viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"></path></svg>
                    Automated booking confirmations and reminders
                </li>
                <li class="flex items-start gap-3">
                    <svg class="w-6 h-6 mt-1 text-green-500" fill="none" stroke="currentColor" stroke-width="2"
                         viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"></path></svg>
                    Access from desktop, tablet, or mobile
                </li>
            </ul>
        </div>

        <!-- Right Image -->
        <div class="relative">
            <img src="{{ Storage::url('images/website/bookings.png') }}"
                 alt="Fleet dashboard preview"
                 class="object-cover w-full h-auto shadow-2xl rounded-xl"
                 loading="lazy">
            {{-- <div class="absolute px-3 py-1 text-sm font-medium text-gray-800 rounded-full shadow top-4 left-4 bg-white/80">
                Sample Interface
            </div> --}}
        </div>
    </div>
</section>
