<header x-data="{ open: false, scrolled: false, darkMode: window.darkMode }" 
        x-init="
            window.addEventListener('scroll', () => { scrolled = window.scrollY > 50 });
            $watch('darkMode', val => {
                if (val) {
                    document.documentElement.classList.add('dark');
                    localStorage.setItem('theme', 'dark');
                } else {
                    document.documentElement.classList.remove('dark');
                    localStorage.setItem('theme', 'light');
                }
            });
        "
        :class="scrolled ? 'bg-white/95 dark:bg-gray-900/95 backdrop-blur-lg shadow-lg border-b border-gray-100 dark:border-gray-800' : 'bg-white dark:bg-gray-900 shadow-sm'"
        class="sticky top-0 z-50 transition-all duration-300">
    <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 lg:h-20">
            <!-- Logo -->
            <a href="{{ url('/') }}" class="text-2xl font-extrabold tracking-tight text-blue-800 dark:text-blue-400 transition-colors hover:text-blue-900 dark:hover:text-blue-300">
                KEYFLEET
            </a>

            <!-- Desktop Navigation - Now visible on md and up (tablets) -->
            <nav class="items-center hidden space-x-1 text-sm font-medium text-gray-700 dark:text-gray-300 md:flex">
                <a href="/" class="px-4 py-2 transition-all rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-blue-600 dark:hover:text-blue-400">Home</a>
                <a href="/blog" class="px-4 py-2 transition-all rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-blue-600 dark:hover:text-blue-400">Blog</a>
                <a href="/#features" class="px-4 py-2 transition-all rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-blue-600 dark:hover:text-blue-400">Features</a>
                <a href="/testimonials" class="px-4 py-2 transition-all rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-blue-600 dark:hover:text-blue-400">Testimonials</a>
                <a href="/pricing" class="px-4 py-2 transition-all rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-blue-600 dark:hover:text-blue-400">Pricing</a>
                <a
                    href="{{ route('become-agent') }}"
                    class="px-4 py-2 transition-all rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-blue-600 dark:hover:text-blue-400"
                >
                    Become an Agent
                </a>
                <a href="/app/login" class="px-4 py-2 transition-all rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-blue-600 dark:hover:text-blue-400">Log In</a>
            </nav>

            <!-- Right Side: Dark Mode Toggle & CTA -->
            <div class="hidden md:flex md:items-center md:gap-3">
                <!-- Dark Mode Toggle -->
                <button @click="darkMode = !darkMode" 
                        class="p-2.5 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-all duration-200"
                        aria-label="Toggle dark mode">
                    <svg x-show="!darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                    </svg>
                    <svg x-show="darkMode" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </button>

                <a href="{{ route('tenant.register') }}" 
                   class="px-6 py-2.5 text-sm font-semibold text-white transition-all duration-300 bg-blue-700 dark:bg-blue-600 rounded-full hover:bg-blue-800 dark:hover:bg-blue-700 hover:shadow-lg hover:shadow-blue-200/50 dark:hover:shadow-blue-900/30 hover:-translate-y-0.5 active:scale-95">
                    Get Started
                </a>
            </div>

            <!-- Mobile: Dark Mode Toggle & Menu Button -->
            <div class="flex items-center gap-1 md:hidden">
                <!-- Dark Mode Toggle (Mobile) -->
                <button @click="darkMode = !darkMode" 
                        class="p-2 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-all duration-200"
                        aria-label="Toggle dark mode">
                    <svg x-show="!darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                    </svg>
                    <svg x-show="darkMode" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </button>

                <button @click="open = !open" 
                        class="p-2 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <svg x-show="!open" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <svg x-show="open" x-cloak class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-4"
         x-cloak
         class="bg-white dark:bg-gray-900 border-t dark:border-gray-800 shadow-lg md:hidden">
        <nav class="flex flex-col px-4 py-4 space-y-1 text-sm font-medium text-gray-700 dark:text-gray-300">
            <a href="/" class="px-4 py-2.5 transition-all rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-blue-600 dark:hover:text-blue-400" @click="open = false">Home</a>
            <a href="/blog" class="px-4 py-2.5 transition-all rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-blue-600 dark:hover:text-blue-400" @click="open = false">Blog</a>
            <a href="/#features" class="px-4 py-2.5 transition-all rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-blue-600 dark:hover:text-blue-400" @click="open = false">Features</a>
            <a href="/testimonials" class="px-4 py-2.5 transition-all rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-blue-600 dark:hover:text-blue-400" @click="open = false">Testimonials</a>
            <a href="/pricing" class="px-4 py-2.5 transition-all rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-blue-600 dark:hover:text-blue-400" @click="open = false">Pricing</a>
            <a
                href="{{ route('become-agent') }}"
                class="px-4 py-2.5 transition-all rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-blue-600 dark:hover:text-blue-400"
                @click="open = false"
            >
                Become an Agent
            </a>
            <a href="/app/login" class="px-4 py-2.5 transition-all rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-blue-600 dark:hover:text-blue-400" @click="open = false">Log In</a>
            <div class="pt-2 mt-2 border-t border-gray-100 dark:border-gray-800">
                <a href="{{ route('tenant.register') }}"
                   class="block w-full py-3 text-center text-white transition-all bg-blue-700 dark:bg-blue-600 rounded-lg hover:bg-blue-800 dark:hover:bg-blue-700 active:scale-95" @click="open = false">
                    Get Started
                </a>
            </div>
        </nav>
    </div>
</header>