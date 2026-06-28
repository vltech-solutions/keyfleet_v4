<div class="bottom-navbar fixed bottom-0 left-0 right-0 z-50 lg:hidden">
    <div class="relative bg-white/95 dark:bg-gray-900/95 backdrop-blur-xl border-t border-gray-200/50 dark:border-gray-800/50 shadow-[0_-4px_20px_0_rgba(0,0,0,0.08)] dark:shadow-[0_-4px_20px_0_rgba(0,0,0,0.3)]">
        
        <!-- Safe area spacer for modern phones -->
        <div class="safe-area-top"></div>
        
        <div class="flex items-center justify-around px-4 pb-2 pt-2">
            
            @php
                $tenantSlug = filament()->getTenant()?->slug;
                $activeClass = 'text-primary-600 dark:text-primary-400';
                $inactiveClass = 'text-gray-400 dark:text-gray-500';
            @endphp

            <!-- Home -->
            <a href="{{ url("/app/{$tenantSlug}/") }}" 
               class="relative flex flex-col items-center justify-center gap-1 py-1 transition-all duration-200 {{ request()->routeIs('filament.app.pages.dashboard') ? $activeClass : $inactiveClass }} group">
                <div class="relative">
                    <x-heroicon-s-home class="w-6 h-6 transition-transform duration-200 group-active:scale-95" />
                    @if(request()->routeIs('filament.app.pages.dashboard'))
                        <span class="absolute inset-0 animate-ping-slow opacity-75">
                            <x-heroicon-s-home class="w-6 h-6 text-primary-400 dark:text-primary-500" />
                        </span>
                    @endif
                </div>
                <span class="text-xs font-medium {{ request()->routeIs('filament.app.pages.dashboard') ? 'text-primary-600 dark:text-primary-400' : 'text-gray-500 dark:text-gray-400' }}">Home</span>
                @if(request()->routeIs('filament.app.pages.dashboard'))
                    <span class="absolute -top-2 w-8 h-0.5 bg-gradient-to-r from-transparent via-primary-500 to-transparent rounded-full"></span>
                @endif
            </a>

            <!-- Bookings -->
            <a href="{{ url("/app/{$tenantSlug}/bookings") }}" 
               class="relative flex flex-col items-center justify-center gap-1 py-1 transition-all duration-200 {{ str_contains(request()->url(), '/bookings') ? $activeClass : $inactiveClass }} group">
                <x-heroicon-s-calendar class="w-6 h-6 transition-transform duration-200 group-active:scale-95" />
                <span class="text-xs font-medium {{ str_contains(request()->url(), '/bookings') ? 'text-primary-600 dark:text-primary-400' : 'text-gray-500 dark:text-gray-400' }}">Bookings</span>
                @if(str_contains(request()->url(), '/bookings'))
                    <span class="absolute -top-2 w-8 h-0.5 bg-gradient-to-r from-transparent via-primary-500 to-transparent rounded-full"></span>
                @endif
            </a>

            <!-- Services (no circle, matching others) -->
            <a href="{{ url("/app/{$tenantSlug}/explore") }}" 
               class="relative flex flex-col items-center justify-center gap-1 py-1 transition-all duration-200 {{ str_contains(request()->url(), '/explore') ? $activeClass : $inactiveClass }} group">
                <x-heroicon-s-squares-2x2 class="w-6 h-6 transition-transform duration-200 group-active:scale-95" />
                <span class="text-xs font-medium {{ str_contains(request()->url(), '/explore') ? 'text-primary-600 dark:text-primary-400' : 'text-gray-500 dark:text-gray-400' }}">Services</span>
                @if(str_contains(request()->url(), '/explore'))
                    <span class="absolute -top-2 w-8 h-0.5 bg-gradient-to-r from-transparent via-primary-500 to-transparent rounded-full"></span>
                @endif
            </a>

            <!-- Customers -->
            <a href="{{ url("/app/{$tenantSlug}/customers") }}" 
               class="relative flex flex-col items-center justify-center gap-1 py-1 transition-all duration-200 {{ str_contains(request()->url(), '/customers') ? $activeClass : $inactiveClass }} group">
                <x-heroicon-s-users class="w-6 h-6 transition-transform duration-200 group-active:scale-95" />
                <span class="text-xs font-medium {{ str_contains(request()->url(), '/customers') ? 'text-primary-600 dark:text-primary-400' : 'text-gray-500 dark:text-gray-400' }}">Clients</span>
                @if(str_contains(request()->url(), '/customers'))
                    <span class="absolute -top-2 w-8 h-0.5 bg-gradient-to-r from-transparent via-primary-500 to-transparent rounded-full"></span>
                @endif
            </a>

            <!-- Profile -->
            <a href="{{ url("/app/{$tenantSlug}/user-profile") }}" 
               class="relative flex flex-col items-center justify-center gap-1 py-1 transition-all duration-200 {{ request()->routeIs('filament.app.pages.user-profile') ? $activeClass : $inactiveClass }} group">
                <x-heroicon-s-user-circle class="w-6 h-6 transition-transform duration-200 group-active:scale-95" />
                <span class="text-xs font-medium {{ request()->routeIs('filament.app.pages.user-profile') ? 'text-primary-600 dark:text-primary-400' : 'text-gray-500 dark:text-gray-400' }}">Profile</span>
                @if(request()->routeIs('filament.app.pages.user-profile'))
                    <span class="absolute -top-2 w-8 h-0.5 bg-gradient-to-r from-transparent via-primary-500 to-transparent rounded-full"></span>
                @endif
            </a>
            
        </div>
        
        <!-- iOS-style home indicator -->
        <div class="pb-1 pt-0.5 flex justify-center">
            <div class="w-10 h-1 bg-gray-300 dark:bg-gray-700 rounded-full"></div>
        </div>
        
        <!-- Safe area spacer for bottom -->
        <div class="safe-area-bottom"></div>
    </div>
