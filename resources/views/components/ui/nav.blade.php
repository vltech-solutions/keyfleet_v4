<header x-data="{ open: false }" class="sticky top-0 z-50 bg-white shadow-sm">
    <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="flex items-center justify-between py-4">
            <!-- Logo -->
            <a href="{{ url('/') }}" class="text-2xl font-extrabold tracking-tight text-blue-800">
                KEYFLEET
            </a>

            <!-- Desktop Navigation -->
            <nav class="items-center hidden space-x-8 text-sm font-medium text-gray-700 md:flex">
                <a href="/" class="px-4 transition-all hover:text-blue-600">Home</a>
                <a href="/blog" class="px-4 transition-all hover:text-blue-600">Blog</a>
                <a href="/#features" class="px-4 transition-all hover:text-blue-600">Features</a>
                <a href="/testimonials" class="px-4 transition-all hover:text-blue-600">Testimonials</a>
                <a href="/pricing" class="px-4 transition-all hover:text-blue-600">Pricing</a>
		        <a href="/app/login" class="px-4 transition-all hover:text-blue-600">Log-In</a>
            </nav>

            <!-- CTA Button (Desktop) -->
	    <div class="hidden md:block">
               	<a href="{{ route('tenant.register') }}" class="px-5 py-2 ml-6 text-sm font-semibold text-white transition duration-200 bg-blue-700 rounded-full hover:bg-blue-800">Get Started</a>
            </div>

            <!-- Mobile Menu Button -->
            <button @click="open = !open" class="text-gray-700 md:hidden focus:outline-none focus:ring-2 focus:ring-blue-500">
                <svg x-show="!open" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                <svg x-show="open" x-cloak class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         x-cloak
         class="bg-white border-t md:hidden">
        <nav class="flex flex-col px-4 py-4 space-y-2 text-sm font-medium text-gray-700">
            <a href="/" class="hover:text-blue-600">Home</a>
            <a href="/blog" class="hover:text-blue-600">Blog</a>
            <a href="/#features" class="hover:text-blue-600">Features</a>
            <a href="/testimonials" class="hover:text-blue-600">Testimonials</a>
            <a href="/pricing" class="hover:text-blue-600">Pricing</a>
            <a href="/app/login" class="hover:text-blue-600">Log-In</a>
            <a href="{{ route('tenant.register') }}"
               class="w-full py-2 text-center text-white transition bg-blue-700 rounded-full hover:bg-blue-800">
                Get Started
            </a>
        </nav>
    </div>
</header>
