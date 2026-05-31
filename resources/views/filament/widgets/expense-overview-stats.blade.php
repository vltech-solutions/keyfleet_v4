<x-filament-widgets::widget>
    <div class="grid w-full grid-cols-1 gap-6 md:grid-cols-3">

        <div class="flex items-center justify-between p-6 bg-white shadow rounded-xl dark:bg-gray-800">
            <div>
                <div class="text-sm text-gray-500 dark:text-gray-400">Total Expenses</div>
                <div class="mt-1 text-2xl font-bold text-red-600">
                    ₱{{ number_format($totalExpenses, 2) }}
                </div>
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    Total cash outflow
                </div>
            </div>
            <div class="flex items-center justify-center w-12 h-12 rounded-full bg-red-500/10">
                <x-heroicon-o-banknotes class="w-6 h-6 text-red-600" />
            </div>
        </div>

        <div class="flex items-center justify-between p-6 bg-white shadow rounded-xl dark:bg-gray-800">
            <div>
                <div class="text-sm text-gray-500 dark:text-gray-400">Car Related</div>
                <div class="mt-1 text-2xl font-bold text-blue-600">
                    ₱{{ number_format($fleetExpenses, 2) }}
                </div>
                <div class="text-sm text-blue-500">
                    Fleet operational costs
                </div>
            </div>
            <div class="flex items-center justify-center w-12 h-12 rounded-full bg-blue-500/10">
                <x-heroicon-o-truck class="w-6 h-6 text-blue-600" />
            </div>
        </div>

        <div class="flex items-center justify-between p-6 bg-white shadow rounded-xl dark:bg-gray-800">
            <div>
                <div class="text-sm text-gray-500 dark:text-gray-400">General Expense</div>
                <div class="mt-1 text-2xl font-bold text-green-600">
                    ₱{{ number_format($generalExpenses, 2) }}
                </div>
                <div class="text-sm text-green-500">
                    Overhead and others
                </div>
            </div>
            <div class="flex items-center justify-center w-12 h-12 rounded-full bg-green-500/10">
                <x-heroicon-o-user-group class="w-6 h-6 text-green-600" />
            </div>
        </div>

    </div>
</x-filament-widgets::widget>