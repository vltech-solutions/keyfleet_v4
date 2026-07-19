@props(['company', 'companyLogo'])

<nav 
    x-data="{ 
        scrolled: false, 
        mobileMenuOpen: false,
        loginModalOpen: false
    }" 
    x-init="scrolled = window.pageYOffset > 20"
    @scroll.window="scrolled = (window.pageYOffset > 20)"
    :class="(scrolled || mobileMenuOpen) 
        ? 'bg-white/95 backdrop-blur-lg border-b border-gray-200 shadow-sm' 
        : 'bg-transparent border-transparent'"
    class="fixed top-0 left-0 right-0 z-50 transition-all duration-300 ease-in-out"
>
    <div class="max-w-7xl mx-auto px-4 md:px-8 flex items-center justify-between h-20 transition-all duration-300"
        :class="scrolled ? 'h-16' : 'h-24'">
        
        {{-- Brand/Logo --}}
        <a href="/" class="flex items-center gap-2 font-bold text-xl tracking-tight transition-colors duration-300 z-[60]">
            <img src="{{ $companyLogo }}" alt="{{ $company->name }} Logo" 
                class="object-contain w-10 h-10 rounded-md shadow-sm transition-transform"
                :class="scrolled ? 'scale-100' : 'scale-110'">
            <span 
                x-bind:class="(scrolled || mobileMenuOpen) ? 'text-gray-900' : 'text-white'"
                class="hidden md:inline-block transition-colors duration-300">
                {{ $company->name }}
            </span>
        </a>

        {{-- Desktop Navigation --}}
        <div class="hidden md:flex items-center gap-8 text-sm font-medium transition-colors duration-300">
            <a href="#cars" 
                class="px-4 py-2 rounded-full border transition-all duration-300"
                x-bind:class="(scrolled || mobileMenuOpen) 
                    ? 'border-gray-900 text-gray-900 hover:bg-gray-900 hover:text-white' 
                    : 'border-white/80 text-white/80 hover:bg-white hover:text-gray-900'">
                Our Fleet
            </a>
            
            {{-- Check if customer is logged in --}}
            @php
                $customerToken = session('customer_token');
                $isLoggedIn = !empty($customerToken);
            @endphp

            @if($isLoggedIn)
                {{-- Show Account link when logged in --}}
                <a href="{{ route('customer.dashboard', ['repeatToken' => $customerToken]) }}" 
                    class="px-4 py-2 rounded-full border transition-all duration-300 flex items-center gap-2"
                    x-bind:class="(scrolled || mobileMenuOpen) 
                        ? 'border-gray-900 text-gray-900 hover:bg-gray-900 hover:text-white' 
                        : 'border-white/80 text-white/80 hover:bg-white hover:text-gray-900'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span>My Account</span>
                </a>
            @else
                {{-- Show Login button when not logged in --}}
                <button @click="$dispatch('open-login-modal')" 
                    class="px-4 py-2 rounded-full border transition-all duration-300 flex items-center gap-2"
                    x-bind:class="(scrolled || mobileMenuOpen) 
                        ? 'border-gray-900 text-gray-900 hover:bg-gray-900 hover:text-white' 
                        : 'border-white/80 text-white/80 hover:bg-white hover:text-gray-900'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                    </svg>
                    <span>Login</span>
                </button>
            @endif
        </div>

        {{-- Mobile Hamburger Button --}}
        <div class="flex md:hidden items-center z-[60] gap-2">
            @if($isLoggedIn)
                {{-- Mobile Account link --}}
                <a href="{{ route('customer.dashboard', ['repeatToken' => $customerToken]) }}" 
                    class="p-2 transition-colors focus:outline-none"
                    x-bind:class="(scrolled || mobileMenuOpen) ? 'text-gray-900' : 'text-white'">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </a>
            @else
                {{-- Mobile Login Button --}}
                <button @click="$dispatch('open-login-modal')" 
                        class="p-2 transition-colors focus:outline-none"
                        x-bind:class="(scrolled || mobileMenuOpen) ? 'text-gray-900' : 'text-white'">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                    </svg>
                </button>
            @endif
            
            <button @click="mobileMenuOpen = !mobileMenuOpen" 
                    class="p-2 transition-colors focus:outline-none"
                    x-bind:class="(scrolled || mobileMenuOpen) ? 'text-gray-900' : 'text-white'">
                <svg x-show="!mobileMenuOpen" xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
                </svg>
                <svg x-show="mobileMenuOpen" x-cloak xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>

    {{-- Mobile Menu Overlay --}}
    <div x-show="mobileMenuOpen" 
        x-cloak
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 -translate-y-4"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-4"
        class="absolute top-0 left-0 w-full bg-white border-b border-gray-200 pt-24 pb-12 px-6 shadow-xl md:hidden">
        
        <div class="flex flex-col gap-6 text-center">
            <a href="#cars" @click="mobileMenuOpen = false" class="text-xl font-bold text-gray-900 hover:text-blue-600">Our Fleet</a>
            <hr class="border-gray-100">
            
            @if($isLoggedIn)
                <a href="{{ route('customer.dashboard', ['repeatToken' => $customerToken]) }}" 
                   @click="mobileMenuOpen = false" 
                   class="text-xl font-bold text-gray-900 hover:text-blue-600 flex items-center justify-center gap-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    My Account
                </a>
            @else
                <button @click="$dispatch('open-login-modal'); mobileMenuOpen = false" 
                        class="text-xl font-bold text-gray-900 hover:text-blue-600 flex items-center justify-center gap-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                    </svg>
                    Login
                </button>
            @endif
        </div>
    </div>
</nav>