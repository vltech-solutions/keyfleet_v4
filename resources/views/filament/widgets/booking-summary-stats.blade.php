<x-filament-widgets::widget>
    <div class="grid w-full grid-cols-1 gap-6 md:grid-cols-3">
        <!-- Upcoming Bookings -->
        <div class="flex items-center justify-between p-6 bg-white shadow rounded-xl dark:bg-gray-800">
            <div>
                <div class="text-sm text-gray-500 dark:text-gray-400">Upcoming Bookings</div>
                <div class="mt-1 text-2xl font-bold text-primary-600">{{ $upcoming['count'] }}</div>
                <div class="text-sm text-primary-500">
                    ₱{{ number_format($upcoming['total_receivables'], 2) }} receivables
                </div>
            </div>
            <div class="flex items-center justify-center w-12 h-12 rounded-full bg-blue-500/10">
                <x-lucide-calendar-days class="w-6 h-6 text-primary-600" />
            </div>
        </div>

        <!-- Ongoing Bookings -->
        <div class="flex items-center justify-between p-6 bg-white shadow rounded-xl dark:bg-gray-800">
            <div>
                <div class="text-sm text-gray-500 dark:text-gray-400">Ongoing Bookings</div>
                <div class="mt-1 text-2xl font-bold text-yellow-600">{{ $ongoing['count'] }}</div>
                <div class="text-sm text-yellow-500">
                    ₱{{ number_format($ongoing['total_receivables'], 2) }} receivables
                </div>
            </div>
            <div class="flex items-center justify-center w-12 h-12 rounded-full bg-yellow-500/10">
                <x-lucide-clock class="w-6 h-6 text-yellow-600" />
            </div>
        </div>

        <!-- Finished Bookings -->
        <div class="flex items-center justify-between p-6 bg-white shadow rounded-xl dark:bg-gray-800">
            <div>
                <div class="text-sm text-gray-500 dark:text-gray-400">Finished Bookings</div>
                <div class="mt-1 text-2xl font-bold text-green-600">{{ $finished['count'] }}</div>
                <div class="text-sm text-green-500">
                    ₱{{ number_format($finished['total_receivables'], 2) }} receivables
                </div>
            </div>
            <div class="flex items-center justify-center w-12 h-12 rounded-full bg-green-500/10">
                <x-lucide-check-circle class="w-6 h-6 text-green-600" />
            </div>
        </div>
    </div>
</x-filament-widgets::widget>

{{-- Enhanced design --}}
{{-- <x-filament-widgets::widget>
    <div class="grid w-full grid-cols-1 gap-6 md:grid-cols-3">
        
        <div class="relative group p-6 bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 rounded-3xl transition-all duration-300 hover:border-primary-500/50">
            <div class="flex items-start justify-between">
                <div class="space-y-1">
                    <p class="text-xs  text-sm text-gray-400">Upcoming Bookings</p>
                    <div class="flex items-baseline gap-2">
                        <h2 class="text-3xl  text-gray-900 dark:text-white ">
                            {{ number_format($upcoming['count']) }}
                        </h2>
                        <span class="text-[10px] font-bold text-primary-500 text-sm">Reservations</span>
                    </div>
                </div>
                <div class="p-3 bg-primary-50 dark:bg-primary-500/10 rounded-2xl ring-1 ring-primary-100 dark:ring-primary-500/20">
                    <x-lucide-calendar-days class="w-6 h-6 text-primary-600 dark:text-primary-400" />
                </div>
            </div>
            
            <div class="mt-5 flex items-center justify-between gap-2">
                <div class="px-3 py-1 bg-primary-50/50 dark:bg-primary-500/5 rounded-xl border border-primary-100/50 dark:border-primary-500/10">
                    <p class="text-xs font-bold text-primary-600 dark:text-primary-400">
                        ₱{{ number_format($upcoming['total_receivables'], 2) }} <span class="text-[10px] opacity-70">to collect</span>
                    </p>
                </div>
            </div>
        </div>

        <div class="relative group p-6 bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 rounded-3xl transition-all duration-300 hover:border-amber-500/50">
            <div class="flex items-start justify-between">
                <div class="space-y-1">
                    <p class="text-xs  text-sm text-gray-400">Active / On-Trip</p>
                    <div class="flex items-baseline gap-2">
                        <h2 class="text-3xl  text-amber-600 dark:text-amber-500 ">
                            {{ number_format($ongoing['count']) }}
                        </h2>
                        <span class="text-[10px] font-bold text-amber-500 text-sm">Vehicles Out</span>
                    </div>
                </div>
                <div class="p-3 bg-amber-50 dark:bg-gray-900 rounded-2xl ring-1 ring-amber-100 dark:ring-amber-500/20">
                    <x-lucide-clock class="w-6 h-6 text-amber-600 dark:text-amber-500" />
                </div>
            </div>
            
            <div class="mt-5 flex items-center justify-between gap-2">
                <div class="px-3 py-1 bg-amber-50/50 dark:bg-transparent rounded-xl border border-amber-100/50 dark:border-amber-500">
                    <p class="text-xs font-bold text-amber-600 dark:text-amber-500">
                        ₱{{ number_format($ongoing['total_receivables'], 2) }} <span class="text-[10px] opacity-70">balance</span>
                    </p>
                </div>
            </div>
        </div>

        <div class="relative group p-6 bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 rounded-3xl transition-all duration-300 hover:border-emerald-500/50">
            <div class="flex items-start justify-between">
                <div class="space-y-1">
                    <p class="text-xs  text-sm text-gray-400">Completed Trips</p>
                    <div class="flex items-baseline gap-2">
                        <h2 class="text-3xl  text-emerald-600 dark:text-emerald-400 ">
                            {{ number_format($finished['count']) }}
                        </h2>
                        <span class="text-[10px] font-bold text-emerald-500 text-sm">Overall</span>
                    </div>
                </div>
                <div class="p-3 bg-emerald-50 dark:bg-gray-900 rounded-2xl ring-1 ring-emerald-100 dark:ring-emerald-50">
                    <x-lucide-check-circle class="w-6 h-6 text-emerald-600 dark:text-emerald-400" />
                </div>
            </div>
            
            <div class="mt-5 flex items-center justify-between gap-2">
                <div class="px-3 py-1 bg-emerald-50/50 dark:bg-transparent rounded-xl border border-emerald-100/50 dark:border-emerald-500/10">
                    <p class="text-xs font-bold text-emerald-600 dark:text-emerald-400">
                        ₱{{ number_format($finished['total_receivables'], 2) }} <span class="text-[10px] opacity-70">pending</span>
                    </p>
                </div>
            </div>
        </div>

    </div>
</x-filament-widgets::widget> --}}