<x-filament::page>
    <div class="space-y-8">
        {{-- Top Section: Welcome & Referral Link --}}
        <div class="relative overflow-hidden bg-white  dark:bg-gray-900  rounded-3xl shadow-md">
            {{-- Decorative Background Element --}}
            <div class="absolute top-0 right-0 w-32 h-32 -mr-16 -mt-16 transition-transform duration-500 rounded-full bg-primary-500/10 blur-3xl group-hover:scale-110"></div>
            
            <div class="relative p-6 md:p-8">
                <div class="flex flex-col items-start justify-between gap-6 md:flex-row md:items-center">
                    <div class="space-y-2">
                        <h2 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white md:text-3xl">
                            Invite friends, get <span class="text-primary-600 dark:text-primary-400">free days</span>
                        </h2>
                        <p class="max-w-md text-gray-500 dark:text-gray-400">
                            Grow your fleet network. For every company that joins and subscribes, we'll extend your plan.
                        </p>
                    </div>

                    @if ($this->getStats()['hasActivePaidSubscription'])
                    <div x-data="{ 
                        copied: false,
                        url: '{{ url('/register-company?ref=' . $this->getStats()['referral_code']) }}'
                    }" class="w-full md:w-auto">
                        <div class="flex flex-col gap-3 p-2 bg-gray-50 dark:bg-gray-800/50 rounded-2xl ring-1 ring-gray-200 dark:ring-gray-700 md:flex-row md:items-center">
                            <div class="px-4 py-2 font-mono text-sm font-medium text-gray-600 dark:text-gray-300">
                                {{ $this->getStats()['referral_code'] }}
                            </div>
                            <button 
                                @click="
                                    navigator.clipboard.writeText(url);
                                    copied = true;
                                    setTimeout(() => copied = false, 2000);
                                "
                                class="flex items-center justify-center gap-2 px-6 py-2.5 text-sm font-bold text-white transition-all shadow-lg rounded-xl bg-primary-600 hover:bg-primary-500 active:scale-95 shadow-primary-500/20"
                            >
                                <x-heroicon-s-share x-show="!copied" class="w-4 h-4" />
                                <x-heroicon-s-check x-show="copied" class="w-4 h-4" />
                                <span x-text="copied ? 'Copied Link!' : 'Copy Invite Link'"></span>
                            </button>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Main Content Grid --}}
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-12">
            
            {{-- Left: Stats & Info (8/12 columns) --}}
            <div class="space-y-8 lg:col-span-8">
                
                @if ($this->getStats()['hasActivePaidSubscription'])
                    {{-- Stats Grid --}}
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        @foreach([
                            ['label' => 'Total Referrals', 'value' => $this->getStats()['total_referrals'], 'icon' => 'heroicon-o-user-group', 'color' => 'primary'],
                            ['label' => 'Success Rate', 'value' => ($this->getStats()['total_referrals'] > 0 ? round(($this->getStats()['converted'] / $this->getStats()['total_referrals']) * 100) : 0) . '%', 'icon' => 'heroicon-o-arrow-trending-up', 'color' => 'success'],
                            ['label' => 'Days Earned', 'value' => $this->getStats()['total_reward_days'], 'icon' => 'heroicon-o-sparkles', 'color' => 'warning'],
                        ] as $stat)
                            <div class="relative p-6 transition-all duration-300 bg-white  shadow-md dark:bg-gray-900 rounded-3xl hover:shadow-md group">
                                <div class="flex flex-col gap-4">
                                    <div class="flex items-center justify-between">
                                        <div class="p-2 rounded-lg bg-{{ $stat['color'] }}-50 dark:bg-{{ $stat['color'] }}-500/10 text-{{ $stat['color'] }}-600 dark:text-{{ $stat['color'] }}-400">
                                            <x-dynamic-component :component="$stat['icon']" class="w-6 h-6" />
                                        </div>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $stat['label'] }}</p>
                                        <h3 class="text-3xl font-black tracking-tight text-gray-900 dark:text-white">{{ $stat['value'] }}</h3>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Reward Tiers (Visual Cards) --}}
                    <div class="space-y-4">
                        <h4 class="text-sm font-bold tracking-widest text-gray-400 uppercase">Available Rewards</h4>
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            @foreach ($this->plans as $plan)
                                <div class="flex items-center justify-between p-5 bg-white shadow-md dark:bg-gray-900  rounded-2xl group hover:border-primary-500 transition-colors">
                                    <div class="flex items-center gap-4">
                                        <div class="flex items-center justify-center w-12 h-12 text-xl font-bold rounded-xl bg-gray-50 dark:bg-gray-800 text-primary-600">
                                            {{ substr($plan->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <p class="font-bold text-gray-900 dark:text-white">{{ $plan->name }}</p>
                                            <p class="text-xs text-gray-500">Subscription Reward</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-lg font-black text-primary-600 dark:text-primary-400">+{{ $plan->referral_reward_days }}</span>
                                        <p class="text-[10px] font-bold uppercase text-gray-400">Days Free</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    {{-- Empty State --}}
                    <div class="flex flex-col items-center justify-center p-12 text-center bg-gray-50 dark:bg-gray-800/20 rounded-3xl border-2 border-dashed border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-center w-20 h-20 mb-6 bg-white rounded-full shadow-xl dark:bg-gray-900">
                            <x-heroicon-o-no-symbol class="w-10 h-10 text-danger-500" />
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">Subscription Required</h3>
                        <p class="max-w-sm mt-2 text-gray-500 dark:text-gray-400">You must have an active paid subscription to participate in the referral program and earn free credits.</p>
                        <div class="mt-8">
                            <x-filament::button color="primary" size="lg" icon="heroicon-m-bolt" class="shadow-xl">
                                Upgrade Plan
                            </x-filament::button>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Right: Guidelines (4/12 columns) --}}
            <div class="lg:col-span-4">
                <div class="sticky top-8 p-8 bg-primary-600 rounded-3xl shadow-2xl shadow-primary-500/20 text-white">
                    <h4 class="flex items-center gap-2 mb-6 text-lg font-bold">
                        <x-heroicon-s-information-circle class="w-6 h-6" />
                        How it works
                    </h4>
                    <ul class="space-y-6">
                        <li class="flex gap-4">
                            <span class="flex items-center justify-center w-6 h-6 text-xs font-bold text-primary-600 bg-white rounded-full shrink-0">1</span>
                            <p class="text-sm leading-relaxed text-primary-50">Share your link with colleagues or other fleet owners.</p>
                        </li>
                        <li class="flex gap-4">
                            <span class="flex items-center justify-center w-6 h-6 text-xs font-bold text-primary-600 bg-white rounded-full shrink-0">2</span>
                            <p class="text-sm leading-relaxed text-primary-50">They sign up and purchase any paid plan.</p>
                        </li>
                        <li class="flex gap-4">
                            <span class="flex items-center justify-center w-6 h-6 text-xs font-bold text-primary-600 bg-white rounded-full shrink-0">3</span>
                            <p class="text-sm leading-relaxed text-primary-50 font-bold">Boom! Free days are instantly added to your account.</p>
                        </li>
                    </ul>

                    <div class="mt-8 pt-8 border-t border-primary-500/30">
                        <p class="text-[10px] font-bold uppercase tracking-widest text-primary-200 mb-2">Notice</p>
                        <p class="text-xs text-primary-100 leading-relaxed italic">
                            Rewards are valid for new company signups only. Limit of one reward per referred organization.
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-filament::page>