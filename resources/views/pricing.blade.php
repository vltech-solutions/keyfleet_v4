<x-layouts.guest>
    <section class="px-4 py-20 sm:px-6 lg:px-8 bg-gray-50 dark:bg-gray-900 transition-colors duration-300">
        <div class="max-w-4xl mx-auto mb-16 text-center">
            <span class="inline-block px-4 py-1.5 text-xs font-bold tracking-wider text-indigo-700 dark:text-indigo-300 uppercase bg-indigo-100 dark:bg-indigo-900/30 rounded-full">Pricing</span>
            <h2 class="mt-4 text-4xl sm:text-5xl font-extrabold text-gray-900 dark:text-white">
                Simple, <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-blue-600 dark:from-indigo-400 dark:to-blue-400">Transparent</span> Pricing
            </h2>
            <p class="mt-4 text-lg text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">Choose a plan that fits your fleet. No hidden fees, no surprises.</p>
            
            <!-- Billing toggle -->
            {{-- <div class="mt-8 inline-flex items-center gap-4 p-1 bg-white dark:bg-gray-800 rounded-full shadow-sm border border-gray-200 dark:border-gray-700">
                <button class="px-6 py-2 text-sm font-semibold text-white bg-indigo-600 dark:bg-indigo-500 rounded-full">Monthly</button>
                <button class="px-6 py-2 text-sm font-semibold text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 transition">Annual</button>
            </div> --}}
        </div>

        <div class="grid grid-cols-1 gap-8 mx-auto sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 max-w-7xl">
            @foreach ($plans->take(4) as $plan)
                @php
                    $monthlyPrice = $plan->prices->firstWhere('billing_cycle', 'monthly');
                    $annualPrice = $plan->prices->firstWhere('billing_cycle', 'annually');
                    $isPopular = $plan->car_limit === 7;
                    
                    // Calculate monthly equivalent for annual
                    $monthlyEquivalent = $annualPrice ? round($annualPrice->price / 12, 2) : null;
                    $savings = 0;
                    if ($monthlyPrice && $annualPrice) {
                        $savings = ($monthlyPrice->price * 12) - $annualPrice->price;
                    }
                    
                    // Define features based on plan
                    $userLabel = ($plan->user_limit > 1) ? 'users' : 'user';
                    $features = [
                        'user_limit' => "{$plan->user_limit} ".$userLabel,
                        'vehicle_limit' => "Manage {$plan->car_limit} vehicles",
                        'advanced_reports' => true,
                        'premium_features' => true,
                        'support' => $plan->car_limit <= 3 ? 'Support' : 'Priority Support',
                        'basic_booking_form' => $plan->car_limit >= 7,
                        'smart_booking' => $plan->car_limit >= 15,
                        'customer_portal' => $plan->car_limit >= 15,
                        'partner_portal' => $plan->car_limit >= 15,
                        'file_retention' => $plan->car_limit >= 7 ? ($plan->car_limit >= 15 ? '6 months' : '4 months') : false,
                        'file_retention_details' => $plan->car_limit >= 7 ? [
                            'customer_requirements' => true,
                            'reservation_fees' => true,
                            'invoices' => true,
                            'receipts' => true,
                            'contracts' => $plan->car_limit >= 15,
                        ] : false,
                    ];
                @endphp

                <div class="relative p-6 transition bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-md rounded-2xl hover:shadow-xl hover:-translate-y-1 duration-300 {{ $isPopular ? 'border-indigo-500 dark:border-indigo-400 ring-2 ring-indigo-200 dark:ring-indigo-500/30 shadow-lg' : '' }} flex flex-col h-full">
                    @if ($isPopular)
                        <span class="absolute top-0 right-0 px-3 py-1 text-xs font-semibold text-white bg-indigo-600 dark:bg-indigo-500 rounded-br-2xl rounded-tl-xl">
                            Most Popular
                        </span>
                    @endif

                    <!-- Content wrapper -->
                    <div class="flex flex-col flex-1">
                        <h3 class="mb-1 text-2xl font-bold text-gray-800 dark:text-white">{{ $plan->name }}</h3>
                        <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">Manage up to {{ $plan->car_limit }} vehicles</p>

                        <div class="text-4xl font-extrabold text-indigo-600 dark:text-indigo-400">
                            @if ($monthlyPrice)
                                ₱{{ number_format($monthlyPrice->price, 0) }}
                                <span class="text-base font-normal text-gray-500 dark:text-gray-400">/mo</span>
                            @else
                                <span class="text-base font-normal text-gray-400 dark:text-gray-500">No monthly price</span>
                            @endif
                        </div>

                        <!-- Annual savings badge -->
                        @if($annualPrice && $savings > 0)
                            <div class="mt-1">
                                <span class="inline-block px-3 py-0.5 text-xs font-semibold text-green-700 dark:text-green-300 bg-green-100 dark:bg-green-900/30 rounded-full">
                                    Save ₱{{ number_format($savings, 0) }} annually
                                </span>
                            </div>
                        @endif

                        <!-- Billing Options -->
                        @if ($plan->prices->count())
                            <div class="mt-3 space-y-0.5 text-xs text-gray-500 dark:text-gray-400">
                                @if($threeMonthPrice = $plan->prices->firstWhere('billing_cycle', '3months'))
                                    <p>₱{{ number_format($threeMonthPrice->price, 0) }} / 3 months</p>
                                @endif
                                @if($sixMonthPrice = $plan->prices->firstWhere('billing_cycle', '6months'))
                                    <p>₱{{ number_format($sixMonthPrice->price, 0) }} / 6 months</p>
                                @endif
                                @if($annualPrice)
                                    <p class="font-medium text-indigo-600 dark:text-indigo-400">₱{{ number_format($annualPrice->price, 0) }} / annually</p>
                                @endif
                            </div>
                        @endif

                        <!-- Features -->
                        <ul class="mt-6 space-y-2.5 text-sm text-gray-600 dark:text-gray-300 flex-1">
                            <li class="flex items-start gap-2">
                                <svg class="w-5 h-5 text-green-500 dark:text-green-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span>{{ $features['user_limit'] }}</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <svg class="w-5 h-5 text-green-500 dark:text-green-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span>{{ $features['vehicle_limit'] }}</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <svg class="w-5 h-5 text-green-500 dark:text-green-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span>Advanced reports</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <svg class="w-5 h-5 text-green-500 dark:text-green-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span>All premium features</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <svg class="w-5 h-5 text-green-500 dark:text-green-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span>{{ $features['support'] }}</span>
                            </li>
                            
                            <!-- Basic Booking Form -->
                            <li class="flex items-start gap-2 {{ $features['basic_booking_form'] ? '' : 'text-gray-400 dark:text-gray-500' }}">
                                @if($features['basic_booking_form'])
                                    <svg class="w-5 h-5 text-green-500 dark:text-green-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                    </svg>
                                @else
                                    <svg class="w-5 h-5 text-gray-300 dark:text-gray-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                @endif
                                <span class="{{ $features['basic_booking_form'] ? '' : 'line-through' }}">Basic Booking Form</span>
                            </li>
                            
                            <!-- Smart Booking Website -->
                            <li class="flex items-start gap-2 {{ $features['smart_booking'] ? '' : 'text-gray-400 dark:text-gray-500' }}">
                                @if($features['smart_booking'])
                                    <svg class="w-5 h-5 text-green-500 dark:text-green-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                    </svg>
                                @else
                                    <svg class="w-5 h-5 text-gray-300 dark:text-gray-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                @endif
                                <span class="{{ $features['smart_booking'] ? '' : 'line-through' }}">Smart Booking Website</span>
                            </li>
                            
                            <!-- Customer Portal -->
                            <li class="flex items-start gap-2 {{ $features['customer_portal'] ? '' : 'text-gray-400 dark:text-gray-500' }}">
                                @if($features['customer_portal'])
                                    <svg class="w-5 h-5 text-green-500 dark:text-green-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                    </svg>
                                @else
                                    <svg class="w-5 h-5 text-gray-300 dark:text-gray-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                @endif
                                <span class="{{ $features['customer_portal'] ? '' : 'line-through' }}">Customer Portal</span>
                            </li>
                            
                            <!-- Partner's Portal -->
                            <li class="flex items-start gap-2 {{ $features['partner_portal'] ? '' : 'text-gray-400 dark:text-gray-500' }}">
                                @if($features['partner_portal'])
                                    <svg class="w-5 h-5 text-green-500 dark:text-green-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                    </svg>
                                @else
                                    <svg class="w-5 h-5 text-gray-300 dark:text-gray-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                @endif
                                <span class="{{ $features['partner_portal'] ? '' : 'line-through' }}">Partner's Portal</span>
                            </li>
                            
                            <!-- File Retention -->
                            <li class="flex items-start gap-2 {{ $features['file_retention'] ? '' : 'text-gray-400 dark:text-gray-500' }}">
                                @if($features['file_retention'])
                                    <svg class="w-5 h-5 text-green-500 dark:text-green-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span>File Retention ({{ $features['file_retention'] }})</span>
                                @else
                                    <svg class="w-5 h-5 text-gray-300 dark:text-gray-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                    <span class="line-through">File Retention</span>
                                @endif
                            </li>
                        </ul>

                        <!-- Button -->
                        <div class="mt-8">
                            <a href="{{ route('tenant.register') }}"
                                class="w-full inline-block bg-gradient-to-r from-indigo-600 to-blue-600 dark:from-indigo-500 dark:to-blue-500 text-white text-center py-3 px-4 rounded-xl font-semibold hover:from-indigo-700 hover:to-blue-700 dark:hover:from-indigo-600 dark:hover:to-blue-600 transition hover:shadow-lg {{ $isPopular ? 'shadow-lg shadow-indigo-600/25 dark:shadow-indigo-900/30' : '' }} active:scale-95">
                                @if($plan->car_limit == 3)
                                    Get Started
                                @elseif($plan->car_limit == 7)
                                    Choose Plan
                                @else
                                    Go Enterprise        
                                @endif
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Feature Comparison Table -->
        <div class="max-w-6xl mx-auto mt-16 overflow-x-auto">
            <div class="flex items-center justify-center gap-2 mb-6">
                <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white">Compare All Features</h3>
            </div>
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
                <table class="w-full text-sm border-collapse">
                    <thead>
                        <tr class="bg-gradient-to-r from-indigo-50 to-blue-50 dark:from-indigo-950/50 dark:to-blue-950/50">
                            <th class="p-4 text-left text-gray-700 dark:text-gray-300 font-semibold border-b dark:border-gray-700">Feature</th>
                            @foreach ($plans->take(4) as $plan)
                                <th class="p-4 text-center text-gray-700 dark:text-gray-300 font-semibold border-b dark:border-gray-700">{{ $plan->name }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="p-4 font-medium text-gray-700 dark:text-gray-300 border-b dark:border-gray-700">User Limit</td>
                            @foreach ($plans->take(4) as $plan)
                                <td class="p-4 text-center border-b dark:border-gray-700 font-bold text-gray-900 dark:text-white">{{ $plan->user_limit }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td class="p-4 font-medium text-gray-700 dark:text-gray-300 border-b dark:border-gray-700">Vehicle Limit</td>
                            @foreach ($plans->take(4) as $plan)
                                <td class="p-4 text-center border-b dark:border-gray-700 font-bold text-gray-900 dark:text-white">{{ $plan->car_limit }}</td>
                            @endforeach
                        </tr>
                        <tr class="bg-gray-50/50 dark:bg-gray-800/50">
                            <td class="p-4 font-medium text-gray-700 dark:text-gray-300 border-b dark:border-gray-700">Advanced Reports</td>
                            @foreach ($plans->take(4) as $plan)
                                <td class="p-4 text-center border-b dark:border-gray-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 mx-auto text-green-600 dark:text-green-400">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                    </svg>
                                </td>
                            @endforeach
                        </tr>
                        <tr>
                            <td class="p-4 font-medium text-gray-700 dark:text-gray-300 border-b dark:border-gray-700">Premium Features</td>
                            @foreach ($plans->take(4) as $plan)
                                <td class="p-4 text-center border-b dark:border-gray-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 mx-auto text-green-600 dark:text-green-400">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                    </svg>
                                </td>
                            @endforeach
                        </tr>
                        <tr class="bg-gray-50/50 dark:bg-gray-800/50">
                            <td class="p-4 font-medium text-gray-700 dark:text-gray-300 border-b dark:border-gray-700">Basic Booking Form</td>
                            @foreach ($plans->take(4) as $plan)
                                <td class="p-4 text-center border-b dark:border-gray-700">
                                    @if($plan->car_limit >= 7)
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 mx-auto text-green-600 dark:text-green-400">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                        </svg>
                                    @else
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 mx-auto text-red-500 dark:text-red-400">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                        </svg>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                        <tr>
                            <td class="p-4 font-medium text-gray-700 dark:text-gray-300 border-b dark:border-gray-700">Smart Booking Website</td>
                            @foreach ($plans->take(4) as $plan)
                                <td class="p-4 text-center border-b dark:border-gray-700">
                                    @if($plan->car_limit >= 15)
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 mx-auto text-green-600 dark:text-green-400">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                        </svg>
                                    @else
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 mx-auto text-red-500 dark:text-red-400">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                        </svg>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                        <tr class="bg-gray-50/50 dark:bg-gray-800/50">
                            <td class="p-4 font-medium text-gray-700 dark:text-gray-300 border-b dark:border-gray-700">Customer Portal</td>
                            @foreach ($plans->take(4) as $plan)
                                <td class="p-4 text-center border-b dark:border-gray-700">
                                    @if($plan->car_limit >= 15)
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 mx-auto text-green-600 dark:text-green-400">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                        </svg>
                                    @else
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 mx-auto text-red-500 dark:text-red-400">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                        </svg>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                        <tr>
                            <td class="p-4 font-medium text-gray-700 dark:text-gray-300 border-b dark:border-gray-700">Partner's Portal</td>
                            @foreach ($plans->take(4) as $plan)
                                <td class="p-4 text-center border-b dark:border-gray-700">
                                    @if($plan->car_limit >= 15)
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 mx-auto text-green-600 dark:text-green-400">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                        </svg>
                                    @else
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 mx-auto text-red-500 dark:text-red-400">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                        </svg>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                        <tr class="bg-gray-50/50 dark:bg-gray-800/50">
                            <td class="p-4 font-medium text-gray-700 dark:text-gray-300 border-b dark:border-gray-700">File Retention</td>
                            @foreach ($plans->take(4) as $plan)
                                <td class="p-4 text-center border-b dark:border-gray-700 font-medium">
                                    @if($plan->car_limit < 7)
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 mx-auto text-red-500 dark:text-red-400">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                        </svg>
                                    @elseif($plan->car_limit >= 15)
                                        6 months
                                    @else
                                        4 months
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                        <tr>
                            <td class="p-4 font-medium text-gray-700 dark:text-gray-300 border-b dark:border-gray-700">Files Retained</td>
                            @foreach ($plans->take(4) as $plan)
                                <td class="p-4 text-center border-b dark:border-gray-700 text-xs text-gray-600 dark:text-gray-400">
                                    @if($plan->car_limit < 7)
                                        <span class="text-gray-400 dark:text-gray-500">—</span>
                                    @elseif($plan->car_limit >= 15)
                                        Requirements, Fees, Invoices, Receipts, Contracts
                                    @else
                                        Requirements, Fees, Invoices, Receipts
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                        <tr class="bg-gray-50/50 dark:bg-gray-800/50">
                            <td class="p-4 font-medium text-gray-700 dark:text-gray-300 border-b dark:border-gray-700">Support Level</td>
                            @foreach ($plans->take(4) as $plan)
                                <td class="p-4 text-center border-b dark:border-gray-700 font-medium text-gray-700 dark:text-gray-300">
                                    {{ $plan->car_limit > 3 ? 'Priority Support' : 'Support' }}
                                </td>
                            @endforeach
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- FAQ or Note -->
        <div class="max-w-3xl mx-auto mt-12 text-center">
            <div class="p-6 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    <span class="font-semibold text-gray-700 dark:text-gray-300">✨ All plans include a 14-day free trial.</span> No credit card required.
                </p>
                <p class="mt-2 text-sm text-gray-400 dark:text-gray-500">
                    Need more than 35 vehicles? <a href="/contact-us" class="text-indigo-600 dark:text-indigo-400 hover:underline font-medium">Contact us</a> for a custom quote.
                </p>
            </div>
        </div>
    </section>
</x-layouts.guest>