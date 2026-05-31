@props(['company'])

@if($company->website && $company->website->map_url)
<section id="location" {{ $attributes->merge(['class' => 'py-20 bg-gray-50/50']) }}>
    <div class="max-w-7xl mx-auto px-4 md:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-12 items-end">
            
            {{-- Text Info --}}
            <div class="lg:col-span-1 space-y-3">
                <div>
                    <h2 class="text-xs font-bold text-gray-400 uppercase tracking-[0.3em] mb-3">Find Us</h2>
                    <h3 class="text-4xl font-black text-gray-900 leading-tight">Our Office Location</h3>
                </div>
                
                <p class="text-gray-600 leading-relaxed">
                    Ready to pick up your ride? Visit us at our main hub. We are conveniently located to serve you better and faster.
                </p>

                @if($company->website->business_address)
                    <div class="flex items-start gap-4  rounded-2xl ">
                        <div class="w-10 h-10 rounded-full bg-gray-900 flex items-center justify-center shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Address</p>
                            <p class="text-sm font-bold text-gray-900 mt-1">{{ $company->website->business_address ?? '' }}</p>
                        </div>
                    </div>
                @endif

                {{-- Contacts Column Section --}}
                @if($company->contacts)
                    <div class="flex items-start gap-4 mt-4">
                        <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center shrink-0 border border-gray-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Contact Details</p>
                            <div class="text-sm font-bold text-gray-900 mt-1">
                                {{ $company->contacts }}
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Map Iframe --}}
            <div class="lg:col-span-2">
                <div class="relative w-full h-[400px] md:h-[500px] rounded-[2.5rem] overflow-hidden shadow-2xl border-8 border-white">
                    <iframe 
                        class="absolute inset-0 w-full h-full grayscale-[10%] hover:grayscale-0 transition-all duration-700"
                        src="{{ $company->website->map_url }}" 
                        style="border:0;" 
                        allowfullscreen="" 
                        loading="lazy" 
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
            </div>

        </div>
    </div>
</section>
@endif