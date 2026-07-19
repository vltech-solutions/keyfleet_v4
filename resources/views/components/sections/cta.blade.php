<section id="get-started" class="relative px-6 py-24 overflow-hidden text-white shadow-2xl keyfleet-gradient dark:from-blue-900 dark:to-blue-700 transition-colors duration-300">
    <!-- Animated Background Elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <!-- Floating shapes -->
        <div class="absolute -top-20 -right-20 w-64 h-64 bg-white/10 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute -bottom-20 -left-20 w-80 h-80 bg-white/10 rounded-full blur-3xl animate-pulse delay-700"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-white/5 rounded-full blur-3xl"></div>
        
        <!-- Grid pattern -->
        <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGRlZnM+PHBhdHRlcm4gaWQ9ImdyaWQiIHdpZHRoPSI2MCIgaGVpZ2h0PSI2MCIgcGF0dGVyblVuaXRzPSJ1c2VyU3BhY2VPblVzZSI+PHBhdGggZD0iTSA2MCAwIEwgMCAwIDAgNjAiIGZpbGw9Im5vbmUiIHN0cm9rZT0icmdiYSgyNTUsMjU1LDI1NSwwLjA0KSIgc3Ryb2tlLXdpZHRoPSIxIi8+PC9wYXR0ZXJuPjwvZGVmcz48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSJ1cmwoI2dyaWQpIi8+PC9zdmc+')] opacity-20"></div>
    </div>

    <div class="relative flex flex-col items-center justify-between gap-12 mx-auto max-w-7xl lg:flex-row">
        
        <!-- Left Content -->
        <div class="text-center lg:text-left lg:w-1/2">
            <!-- Badge -->
            <span class="inline-block px-4 py-1.5 mb-4 text-xs font-bold tracking-wider text-white/90 bg-white/20 rounded-full backdrop-blur-sm">
                🚀 Start Today
            </span>
            
            <h2 class="mb-4 text-4xl font-extrabold leading-tight sm:text-5xl">
                Get Started in 
                <span class="text-white/90">Minutes</span>
            </h2>
            
            <p class="mb-2 text-xl font-medium text-white/90">No training required. Just sign up and go.</p>
            <p class="mb-8 text-lg text-white/80">Start your 14-day free trial. No credit card needed.</p>
            
            <div class="flex flex-col items-center gap-4 sm:flex-row lg:justify-start">
                <a href="{{ route('tenant.register') }}"
                   class="inline-flex items-center gap-2 px-8 py-4 font-semibold text-blue-800 dark:text-blue-900 transition-all bg-white dark:bg-gray-100 rounded-full shadow-lg hover:shadow-2xl hover:scale-105 hover:bg-gray-50 dark:hover:bg-white group">
                    <span>Start Free Trial</span>
                    <svg class="w-5 h-5 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                    </svg>
                </a>
                <a href="#features" 
                   class="inline-flex items-center gap-2 px-8 py-4 font-semibold text-white transition-all border border-white/30 rounded-full hover:bg-white/10 hover:border-white/50">
                    <span>Explore Features</span>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            </div>

            <!-- Trust badges -->
            <div class="flex flex-wrap items-center justify-center gap-6 mt-8 lg:justify-start">
                <div class="flex items-center gap-2 text-sm text-white/80">
                    <svg class="w-5 h-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>Free 14-day trial</span>
                </div>
                <div class="flex items-center gap-2 text-sm text-white/80">
                    <svg class="w-5 h-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>No credit card required</span>
                </div>
                <div class="flex items-center gap-2 text-sm text-white/80">
                    <svg class="w-5 h-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>Cancel anytime</span>
                </div>
            </div>
        </div>

        <!-- Right Visual -->
        <div class="lg:w-1/2">
            <div class="relative">
                <!-- Floating badge top -->
                <div class="absolute -top-4 -left-4 z-10 px-3 py-1.5 text-xs font-semibold text-blue-800 dark:text-blue-200 bg-white dark:bg-gray-800 rounded-lg shadow-lg">
                    ⚡ Quick setup
                </div>
                
                <!-- Image container -->
                <div class="relative overflow-hidden rounded-2xl shadow-2xl">
                    <img 
                        src="{{ Storage::url('images/website/cars.png') }}"
                        alt="Quick start visual"
                        class="object-cover w-full transition-transform duration-700 hover:scale-105"
                        loading="lazy"
                    >
                    <!-- Gradient overlay -->
                    <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent"></div>
                </div>

                <!-- Floating badge bottom -->
                <div class="absolute -bottom-4 -right-4 z-10 px-3 py-1.5 text-xs font-semibold text-blue-800 dark:text-blue-200 bg-white dark:bg-gray-800 rounded-lg shadow-lg">
                    📱 Mobile ready
                </div>

                <!-- Stats floating card -->
                <div class="absolute -bottom-6 left-1/2 -translate-x-1/2 px-5 py-2.5 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700 flex items-center gap-4 whitespace-nowrap">
                    <div class="flex items-center gap-2">
                        <div class="flex -space-x-2">
                            <div class="w-7 h-7 rounded-full bg-indigo-100 dark:bg-indigo-900/50 border-2 border-white dark:border-gray-800 flex items-center justify-center text-xs font-bold text-indigo-600 dark:text-indigo-400">JD</div>
                            <div class="w-7 h-7 rounded-full bg-blue-100 dark:bg-blue-900/50 border-2 border-white dark:border-gray-800 flex items-center justify-center text-xs font-bold text-blue-600 dark:text-blue-400">MK</div>
                            <div class="w-7 h-7 rounded-full bg-green-100 dark:bg-green-900/50 border-2 border-white dark:border-gray-800 flex items-center justify-center text-xs font-bold text-green-600 dark:text-green-400">AL</div>
                            <div class="w-7 h-7 rounded-full bg-purple-100 dark:bg-purple-900/50 border-2 border-white dark:border-gray-800 flex items-center justify-center text-xs font-bold text-purple-600 dark:text-purple-400">+</div>
                        </div>
                        <div>
                            <div class="text-sm font-bold text-gray-900 dark:text-white">1,200+</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Active users</div>
                        </div>
                    </div>
                    <div class="w-px h-6 bg-gray-200 dark:bg-gray-700"></div>
                    <div class="flex items-center gap-1">
                        <svg class="w-4 h-4 text-yellow-400 fill-current" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        <span class="text-sm font-bold text-gray-900 dark:text-white">4.9</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">(2.4k reviews)</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom wave decoration -->
    <div class="absolute bottom-0 left-0 right-0">
        <svg class="w-full h-12 text-white" viewBox="0 0 1200 120" preserveAspectRatio="none">
            <path d="M0,0 C300,80 600,0 900,80 C1050,120 1150,100 1200,80 L1200,120 L0,120 Z" fill="currentColor" opacity="0.1"/>
        </svg>
    </div>
</section>

<style>
    @keyframes pulse-slow {
        0%, 100% { transform: scale(1); opacity: 0.1; }
        50% { transform: scale(1.1); opacity: 0.2; }
    }
    .animate-pulse-slow {
        animation: pulse-slow 4s ease-in-out infinite;
    }
</style>