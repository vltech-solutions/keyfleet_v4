<x-filament-widgets::widget>
    
    <h1 class="mt-6 mb-6 text-lg font-semibold text-gray-700 dark:text-gray-200">Financial Summary</h1>
    <div class="mt-4 mb-4">
        <div class="inline-flex p-1 bg-gray-100 rounded-xl dark:bg-gray-700">
            <button 
                wire:click="$set('filter', 'all')" 
                class="px-4 py-2 rounded-xl text-sm font-medium transition 
                       {{ $filter === 'all' ? 'bg-white dark:bg-gray-800 shadow text-primary-600' : 'text-gray-600 dark:text-gray-300' }}">
                All
            </button>
            <button 
                wire:click="$set('filter', 'current_year')" 
                class="px-4 py-2 rounded-xl text-sm font-medium transition 
                       {{ $filter === 'current_year' ? 'bg-white dark:bg-gray-800 shadow text-primary-600' : 'text-gray-600 dark:text-gray-300' }}">
                {{ now()->year }} <span class="text-xs text-gray-500">(Current Year)</span>
            </button>
            <button 
                wire:click="$set('filter', 'current_month')" 
                class="px-4 py-2 rounded-xl text-sm font-medium transition 
                       {{ $filter === 'current_month' ? 'bg-white dark:bg-gray-800 shadow text-primary-600' : 'text-gray-600 dark:text-gray-300' }}">
                {{ now()->monthName }} <span class="text-xs text-gray-500">(Current Month)</span>
            </button>
        </div>
    </div>

    <div class="grid w-full grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-2">
        <!-- Total Bookings -->
        <div class="flex items-center justify-between p-6 bg-white shadow rounded-xl dark:bg-gray-800">
            <div>
                <div class="text-sm text-gray-500 dark:text-gray-400">Total Bookings</div>
                <div class="mt-1 text-2xl font-bold text-orange-600">{{ $this->getTotalBookingsProperty() }}</div>
            </div>
            <div class="flex items-center justify-center w-12 h-12 rounded-full bg-orange-500/10">
                <x-lucide-book-open class="w-6 h-6 text-orange-500" />
            </div>
        </div>

        <!-- Total Revenue -->
        <div class="flex items-center justify-between p-6 bg-white shadow rounded-xl dark:bg-gray-800">
            <div>
                <div class="text-sm text-gray-500 dark:text-gray-400">Total Revenue</div>
                <div class="mt-1 text-2xl font-bold text-green-500">{{ number_format($this->getTotalRevenueProperty(), 2) }}</div>
            </div>
            <div class="flex items-center justify-center w-12 h-12 rounded-full bg-green-500/10">
                <x-lucide-philippine-peso class="w-6 h-6 text-green-500" />
            </div>
        </div>

        <!-- Total Expenses -->
        <div class="flex items-center justify-between p-6 bg-white shadow rounded-xl dark:bg-gray-800">
            <div>
                <div class="text-sm text-gray-500 dark:text-gray-400">Total Expenses</div>
                <div class="mt-1 text-2xl font-bold text-red-500">{{ number_format($this->getTotalExpensesProperty(), 2) }}</div>
            </div>
            <div class="flex items-center justify-center w-12 h-12 rounded-full bg-red-500/10">
                <x-lucide-file-text class="w-6 h-6 text-red-500" />
            </div>
        </div>

        <!-- Total Profit -->
        <div class="flex items-center justify-between p-6 bg-white shadow rounded-xl dark:bg-gray-800">
            <div>
                <div class="text-sm text-gray-500 dark:text-gray-400">Total Profit</div>
                <div class="mt-1 text-2xl font-bold text-blue-500">{{ number_format($this->getTotalProfitProperty(), 2) }}</div>
            </div>
            <div class="flex items-center justify-center w-12 h-12 rounded-full bg-blue-500/10">
                <x-lucide-bar-chart-2 class="w-6 h-6 text-blue-500" />
            </div>
        </div>
    </div>
</x-filament-widgets::widget>