</div>

<style>
    /* Modern smooth padding for content */
    body {
        padding-bottom: 5rem;
    }
    
    @media (min-width: 1025px) {
        body {
            padding-bottom: 0;
        }
    }
    
    /* Safe area support for modern iPhones */
    .safe-area-top {
        padding-top: env(safe-area-inset-top, 0px);
    }
    
    .safe-area-bottom {
        padding-bottom: env(safe-area-inset-bottom, 0px);
    }
    
    /* Smooth animations */
    .animate-ping-slow {
        animation: ping 2s cubic-bezier(0, 0, 0.2, 1) infinite;
    }
    
    @keyframes ping {
        75%, 100% {
            transform: scale(1.5);
            opacity: 0;
        }
    }
    
    /* Hover effects for non-touch devices */
    @media (hover: hover) {
        .group:hover .group-hover\:scale-105 {
            transform: scale(1.05);
        }
    }
    
    /* Active ripple effect */
    .group:active .group-active\:scale-95 {
        transform: scale(0.95);
    }
    
    /* Glass morphism enhancement */
    .bottom-navbar .bg-white\/95 {
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
    }
    
    /* Subtle gradient border on top */
    .bottom-navbar .border-t {
        border-image: linear-gradient(90deg, transparent, rgba(99, 102, 241, 0.3), transparent) 1;
    }
    
    /* Dark mode improvements */
    .dark .bottom-navbar .bg-gray-900\/95 {
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
    }
    
    /* Micro-interactions */
    a {
        -webkit-tap-highlight-color: transparent;
    }
    
    /* Smooth transition for active states */
    .transition-all {
        transition-duration: 200ms;
        transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    }
</style>

<!-- Optional: Add haptic feedback for iOS -->
<script>
document.querySelectorAll('.bottom-navbar a').forEach(link => {
    link.addEventListener('click', (e) => {
        // Add haptic feedback simulation (vibrate if supported)
        if (window.navigator && window.navigator.vibrate) {
            window.navigator.vibrate(10);
        }
        
        // Add ripple effect (optional)
        const ripple = document.createElement('span');
        ripple.classList.add('ripple-effect');
        link.appendChild(ripple);
        setTimeout(() => ripple.remove(), 500);
    });
});
</script>

<style>
/* Ripple effect styles */
.ripple-effect {
    position: absolute;
    border-radius: 50%;
    background-color: rgba(99, 102, 241, 0.3);
    transform: scale(0);
    animation: ripple 0.6s linear;
    pointer-events: none;
}

@keyframes ripple {
    to {
        transform: scale(4);
        opacity: 0;
    }
}

/* Ensure links have position relative for ripple */
.bottom-navbar a {
    position: relative;
    overflow: hidden;
}
</style>