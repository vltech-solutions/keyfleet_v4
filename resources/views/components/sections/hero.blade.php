<section class="relative px-5 py-16 overflow-hidden text-white keyfleet-gradient sm:py-20 lg:py-28">
    <!-- Animated Background Elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <!-- Floating shapes -->
        <div class="absolute -top-20 -right-20 w-96 h-96 bg-white/10 rounded-full blur-3xl animate-pulse-slow"></div>
        <div class="absolute -bottom-20 -left-20 w-80 h-80 bg-blue-400/20 rounded-full blur-3xl animate-pulse-slow delay-700"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-white/5 rounded-full blur-3xl"></div>
        
        <!-- Grid pattern -->
        <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGRlZnM+PHBhdHRlcm4gaWQ9ImdyaWQiIHdpZHRoPSI2MCIgaGVpZ2h0PSI2MCIgcGF0dGVyblVuaXRzPSJ1c2VyU3BhY2VPblVzZSI+PHBhdGggZD0iTSA2MCAwIEwgMCAwIDAgNjAiIGZpbGw9Im5vbmUiIHN0cm9rZT0icmdiYSgyNTUsMjU1LDI1NSwwLjA0KSIgc3Ryb2tlLXdpZHRoPSIxIi8+PC9wYXR0ZXJuPjwvZGVmcz48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSJ1cmwoI2dyaWQpIi8+PC9zdmc+')] opacity-20"></div>
        
        <!-- Floating particles -->
        <div class="absolute top-20 left-10 w-2 h-2 bg-white/30 rounded-full animate-float"></div>
        <div class="absolute top-40 right-20 w-3 h-3 bg-white/20 rounded-full animate-float delay-1000"></div>
        <div class="absolute bottom-32 left-1/3 w-2 h-2 bg-white/25 rounded-full animate-float delay-2000"></div>
        <div class="absolute top-1/2 right-10 w-2 h-2 bg-white/30 rounded-full animate-float delay-1500"></div>
    </div>

    <div class="relative px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="flex flex-col items-center gap-8 lg:gap-12 lg:flex-row lg:items-center">
            <!-- Left: Text Content -->
            <div class="flex-1 text-center lg:text-left">
                <!-- Badge -->
                <div class="inline-flex items-center gap-2 px-4 py-2 mb-5 bg-white/10 backdrop-blur-sm rounded-full border border-white/10">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                    </span>
                    <span class="text-xs font-medium text-white/90">🚀 Launch your rental business</span>
                </div>

                <h1 class="mb-4 text-4xl font-extrabold leading-tight tracking-tight sm:text-5xl md:text-6xl lg:text-6xl drop-shadow-md">
                    <span class="block">Run Your Car Rental</span>
                    <span class="block text-transparent bg-clip-text bg-gradient-to-r from-yellow-200 to-yellow-400">Business Smarter</span>
                </h1>
                
                <p class="max-w-xl mx-auto mb-8 text-lg leading-relaxed sm:text-xl md:text-2xl text-white/90 lg:mx-0 drop-shadow-sm">
                    Keyfleet helps rental companies track vehicles, manage bookings, and get paid — all in one simple dashboard.
                </p>
                
                <!-- CTA Buttons -->
                <div class="flex flex-col items-center gap-4 sm:flex-row lg:justify-start">
                    <a href="{{ route('tenant.register') }}"
                       class="inline-flex items-center gap-2 px-8 py-3.5 font-semibold text-blue-800 transition-all duration-300 transform bg-white rounded-full shadow-lg hover:shadow-2xl hover:scale-105 hover:bg-gray-50 group">
                        <span>Start Free Trial</span>
                        <svg class="w-5 h-5 transition-transform duration-300 group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                        </svg>
                    </a>
                    <a href="/pricing"
                       class="inline-flex items-center gap-2 px-8 py-3.5 font-semibold text-white transition-all duration-300 border border-white/30 rounded-full hover:bg-white hover:text-blue-800 hover:border-white">
                        <span>View Pricing</span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                </div>

                <!-- Trust Indicators -->
                <div class="flex flex-wrap items-center justify-center gap-5 mt-6 lg:justify-start">
                    <div class="flex items-center gap-2 text-sm text-white/80">
                        <svg class="w-5 h-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span>14-day free trial</span>
                    </div>
                    <div class="flex items-center gap-2 text-sm text-white/80">
                        <svg class="w-5 h-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span>No credit card</span>
                    </div>
                    <div class="flex items-center gap-2 text-sm text-white/80">
                        <svg class="w-5 h-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span>Cancel anytime</span>
                    </div>
                </div>
            </div>

            <!-- Right: Hero Image -->
            <div class="flex justify-center flex-1 lg:justify-end">
                <div class="relative w-full max-w-2xl">
                    <!-- Floating badge top -->
                    <div class="absolute -top-3 -left-3 z-10 px-3 py-1.5 text-xs font-semibold text-blue-800 bg-white rounded-lg shadow-lg animate-bounce-slow">
                        ⚡ All-in-one platform
                    </div>
                    
                    <!-- Image container -->
                    <div class="relative overflow-hidden rounded-2xl shadow-2xl">
                        <img 
                            src="{{ Storage::url('images/website/hero-image.png') }}" 
                            alt="Small dashboard preview"
                            class="w-full transition-transform duration-700 hover:scale-105"
                            loading="lazy"
                        />
                        <!-- Gradient overlay -->
                        <div class="absolute inset-0 bg-gradient-to-t from-black/10 to-transparent"></div>
                    </div>

                    <!-- Floating badge bottom -->
                    <div class="absolute -bottom-3 -right-3 z-10 px-3 py-1.5 text-xs font-semibold text-blue-800 bg-white rounded-lg shadow-lg">
                        📱 Mobile friendly
                    </div>

                    <!-- Stats floating card -->
                    <div class="absolute -bottom-6 left-1/2 -translate-x-1/2 px-5 py-2.5 bg-white rounded-xl shadow-lg border border-gray-100 flex items-center gap-4 whitespace-nowrap">
                        <div class="flex items-center gap-2">
                            <div class="flex -space-x-2">
                                <div class="w-7 h-7 rounded-full bg-indigo-100 border-2 border-white flex items-center justify-center text-xs font-bold text-indigo-600">JD</div>
                                <div class="w-7 h-7 rounded-full bg-blue-100 border-2 border-white flex items-center justify-center text-xs font-bold text-blue-600">MK</div>
                                <div class="w-7 h-7 rounded-full bg-green-100 border-2 border-white flex items-center justify-center text-xs font-bold text-green-600">AL</div>
                                <div class="w-7 h-7 rounded-full bg-purple-100 border-2 border-white flex items-center justify-center text-xs font-bold text-purple-600">+</div>
                            </div>
                            <div>
                                <div class="text-sm font-bold text-gray-900">100+</div>
                                <div class="text-xs text-gray-500">Companies</div>
                            </div>
                        </div>
                        <div class="w-px h-6 bg-gray-200"></div>
                        <div class="flex items-center gap-1">
                            <svg class="w-4 h-4 text-yellow-400 fill-current" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                            <span class="text-sm font-bold text-gray-900">4.9</span>
                            <span class="text-xs text-gray-500">(2.4k reviews)</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom wave decoration -->
    <div class="absolute bottom-0 left-0 right-0">
        <svg class="w-full h-8 text-white" viewBox="0 0 1200 120" preserveAspectRatio="none">
            <path d="M0,0 C300,80 600,0 900,80 C1050,120 1150,100 1200,80 L1200,120 L0,120 Z" fill="currentColor" opacity="0.1"/>
        </svg>
    </div>
</section>

<style>
    .dark .keyfleet-gradient {
        background-image: linear-gradient(to right, #1a3a6b, #0d4a7a);
    }
    .dark .banner-bg {
        filter: brightness(0.9);
    }
    
    @keyframes pulse-slow {
        0%, 100% { transform: scale(1); opacity: 0.1; }
        50% { transform: scale(1.1); opacity: 0.2; }
    }
    .animate-pulse-slow {
        animation: pulse-slow 4s ease-in-out infinite;
    }

    @keyframes float {
        0%, 100% { transform: translateY(0px) rotate(0deg); }
        50% { transform: translateY(-10px) rotate(3deg); }
    }
    .animate-float {
        animation: float 6s ease-in-out infinite;
    }
    
    @keyframes bounce-slow {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-6px); }
    }
    .animate-bounce-slow {
        animation: bounce-slow 3s ease-in-out infinite;
    }
</style>