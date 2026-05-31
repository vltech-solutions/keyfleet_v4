<div class="bottom-navbar fixed bottom-6 left-1/2 z-40 w-[92%] -translate-x-1/2 lg:hidden">
    <div class="relative bg-white/80 dark:bg-gray-900/90 backdrop-blur-xl border border-white/20 dark:border-gray-700/50 shadow-[0_8px_32px_0_rgba(0,0,0,0.3)] dark:shadow-[0_8px_32px_0_rgba(0,0,0,0.6)] rounded-[24px]">
        
        <div class="grid h-16 grid-cols-5 mx-auto font-medium">
            
            @php
                $tenantSlug = filament()->getTenant()?->slug;
                // Pinatingkad ang primary colors sa dark mode para hindi "lubog"
                $activeClass = 'text-primary-600 dark:text-primary-400 scale-105';
                $inactiveClass = 'text-gray-400 dark:text-gray-500';
            @endphp

            <a href="{{ url("/app/{$tenantSlug}/") }}" 
               class="relative flex flex-col items-center justify-center transition-all duration-300 {{ request()->routeIs('filament.app.pages.dashboard') ? $activeClass : $inactiveClass }}">
                <x-heroicon-s-home class="w-6 h-6" />
                <span class="text-[9px] mt-1 font-bold uppercase tracking-tight">Home</span>
                @if(request()->routeIs('filament.app.pages.dashboard'))
                    <span class="absolute -bottom-1 w-1 h-1 bg-primary-600 dark:bg-primary-400 rounded-full shadow-[0_0_8px_rgba(var(--primary-600),0.6)]"></span>
                @endif
            </a>

            <a href="{{ url("/app/{$tenantSlug}/bookings") }}" 
               class="relative flex flex-col items-center justify-center transition-all duration-300 {{ str_contains(request()->url(), '/bookings') ? $activeClass : $inactiveClass }}">
                <x-heroicon-s-calendar class="w-6 h-6" />
                <span class="text-[9px] mt-1 font-bold uppercase tracking-tight">Booking</span>
                @if(str_contains(request()->url(), '/bookings'))
                    <span class="absolute -bottom-1 w-1 h-1 bg-primary-600 dark:bg-primary-400 rounded-full shadow-[0_0_8px_rgba(var(--primary-600),0.6)]"></span>
                @endif
            </a>

            <div class="relative flex justify-center">
                <a href="{{ url("/app/{$tenantSlug}/explore") }}" 
                   class="absolute -top-7 flex flex-col items-center justify-center w-16 h-16 bg-primary-600 dark:bg-primary-500 rounded-full shadow-lg shadow-primary-500/40 dark:shadow-primary-900/60 text-white transform transition-all active:scale-95 border-4 border-white dark:border-gray-900">
                    <x-heroicon-s-squares-2x2 class="w-7 h-7 transition-transform group-active:scale-90" />
                </a>
                <span class="absolute bottom-2 text-[10px] font-bold uppercase tracking-tighter text-gray-500 dark:text-gray-400">Services</span>
            </div>

            <a href="{{ url("/app/{$tenantSlug}/customers") }}" 
               class="relative flex flex-col items-center justify-center transition-all duration-300 {{ str_contains(request()->url(), '/customers') ? $activeClass : $inactiveClass }}">
                <x-heroicon-s-users class="w-6 h-6" />
                <span class="text-[9px] mt-1 font-bold uppercase tracking-tight">Clients</span>
                @if(str_contains(request()->url(), '/customers'))
                    <span class="absolute -bottom-1 w-1 h-1 bg-primary-600 dark:bg-primary-400 rounded-full"></span>
                @endif
            </a>

            <a href="{{ url("/app/{$tenantSlug}/user-profile") }}" 
               class="relative flex flex-col items-center justify-center transition-all duration-300 {{ request()->routeIs('filament.app.pages.user-profile') ? $activeClass : $inactiveClass }}">
                <x-heroicon-s-user-circle class="w-6 h-6" />
                <span class="text-[9px] mt-1 font-bold uppercase tracking-tight">Profile</span>
                @if(request()->routeIs('filament.app.pages.user-profile'))
                    <span class="absolute -bottom-1 w-1 h-1 bg-primary-600 dark:bg-primary-400 rounded-full"></span>
                @endif
            </a>
            
        </div>
    </div>
</div>

<style>
    /* Smooth padding para sa background content */
    body {
        padding-bottom: 8rem; 
    }
    @media (min-width: 1025px) {
        body {
            padding-bottom: 0;
        }
    }

    /* Primary indicator glow for Dark Mode */
    .dark .bg-primary-400 {
        box-shadow: 0 0 10px rgba(96, 165, 250, 0.4);
    }
</style>