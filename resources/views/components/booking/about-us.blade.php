@props(['company'])

<section id="about" {{ $attributes->merge(['class' => 'py-24 bg-white overflow-hidden']) }}>
    <div class="max-w-7xl mx-auto px-4 md:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
            
            {{-- Image Column --}}
            <div class="relative order-2 lg:order-1">
                @if($company->website && $company->website->about_us_image)
                    <div class="relative">
                        {{-- Decorative background element --}}
                        <div class="absolute -bottom-6 -right-6 w-full h-full bg-gray-100 rounded-[2.5rem] -z-10"></div>
                        <img src="{{ Storage::disk('s3')->temporaryUrl($company->website->about_us_image, now()->addMinutes(10)) }}" 
                            alt="About {{ $company->name }}" 
                            class="w-full h-[500px] object-cover rounded-[2.5rem] shadow-2xl ring-8 ring-white">
                        
                        {{-- Small Badge or Experience Counter (Optional) --}}
                        <div class="absolute -bottom-10 -left-10 bg-[var(--tw-primary)] text-white p-8 rounded-3xl hidden md:block shadow-xl">
                            <p class="text-3xl font-black italic mb-1">Premium</p>
                            <p class="text-xs uppercase tracking-widest text-gray-400 font-bold">Car Rental Service</p>
                        </div>
                    </div>
                @else
                    {{-- Default Image kung walang in-upload --}}
                    <div class="bg-gray-100 rounded-[2.5rem] w-full h-[400px] flex items-center justify-center border-2 border-dashed border-gray-200">
                         <svg class="w-16 h-16 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                         </svg>
                    </div>
                @endif
            </div>

            {{-- Text Content Column --}}
            <div class="order-1 lg:order-2 space-y-8">
                <div>
                    <h2 class="text-xs font-bold text-gray-400 uppercase tracking-[0.3em] mb-4">About Our Company</h2>
                    <h3 class="text-4xl md:text-5xl font-black text-gray-900 leading-[1.1]">
                        {{ $company->name }}
                    </h3>
                </div>

                <div class="prose prose-lg prose-slate max-w-none text-gray-600 leading-relaxed
                            prose-headings:text-gray-900 prose-headings:font-black
                            prose-strong:text-gray-900 prose-strong:font-bold
                            prose-p:mb-4">
                    
                    @if($company->website && $company->website->about_us)
                        {!! $company->website->about_us !!}
                    @else
                        <p>Welcome to <strong>{{ $company->name }}</strong>. We are dedicated to providing the best car rental experience with a focus on reliability, customer service, and a diverse fleet.</p>
                        <p>Whether it's for business or pleasure, our fleet is ready to take you where you need to go with style and comfort.</p>
                    @endif
                </div>

                <div class="pt-6 flex flex-wrap items-center gap-6">
                    <a href="#cars" class="inline-flex items-center justify-center px-8 py-4 bg-[var(--tw-primary)] text-white rounded-2xl font-bold  transition-all group shadow-lg shadow-gray-900/20">
                        Explore Fleet
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                        </svg>
                    </a>
                </div>
            </div>

        </div>
    </div>
</section>