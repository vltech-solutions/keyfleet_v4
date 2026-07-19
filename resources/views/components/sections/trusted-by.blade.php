<section class="py-20 bg-gray-50 dark:bg-gray-900/50 overflow-hidden transition-colors duration-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-12 text-center">
            <span class="inline-block px-4 py-1.5 text-xs font-bold tracking-wider text-indigo-700 dark:text-indigo-300 uppercase bg-indigo-100 dark:bg-indigo-900/30 rounded-full">Testimonials</span>
            <h2 class="mt-4 text-4xl font-extrabold text-gray-900 dark:text-white sm:text-5xl">Trusted by Rental Businesses</h2>
            <p class="max-w-2xl mx-auto mt-4 text-lg text-gray-600 dark:text-gray-400">
                Companies that rely on Keyfleet to manage their fleet and grow their business.
            </p>
        </div>

        <!-- Logo Marquee -->
        <div class="relative w-full py-8 overflow-hidden">
            <!-- Gradient overlays for fade effect -->
            <div class="absolute inset-y-0 left-0 z-10 w-20 bg-gradient-to-r from-gray-50 dark:from-gray-900/50 to-transparent"></div>
            <div class="absolute inset-y-0 right-0 z-10 w-20 bg-gradient-to-l from-gray-50 dark:from-gray-900/50 to-transparent"></div>
            
            <div class="flex items-center justify-center w-full h-full text-base marque-container">
                <div class="box-border flex items-center w-full p-4 overflow-hidden font-light Marquee">
                    <div class="flex Marquee-content animate-marquee hover:animate-pause items-center">
                        
                        @for ($i = 0; $i < 3; $i++)
                            @foreach($companies as $company)
                                @if($company->avatar_url)
                                    <img src="{{ Storage::url($company->avatar_url) }}" 
                                        alt="{{ $company->name }}" 
                                        class="mx-8 transition-all duration-500 max-h-16 w-auto filter grayscale opacity-60 hover:grayscale-0 hover:opacity-100 hover:scale-110 object-contain" />
                                @else
                                    <span class="mx-8 text-sm font-semibold text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                        {{ $company->name }}
                                    </span>
                                @endif
                            @endforeach
                        @endfor

                    </div>
                </div>
            </div>
        </div>

        <!-- Trust Indicators -->
        <div class="grid grid-cols-2 gap-4 mt-8 md:grid-cols-4">
            <div class="flex items-center justify-center gap-2 px-4 py-3 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
                <svg class="w-5 h-5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span class="text-xs font-medium text-gray-600 dark:text-gray-400">100+ Companies Trust Us</span>
            </div>
            <div class="flex items-center justify-center gap-2 px-4 py-3 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
                <svg class="w-5 h-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812z"/>
                </svg>
                <span class="text-xs font-medium text-gray-600 dark:text-gray-400">4.9/5 Average Rating</span>
            </div>
            <div class="flex items-center justify-center gap-2 px-4 py-3 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
                <svg class="w-5 h-5 text-purple-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span class="text-xs font-medium text-gray-600 dark:text-gray-400">Nationwide Coverage</span>
            </div>
            <div class="flex items-center justify-center gap-2 px-4 py-3 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
                <svg class="w-5 h-5 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span class="text-xs font-medium text-gray-600 dark:text-gray-400">Growing Every Month</span>
            </div>
        </div>
    </div>
</section>

<style>
    @keyframes marquee {
        0% { transform: translateX(0); }
        100% { transform: translateX(-50%); }
    }
    .animate-marquee {
        animation: marquee 15s linear infinite; /* Changed from 30s to 15s - twice as fast */
    }
    .hover\:animate-pause:hover {
        animation-play-state: paused;
    }
    
    .Marquee {
        overflow: hidden;
        mask-image: linear-gradient(to right, transparent, black 10%, black 90%, transparent);
        -webkit-mask-image: linear-gradient(to right, transparent, black 10%, black 90%, transparent);
    }
    
    .Marquee-content {
        display: flex;
        align-items: center;
        gap: 1.5rem;
        width: max-content;
    }
    
    .Marquee-tag {
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }
</style>