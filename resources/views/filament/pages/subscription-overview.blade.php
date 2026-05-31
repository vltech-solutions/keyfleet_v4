<x-filament::page>
    <div class="space-y-8">
        @php
            $company = \App\Models\Company::find(\Filament\Facades\Filament::getTenant()?->id);
            $subscription = $company->subscription;
            $plan = $subscription?->planPrice?->plan;
            $carCount = $company->cars()->count();
            $carLimit = $plan->car_limit ?? 0;
            $usagePercentage = $carLimit > 0 ? min(100, ($carCount / $carLimit) * 100) : 0;
            $isActive = $subscription && now()->lte($subscription->ends_at);
        @endphp

        @if ($subscription)
            <div class="grid grid-cols-1 gap-8 lg:grid-cols-3 items-stretch">
                
                {{-- Main Subscription Card (Left 2/3) --}}
                <div class="lg:col-span-2">
                    <x-filament::card class="h-full relative overflow-hidden border-none shadow-xl ring-1 ring-gray-200 dark:ring-white/10">
                        {{-- Subtle Status Banner --}}
                        <div class="absolute top-0 right-0 px-6 py-2 rounded-bl-2xl {{ $isActive ? 'bg-primary-500/10 text-primary-700 dark:text-primary-400' : 'bg-danger-500/10 text-danger-700 dark:text-danger-400' }} text-xs font-bold uppercase tracking-widest">
                            {{ $isActive ? '● Active' : '○ Expired' }}
                        </div>

                        <div class="p-2 flex flex-col h-full">
                            <div>
                                <h2 class="text-sm font-bold tracking-widest text-gray-400 uppercase">Your Plan</h2>
                                <div class="flex items-end gap-3 mt-1">
                                    <h3 class="text-4xl font-black text-gray-900 dark:text-white">{{ $plan->name ?? 'N/A' }}</h3>
                                    <span class="mb-1 text-lg font-medium text-gray-500 dark:text-gray-400">/ {{ ucfirst($subscription->planPrice->billing_cycle) }}</span>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-8 mt-10 md:grid-cols-2 items-stretch flex-grow">
                                {{-- Dates Section --}}
                                <div class="space-y-4 flex flex-col justify-center">
                                    <div class="flex items-center gap-4 group">
                                        <div class="p-2 rounded-lg bg-gray-50 dark:bg-gray-800 ring-1 ring-gray-200 dark:ring-white/5 group-hover:ring-primary-500 transition-all">
                                            <x-heroicon-o-calendar-days class="w-5 h-5 text-primary-500" />
                                        </div>
                                        <div>
                                            <p class="text-xs font-bold text-gray-400 uppercase tracking-tighter">Started On</p>
                                            <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">
                                                {{ \Carbon\Carbon::parse($subscription->starts_at)->format('M d, Y') }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-4 group">
                                        <div class="p-2 rounded-lg bg-gray-50 dark:bg-gray-800 ring-1 ring-gray-200 dark:ring-white/5 group-hover:ring-danger-500 transition-all">
                                            <x-heroicon-o-arrow-path class="w-5 h-5 text-danger-500" />
                                        </div>
                                        <div>
                                            <p class="text-xs font-bold text-gray-400 uppercase tracking-tighter">Next Renewal</p>
                                            <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">
                                                {{ \Carbon\Carbon::parse($subscription->ends_at)->format('M d, Y') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                {{-- Features/Quick Stats --}}
                                <div class="p-4 rounded-2xl bg-gray-50 dark:bg-gray-800  flex flex-col justify-center">
                                    <h4 class="mb-4 text-xs font-bold text-gray-500 uppercase tracking-widest">Plan Insights</h4>
                                    <div class="space-y-3">
                                        <div class="flex items-center justify-between text-sm">
                                            <span class="text-gray-500 dark:text-gray-400">Billing Currency</span>
                                            <span class="font-bold text-gray-900 dark:text-white">PHP</span>
                                        </div>
                                        <div class="flex items-center justify-between text-sm">
                                            <span class="text-gray-500 dark:text-gray-400">Payment Status</span>
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-primary-500 text-white">PAID</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </x-filament::card>
                </div>

                {{-- Usage/Car Limit Card (Right 1/3) --}}
                <div class="lg:col-span-1">
                    <x-filament::card class="h-full border-none shadow-xl ring-1 ring-gray-200 dark:ring-white/10">
                        <div class="flex flex-col h-full">
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="text-sm font-bold text-gray-400 uppercase tracking-widest">Fleet Usage</h3>
                                <x-heroicon-s-truck class="w-5 h-5 text-primary-500" />
                            </div>

                            <div class="flex flex-col items-center justify-center flex-grow py-4">
                                <div class="text-center">
                                    <span class="text-5xl font-black text-gray-900 dark:text-white">{{ $carCount }}</span>
                                    <span class="block text-xs font-bold text-gray-400 uppercase">Cars Registered</span>
                                </div>
                            </div>

                            <div class="mt-auto space-y-2">
                                <div class="flex items-center justify-between text-xs font-bold uppercase tracking-tighter">
                                    <span class="text-gray-500">Utilization</span>
                                    <span class="{{ $usagePercentage > 90 ? 'text-danger-500' : 'text-primary-500' }}">
                                        {{ $carCount }} / {{ $carLimit }}
                                    </span>
                                </div>
                                {{-- Progress Bar --}}
                                <div class="w-full h-3 overflow-hidden bg-gray-100 rounded-full dark:bg-gray-800">
                                    <div 
                                        class="h-full transition-all duration-1000 bg-primary-500 rounded-full"
                                        style="width: {{ $usagePercentage }}%"
                                    ></div>
                                </div>
                                <p class="text-[10px] text-gray-400 italic mt-1 text-right">
                                    {{ $carLimit - $carCount }} slots remaining
                                </p>
                            </div>
                        </div>
                    </x-filament::card>
                </div>
            </div>
        @else
            {{-- Empty State --}}
            <div class="flex flex-col items-center justify-center p-12 text-center bg-gray-50 dark:bg-gray-800/20 rounded-3xl border-2 border-dashed border-gray-200 dark:border-gray-700">
                <x-heroicon-o-credit-card class="w-16 h-16 mb-4 text-gray-300 dark:text-gray-600" />
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">No active subscription found</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Choose a plan to start managing your fleet efficiently.</p>
                <x-filament::button color="primary" class="mt-6" icon="heroicon-m-sparkles">
                    Browse Plans
                </x-filament::button>
            </div>
        @endif

        {{-- Table Section --}}
        <div class="space-y-4">
            <div class="flex items-center gap-2 px-2">
                <div class="p-1.5 bg-gray-100 dark:bg-gray-800 rounded-lg">
                    <x-heroicon-m-clock class="w-4 h-4 text-gray-500 dark:text-gray-400" />
                </div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Subscription Billing History</h3>
            </div>
            
            <div class="overflow-hidden bg-white border border-gray-200 dark:border-gray-500 shadow-xl rounded-2xl dark:bg-gray-900 ">
                {{ $this->table }}
            </div>
        </div>
    </div>
</x-filament::page>