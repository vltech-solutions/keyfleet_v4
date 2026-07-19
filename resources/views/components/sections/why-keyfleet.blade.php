<section class="px-6 py-24 bg-gray-50 dark:bg-gray-900/50 overflow-hidden transition-colors duration-300">
    <div class="grid items-center grid-cols-1 gap-16 mx-auto max-w-7xl md:grid-cols-2">
        
        <!-- Left Content -->
        <div class="text-center md:text-left">
            <span class="inline-block px-4 py-1.5 text-xs font-bold tracking-wider text-indigo-700 dark:text-indigo-300 uppercase bg-indigo-100 dark:bg-indigo-900/30 rounded-full">Why Keyfleet</span>
            <h2 class="mt-4 mb-6 text-4xl font-extrabold leading-tight text-gray-900 dark:text-white sm:text-5xl">
                Everything You Need,<br class="hidden sm:inline"> 
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-blue-600 dark:from-indigo-400 dark:to-blue-400">Nothing You Don't.</span>
            </h2>
            <p class="mb-8 text-lg text-gray-600 dark:text-gray-400">
                Say goodbye to clunky spreadsheets, double-bookings, and disorganized messages.
                With Keyfleet, you get a centralized, intuitive platform to manage your fleet confidently — from anywhere.
            </p>

            <ul class="space-y-4 text-left text-gray-700 dark:text-gray-300">
                <li class="flex items-start gap-3 transition-all duration-200 hover:translate-x-1 group">
                    <div class="flex items-center justify-center flex-shrink-0 w-6 h-6 mt-1 rounded-full bg-green-100 dark:bg-green-900/30 group-hover:bg-green-200 dark:group-hover:bg-green-800/50 transition-colors">
                        <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <span class="font-medium">Real-time availability and calendar sync</span>
                </li>
                <li class="flex items-start gap-3 transition-all duration-200 hover:translate-x-1 group">
                    <div class="flex items-center justify-center flex-shrink-0 w-6 h-6 mt-1 rounded-full bg-green-100 dark:bg-green-900/30 group-hover:bg-green-200 dark:group-hover:bg-green-800/50 transition-colors">
                        <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <span class="font-medium">Automated booking confirmations and reminders</span>
                </li>
                <li class="flex items-start gap-3 transition-all duration-200 hover:translate-x-1 group">
                    <div class="flex items-center justify-center flex-shrink-0 w-6 h-6 mt-1 rounded-full bg-green-100 dark:bg-green-900/30 group-hover:bg-green-200 dark:group-hover:bg-green-800/50 transition-colors">
                        <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <span class="font-medium">Access from desktop, tablet, or mobile</span>
                </li>
            </ul>

            <div class="flex flex-wrap gap-4 mt-8">
                <a href="{{ route('tenant.register') }}" 
                   class="inline-flex items-center gap-2 px-6 py-3 text-sm font-semibold text-white transition-all bg-indigo-600 dark:bg-indigo-500 rounded-xl hover:bg-indigo-700 dark:hover:bg-indigo-600 shadow-lg shadow-indigo-600/25 dark:shadow-indigo-900/30 hover:shadow-indigo-600/40 hover:-translate-y-0.5">
                    Start Free Trial
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                    </svg>
                </a>
                <a href="#features" 
                   class="inline-flex items-center gap-2 px-6 py-3 text-sm font-semibold text-gray-700 dark:text-gray-300 transition-all bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-600">
                    Explore Features
                </a>
            </div>
        </div>

        <!-- Right Image -->
        <div class="relative">
            <!-- Floating badges -->
            <div class="absolute -top-4 -left-4 z-10 px-4 py-2 text-xs font-semibold text-white bg-indigo-600 dark:bg-indigo-500 rounded-lg shadow-lg animate-bounce-slow">
                ⚡ Real-time sync
            </div>
            <div class="absolute -bottom-4 -right-4 z-10 px-4 py-2 text-xs font-semibold text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 rounded-lg shadow-lg">
                📱 Mobile friendly
            </div>
            
            <!-- Image container -->
            <div class="relative overflow-hidden rounded-2xl shadow-2xl">
                <img src="{{ Storage::url('images/website/bookings.png') }}"
                     alt="Fleet dashboard preview"
                     class="object-cover w-full h-auto transition-transform duration-700 hover:scale-105"
                     loading="lazy">
                
                <!-- Gradient overlay -->
                <div class="absolute inset-0 bg-gradient-to-t from-gray-900/10 dark:from-gray-900/30 to-transparent"></div>
            </div>

            <!-- Stats floating badge -->
            <div class="absolute -bottom-6 left-1/2 -translate-x-1/2 px-6 py-3 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700 flex items-center gap-6 whitespace-nowrap">
                <div class="flex items-center gap-2">
                    <div class="flex -space-x-2">
                        <div class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-700 border-2 border-white dark:border-gray-800 flex items-center justify-center text-xs font-bold text-gray-600 dark:text-gray-300">JD</div>
                        <div class="w-8 h-8 rounded-full bg-blue-200 dark:bg-blue-900/50 border-2 border-white dark:border-gray-800 flex items-center justify-center text-xs font-bold text-blue-600 dark:text-blue-400">MK</div>
                        <div class="w-8 h-8 rounded-full bg-green-200 dark:bg-green-900/50 border-2 border-white dark:border-gray-800 flex items-center justify-center text-xs font-bold text-green-600 dark:text-green-400">AL</div>
                        <div class="w-8 h-8 rounded-full bg-purple-200 dark:bg-purple-900/50 border-2 border-white dark:border-gray-800 flex items-center justify-center text-xs font-bold text-purple-600 dark:text-purple-400">+</div>
                    </div>
                    <div>
                        <div class="text-sm font-bold text-gray-900 dark:text-white">1,200+</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Active users</div>
                    </div>
                </div>
                <div class="w-px h-8 bg-gray-200 dark:bg-gray-700"></div>
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
</section>