<x-filament::page>
    <!-- Note and Export Button -->
    <div class="flex items-center justify-between p-4 text-sm bg-blue-50 rounded-lg dark:bg-blue-900/20 text-blue-800 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
        <div class="flex items-center">
            <svg class="w-5 h-5 mr-3 text-blue-600 dark:text-blue-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2h-1V9a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            <span><strong>Note:</strong> Only fully paid bookings will count for company commission.</span>
        </div>
        <button wire:click="exportPdf" style="display:none"
                class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 dark:bg-blue-500 dark:hover:bg-blue-600 dark:focus:ring-blue-800 transition-colors duration-200">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Export PDF
        </button>
    </div>

    <!-- Filters Form -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10">
        <form wire:submit.prevent>
            {{ $this->form }}
        </form>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
        <!-- Total Revenue Card -->
        <div class="flex items-center justify-between p-6 bg-white shadow-sm rounded-xl dark:bg-gray-800 ring-1 ring-gray-950/5 dark:ring-white/10 hover:shadow-md transition-shadow duration-200">
            <div>
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Revenue (Tie-Ups)</h3>
                <p class="mt-1 text-2xl font-bold text-primary-600 dark:text-primary-400">
                    ₱{{ number_format($tieUpRevenue, 2) }}
                </p>
                <p class="mt-1 text-xs text-gray-400">All bookings with partners</p>
            </div>
            <div class="flex items-center justify-center w-12 h-12 rounded-full bg-primary-500/10">
                <x-heroicon-o-currency-dollar class="w-7 h-7 text-primary-600 dark:text-primary-400" />
            </div>
        </div>

        <!-- Partner Income Card -->
        <div class="flex items-center justify-between p-6 bg-white shadow-sm rounded-xl dark:bg-gray-800 ring-1 ring-gray-950/5 dark:ring-white/10 hover:shadow-md transition-shadow duration-200">
            <div>
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Partner Income</h3>
                <p class="mt-1 text-2xl font-bold text-green-600 dark:text-green-400">
                    ₱{{ number_format($partnerCommission, 2) }}
                </p>
                <p class="mt-1 text-xs text-gray-400">Total amount earned by partners</p>
            </div>
            <div class="flex items-center justify-center w-12 h-12 rounded-full bg-green-500/10">
                <x-heroicon-o-briefcase class="w-7 h-7 text-green-600 dark:text-green-400" />
            </div>
        </div>

        <!-- Company Commission Card -->
        <div class="flex items-center justify-between p-6 bg-white shadow-sm rounded-xl dark:bg-gray-800 ring-1 ring-gray-950/5 dark:ring-white/10 hover:shadow-md transition-shadow duration-200 border-l-4 border-yellow-500">
            <div>
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Company's Commission</h3>
                <p class="mt-1 text-2xl font-bold text-yellow-600 dark:text-yellow-400">
                    ₱{{ number_format($companyEarnings, 2) }}
                </p>
                <p class="mt-1 text-xs text-gray-400">Share from tie-up bookings</p>
            </div>
            <div class="flex items-center justify-center w-12 h-12 rounded-full bg-yellow-500/10">
                <x-heroicon-o-banknotes class="w-7 h-7 text-yellow-600 dark:text-yellow-400" />
            </div>
        </div>
    </div>

    <!-- Partners Section -->
    <div>
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Partner Summary</h3>
            <span class="text-sm text-gray-500 dark:text-gray-400">{{ $this->getTable()->getRecords()->count() }} partners</span>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 overflow-hidden">
            {{ $this->table }}
        </div>
    </div>

    <!-- Car Breakdown Section -->
      <div>
         @php
            $paginatedCars = $this->paginatedCarBreakdown;
         @endphp

         <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 overflow-hidden">
            <!-- Table Header with Stats -->
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
                  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                     <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Car Revenue Breakdown</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                              Showing {{ $paginatedCars->total > 0 ? (($paginatedCars->page - 1) * $paginatedCars->perPage + 1) : 0 }} 
                              to {{ min($paginatedCars->page * $paginatedCars->perPage, $paginatedCars->total) }} of {{ number_format($paginatedCars->total) }} vehicles
                        </p>
                     </div>
                     <div class="flex items-center gap-2">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Per page:</span>
                        <select wire:model.live="carPerPage" class="rounded-md border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm">
                              <option value="5">5</option>
                              <option value="10">10</option>
                              <option value="25">25</option>
                              <option value="50">50</option>
                        </select>
                     </div>
                  </div>
            </div>

            <!-- Table -->
            <div class="relative overflow-x-auto">
                  <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                     <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-300">
                        <tr>
                              <th scope="col" class="px-6 py-3">Car</th>
                              <th scope="col" class="px-6 py-3">Partner</th>
                              <th scope="col" class="px-6 py-3 text-center">Bookings</th>
                              <th scope="col" class="px-6 py-3 text-right">Total Revenue</th>
                              <th scope="col" class="px-6 py-3 text-right">Partner's Income</th>
                              <th scope="col" class="px-6 py-3 text-right">Company's Commission</th>
                        </tr>
                     </thead>
                     <tbody>
                        @forelse ($paginatedCars->items as $car)
                              <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150">
                                 <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                          @if($car->image)
                                             <img src="{{ Storage::url($car->image) }}" alt="{{ $car->name }}" class="object-contain w-16 h-16 rounded-lg">
                                          @else
                                             <div class="flex items-center justify-center w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-lg">
                                                <svg class="w-5 h-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                             </div>
                                          @endif
                                          <span class="font-medium text-gray-900 dark:text-white">{{ $car->name }}</span>
                                    </div>
                                 </td>
                                 <td class="px-6 py-4">
                                    @if($car->partner)
                                          <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-primary-100 text-primary-800 dark:bg-primary-900/30 dark:text-primary-400">
                                             {{ $car->partner->name }}
                                          </span>
                                    @else
                                          <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-700/50 dark:text-gray-300">
                                             Company-Owned
                                          </span>
                                    @endif
                                 </td>
                                 <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center justify-center px-3 py-1 text-sm font-semibold text-blue-600 bg-blue-100 rounded-full dark:bg-blue-900/30 dark:text-blue-400">
                                          {{ $car->bookings->count() }}
                                    </span>
                                 </td>
                                 <td class="px-6 py-4 text-right font-medium text-gray-900 dark:text-white">
                                    ₱{{ number_format($car->bookings->sum('paid_amount'), 2) }}
                                 </td>
                                 <td class="px-6 py-4 text-right font-semibold text-green-600 dark:text-green-400">
                                    ₱{{ number_format($car->bookings->sum('partner_commission'), 2) }}
                                 </td>
                                 <td class="px-6 py-4 text-right font-semibold text-yellow-600 dark:text-yellow-400">
                                    ₱{{ number_format($car->bookings->sum('company_earnings'), 2) }}
                                 </td>
                              </tr>
                        @empty
                              <tr>
                                 <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                          <svg class="w-12 h-12 text-gray-400 dark:text-gray-500 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H5a2 2 0 01-2-2V7a2 2 0 012-2h11.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V17a2 2 0 01-2 2z"/>
                                          </svg>
                                          <p class="text-gray-500 dark:text-gray-400">No car data available for the selected period.</p>
                                          <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">Try adjusting your filter criteria</p>
                                    </div>
                                 </td>
                              </tr>
                        @endforelse
                     </tbody>
                  </table>
            </div>

            <!-- Footer with Pagination -->
            @if($paginatedCars->total > 0)
                  <div class="flex items-center justify-between px-4 py-3 bg-white border-t border-gray-200 dark:bg-gray-800 dark:border-gray-700">
                     <div class="flex flex-col flex-1 gap-2 sm:flex-row sm:items-center sm:justify-between sm:gap-0">
                        <div class="text-sm text-gray-700 dark:text-gray-300">
                              <span class="font-semibold text-gray-900 dark:text-white">Showing</span>
                              <span class="font-semibold text-gray-900 dark:text-white">
                                 {{ $paginatedCars->total > 0 ? (($paginatedCars->page - 1) * $paginatedCars->perPage + 1) : 0 }}
                              </span>
                              <span class="font-semibold text-gray-900 dark:text-white">to {{ min($paginatedCars->page * $paginatedCars->perPage, $paginatedCars->total) }} of {{ number_format($paginatedCars->total) }} results</span>
                        </div>
                     </div>
                     @if($paginatedCars->total > $paginatedCars->perPage)
                        <nav class="inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                              @php
                                 $lastPage = $paginatedCars->lastPage;
                                 $page = $paginatedCars->page;
                                 $startPage = max($page - 2, 1);
                                 $endPage = min($page + 2, $lastPage);
                              @endphp
                              {{-- Previous button --}}
                              @if ($page > 1)
                                 <button wire:click="$set('carPage', {{ $page - 1 }})" class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-l-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed dark:text-gray-400 dark:bg-gray-800 dark:border-gray-700 dark:hover:bg-gray-700" aria-label="Previous">
                                    <svg class="w-5 h-5 transition duration-75" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                          <path fill-rule="evenodd" d="M11.78 5.22a.75.75 0 0 1 0 1.06L8.06 10l3.72 3.72a.75.75 0 1 1-1.06 1.06l-4.25-4.25a.75.75 0 0 1 0-1.06l4.25-4.25a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd" />
                                    </svg>
                                 </button>
                              @endif
                              {{-- Numbered page buttons --}}
                              @for ($i = $startPage; $i <= $endPage; $i++)
                                 <button wire:click="$set('carPage', {{ $i }})" class="relative inline-flex items-center px-4 py-2 border text-sm font-medium
                                    {{ $i === $page
                                          ? 'text-primary-600 bg-gray-100 border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-primary-400'
                                          : 'text-gray-700 bg-white border-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700' }}
                                    {{ $i === $startPage && $page === 1 ? 'rounded-l-md' : '' }}
                                    {{ $i === $endPage && $page >= $lastPage ? 'rounded-r-md' : '' }}" aria-current="{{ $i === $page ? 'page' : false }}">
                                    {{ $i }}
                                 </button>
                              @endfor
                              {{-- Next button --}}
                              @if ($page < $lastPage)
                                 <button wire:click="$set('carPage', {{ $page + 1 }})" class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-r-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed dark:text-gray-400 dark:bg-gray-800 dark:border-gray-700 dark:hover:bg-gray-700" aria-label="Next">
                                    <svg class="w-5 h-5 text-gray-400 transition duration-75 fi-pagination-item-icon group-hover/button:text-gray-500 dark:text-gray-500 dark:group-hover/button:text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                                          <path fill-rule="evenodd" d="M8.22 5.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 0 1-1.06-1.06L11.94 10 8.22 6.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"></path>
                                    </svg>
                                 </button>
                              @endif
                        </nav>
                     @endif
                  </div>
            @endif
         </div>
      </div>

    <!-- Summary Footer -->
    @if($this->carBreakdown->count() > 0)
    <div class="p-4 bg-white dark:bg-gray-800 rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10">
        <div class="flex flex-wrap items-center justify-between gap-4 text-sm">
            <div class="flex items-center gap-4">
                <span class="text-gray-500 dark:text-gray-400">Total Cars:</span>
                <span class="font-semibold text-gray-900 dark:text-white">{{ $this->carBreakdown->count() }}</span>
            </div>
            <div class="flex items-center gap-4">
                <span class="text-gray-500 dark:text-gray-400">Total Bookings:</span>
                <span class="font-semibold text-gray-900 dark:text-white">{{ $this->carBreakdown->sum(fn($car) => $car->bookings->count()) }}</span>
            </div>
            <div class="flex items-center gap-4">
                <span class="text-gray-500 dark:text-gray-400">Total Revenue:</span>
                <span class="font-semibold text-gray-900 dark:text-white">₱{{ number_format($this->carBreakdown->sum(fn($car) => $car->bookings->sum('paid_amount')), 2) }}</span>
            </div>
            <div class="flex items-center gap-4">
                <span class="text-gray-500 dark:text-gray-400">Total Partner Income:</span>
                <span class="font-semibold text-green-600 dark:text-green-400">₱{{ number_format($this->carBreakdown->sum(fn($car) => $car->bookings->sum('partner_commission')), 2) }}</span>
            </div>
            <div class="flex items-center gap-4">
                <span class="text-gray-500 dark:text-gray-400">Total Company Commission:</span>
                <span class="font-semibold text-yellow-600 dark:text-yellow-400">₱{{ number_format($this->carBreakdown->sum(fn($car) => $car->bookings->sum('company_earnings')), 2) }}</span>
            </div>
        </div>
    </div>
    @endif
</x-filament::page>