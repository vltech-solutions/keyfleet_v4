@php
    $tenant = filament()->getTenant()?->slug;
    
    $groups = [
        'Main' => [
            ['name' => 'Dashboard', 'icon' => 'heroicon-o-home', 'url' => "/app/{$tenant}"],
            ['name' => 'Calendar', 'icon' => 'heroicon-o-calendar', 'url' => "/app/{$tenant}/calendar"],
            ['name' => 'Contract', 'icon' => 'heroicon-o-document-text', 'url' => "/app/{$tenant}/contract-builder"],
        ],
        'Transactions' => [
            ['name' => 'Bookings', 'icon' => 'heroicon-o-rectangle-stack', 'url' => "/app/{$tenant}/bookings"],
            ['name' => 'Expenses', 'icon' => 'heroicon-o-credit-card', 'url' => "/app/{$tenant}/expenses"],
            ['name' => 'Funds', 'icon' => 'heroicon-o-banknotes', 'url' => "/app/{$tenant}/fund-types"],
        ],
        'Fleet Management' => [
            ['name' => 'Cars', 'icon' => 'heroicon-o-truck', 'url' => "/app/{$tenant}/cars"],
            ['name' => 'Partners', 'icon' => 'heroicon-o-building-office-2', 'url' => "/app/{$tenant}/partners"],
            ['name' => 'Sources', 'icon' => 'heroicon-o-computer-desktop', 'url' => "/app/{$tenant}/sources"],
        ],
        'Reports' => [
            ['name' => 'Utilization', 'icon' => 'heroicon-o-chart-bar', 'url' => "/app/{$tenant}/fleet-utilization-report"],
            ['name' => 'Income Flow', 'icon' => 'heroicon-o-arrows-right-left', 'url' => "/app/{$tenant}/income-flow-report"],
            ['name' => 'Revenue', 'icon' => 'heroicon-o-presentation-chart-line', 'url' => "/app/{$tenant}/vehicle-revenue"],
            ['name' => 'Commission', 'icon' => 'heroicon-o-document-chart-bar', 'url' => "/app/{$tenant}/partner-commission-summary"],
        ],

		'Account & Billing' => [
            ['name' => 'Company Settings', 'icon' => 'heroicon-o-adjustments-horizontal', 'url' => "/app/{$tenant}/profile"],
            ['name' => 'Profile', 'icon' => 'heroicon-o-user-circle', 'url' => "/app/{$tenant}/user-profile"],
            ['name' => 'Subscription', 'icon' => 'heroicon-o-credit-card', 'url' =>  "/app/{$tenant}/subscription-overview"],
            ['name' => 'Referral', 'icon' => 'heroicon-o-trophy', 'url' => "/app/{$tenant}/referral-dashboard"],
        ],
    ];
@endphp

<div class="space-y-8 p-6 pb-54">
    @foreach($groups as $groupName => $items)
        <div>
            <h2 class="text-xs font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-4 px-1">
                {{ $groupName }}
            </h2>
            <div class="grid grid-cols-3 gap-4">
                @foreach($items as $item)
                    <a href="{{ url($item['url']) }}" 
                       class="flex flex-col items-center justify-center p-4 bg-white dark:bg-gray-900 rounded-3xl shadow-md active:scale-95 transition-all">
                        <div class="w-12 h-12 flex items-center justify-center bg-primary-50 dark:bg-primary-400/10 rounded-2xl mb-2 text-primary-600 dark:text-primary-400">
                            @svg($item['icon'], 'w-6 h-6')
                        </div>
                        <span class="text-[10px] font-bold text-center text-gray-700 dark:text-gray-300 leading-tight">
                            {{ $item['name'] }}
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    @endforeach
</div>