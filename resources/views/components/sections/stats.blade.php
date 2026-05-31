<section class="py-16 bg-white">
    <div class="max-w-6xl px-4 mx-auto sm:px-6 lg:px-8">
        <div class="mb-12 text-center">
            <h2 class="text-3xl font-extrabold text-gray-900 sm:text-4xl">You're in good company</h2>
            <p class="mt-4 text-lg text-gray-600">Trusted by industry leaders and growing teams nationwide.</p>
        </div>

        <div class="grid grid-cols-1 gap-10 text-center sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
            <div>
                <div class="mb-2 text-blue-600">
                    <svg class="w-10 h-10 mx-auto" fill="none" stroke="currentColor" stroke-width="2"
                         viewBox="0 0 24 24">
                        <path d="M3 10h4l3 10h8l3-6h-4l-3 6"></path>
                    </svg>
                </div>
                <p class="text-3xl font-bold text-blue-800">100+</p>
                <p class="text-gray-600">Companies use Keyfleet</p>
            </div>

            <div>
                <div class="mb-2 text-green-600">
                    <svg class="w-10 h-10 mx-auto" fill="none" stroke="currentColor" stroke-width="2"
                         viewBox="0 0 24 24">
                        <path d="M3 3h18v4H3zm0 6h18v12H3z"></path>
                    </svg>
                </div>
                <p class="text-3xl font-bold text-green-700">3,000+</p>
                <p class="text-gray-600">Bookings managed</p>
            </div>

            <div>
                <div class="mb-2 text-purple-600">
                    <svg class="w-10 h-10 mx-auto" fill="none" stroke="currentColor" stroke-width="2"
                         viewBox="0 0 24 24">
                        <path d="M12 8v4l3 3"></path>
                        <circle cx="12" cy="12" r="10"></circle>
                    </svg>
                </div>
                <p class="text-3xl font-bold text-purple-700">99.99%</p>
                <p class="text-gray-600">App uptime</p>
            </div>

            <div>
                <div class="mb-2 text-yellow-500">
                    <svg class="w-10 h-10 mx-auto" fill="none" stroke="currentColor" stroke-width="2"
                         viewBox="0 0 24 24">
                        <path d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <p class="text-3xl font-bold text-yellow-600">4.9/5</p>
                <p class="text-gray-600">Average user rating</p>
            </div>
        </div>
    </div>
    {{-- <div class="w-full py-8 mt-4 overflow-hidden bg-white">
        <div class="flex animate-marquee whitespace-nowrap">
            <!-- Repeat the same loop multiple times for infinite feel -->
            @for ($i = 0; $i < 3; $i++) 
                <div class="flex space-x-6">
                    @foreach($companies as $company)
                        <div class="flex items-center justify-center min-w-[12rem] shadow h-28 bg-gray-50 rounded-xl">
                            <img src="{{ Storage::url($company->avatar_url) }}" alt="{{ $company->name }}" class="rounded max-h-24" />
                        </div>
                    @endforeach
                </div>
            @endfor
        </div>
    </div> --}}

</section>
