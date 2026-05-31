<x-filament-widgets::widget>
    <div class="grid w-full grid-cols-1 gap-6 md:grid-cols-3">

        <!-- Total Customers -->
        <div class="flex items-center justify-between p-6 bg-white shadow rounded-xl dark:bg-gray-800">
            <div>
                <div class="text-sm text-gray-500 dark:text-gray-400">Total Customers</div>
                <div class="mt-1 text-2xl font-bold text-blue-600">{{ number_format($totalCount) }}</div>
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    Registered in the system
                </div>
            </div>
            <div class="flex items-center justify-center w-12 h-12 rounded-full bg-blue-500/10">
                <x-lucide-users class="w-6 h-6 text-blue-600" />
            </div>
        </div>

        <!-- Top Customer -->
        <div class="flex items-center justify-between p-6 bg-white shadow rounded-xl dark:bg-gray-800">
            <div>
                <div class="text-sm text-gray-500 dark:text-gray-400">Top Customer</div>
                <div class="mt-1 text-2xl font-bold text-yellow-600 max-w-[300px] truncate overflow-hidden whitespace-nowrap">
                    {{ $topCustomer?->customer_name ?? 'No data' }}
                </div>
                @if ($topCustomer)
                    <div class="text-sm text-yellow-500">
                        Customer with most booking
                    </div>
                @endif
            </div>
            <div class="flex items-center justify-center w-12 h-12 rounded-full bg-yellow-500/10">
                <x-lucide-star class="w-6 h-6 text-yellow-600" />
            </div>
        </div>


        <!-- Total Receivable -->
        <div class="flex items-center justify-between p-6 bg-white shadow rounded-xl dark:bg-gray-800">
            <div>
                <div class="text-sm text-gray-500 dark:text-gray-400">Total Receivable</div>
                <div class="mt-1 text-2xl font-bold text-green-600">
                    ₱{{ number_format($totalReceivable, 2) }}
                </div>
                <div class="text-sm text-green-500">
                    Outstanding customer balances
                </div>
            </div>
            <div class="flex items-center justify-center w-12 h-12 rounded-full bg-green-500/10">
                <x-lucide-philippine-peso class="w-6 h-6 text-green-600" />
            </div>
        </div>

    </div>
</x-filament-widgets::widget>
