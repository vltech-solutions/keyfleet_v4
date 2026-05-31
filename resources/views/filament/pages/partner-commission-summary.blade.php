<x-filament::page>
   <span class="text-xs"><b>Note:</b> Only fully paid bookings will count for company commission.</span>
   <form wire:submit.prevent>
      {{ $this->form }}
   </form>
   <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-3">
      
      <div class="flex items-center justify-between p-6 bg-white shadow rounded-xl dark:bg-gray-900 ring-1 ring-gray-950/5 dark:ring-white/10">
         <div>
               <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Revenue (Tie-Ups)</h3>
               <p class="mt-1 text-2xl font-bold text-primary-600 dark:text-primary-400">
                  ₱{{ number_format($tieUpRevenue, 2) }}
               </p>
               <p class="mt-1 text-xs text-gray-400">All bookings with partners</p>
         </div>
         <div class="flex items-center justify-center w-12 h-12 rounded-full bg-blue-500/10">
               <x-heroicon-o-currency-dollar class="w-7 h-7 text-primary-600 dark:text-primary-400" />
         </div>
      </div>

      <div class="flex items-center justify-between p-6 bg-white shadow rounded-xl dark:bg-gray-900 ring-1 ring-gray-950/5 dark:ring-white/10">
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

      <div class="flex items-center justify-between p-6 bg-white shadow rounded-xl dark:bg-gray-900 ring-1 ring-gray-950/5 dark:ring-white/10">
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
   <div>
      <h3 class="mb-4 text-lg font-semibold">Partners</h3>
      {{ $this->table }}
   </div>
   <div>
      <h3 class="mb-4 text-lg font-semibold">Car Breakdown</h3>

      <div x-load="visible" x-load-src="http://localhost:8080/js/filament/tables/components/table.js?v=3.3.26.0" x-data="table" class="fi-ta">
         <div class="overflow-hidden bg-white divide-y divide-gray-200 shadow-sm fi-ta-ctn rounded-xl ring-1 ring-gray-950/5 dark:divide-white/10 dark:bg-gray-900 dark:ring-white/10">
            <div x-bind:hidden="! (false || (selectedRecords.length &amp;&amp; 0))" x-show="false || (selectedRecords.length &amp;&amp; 0)" class="divide-y divide-gray-200 fi-ta-header-ctn dark:divide-white/10" hidden="true" style="display: none;">
               <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
               <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
               <div x-show="false || (selectedRecords.length &amp;&amp; 0)" class="flex items-center justify-between px-4 py-3 fi-ta-header-toolbar gap-x-4 sm:px-6" style="display: none;">
                  <div class="flex items-center shrink-0 gap-x-4">
                     <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                     <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                     <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                  </div>
                  <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
               </div>
            </div>
            <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
            <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
            <div class="fi-ta-content relative divide-y divide-gray-200 overflow-x-auto dark:divide-white/10 dark:border-t-white/10 !border-t-0">
               <!--[if BLOCK]><![endif]-->                
               <table class="w-full divide-y divide-gray-200 table-auto fi-ta-table text-start dark:divide-white/5">
                  <!--[if BLOCK]><![endif]-->        
                  <thead class="divide-y divide-gray-200 dark:divide-white/5">
                     <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                     <tr class="bg-gray-50 dark:bg-white/5">
                        <!--[if BLOCK]><![endif]-->                            <!--[if ENDBLOCK]><![endif]-->
                        <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                        <!--[if ENDBLOCK]><![endif]-->
                        <!--[if ENDBLOCK]><![endif]-->
                        <!--[if BLOCK]><![endif]-->                            
                        <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6 fi-table-header-cell-name" style=";">
                           <span class="flex items-center justify-start w-full group gap-x-1 whitespace-nowrap">
                              <span class="text-sm font-semibold fi-ta-header-cell-label text-gray-950 dark:text-white">
                              Car Image
                              </span>
                              <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                           </span>
                        </th>
                        <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6 fi-table-header-cell-cars-count" style=";">
                           <span class="flex items-center justify-start w-full group gap-x-1 whitespace-nowrap">
                              <span class="text-sm font-semibold fi-ta-header-cell-label text-gray-950 dark:text-white">
                              Cars Name
                              </span>
                              <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                           </span>
                        </th>
                        <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6 fi-table-header-cell-bookings-count" style=";">
                           <span class="flex items-center justify-start w-full group gap-x-1 whitespace-nowrap">
                              <span class="text-sm font-semibold fi-ta-header-cell-label text-gray-950 dark:text-white">
                              Booking Count
                              </span>
                              <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                           </span>
                        </th>
                        <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6 fi-table-header-cell-total-revenue" style=";">
                           <span class="flex items-center justify-start w-full group gap-x-1 whitespace-nowrap">
                              <span class="text-sm font-semibold fi-ta-header-cell-label text-gray-950 dark:text-white">
                              Ownership
                              </span>
                              <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                           </span>
                        </th>
                        <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6 fi-table-header-cell-partner-income" style=";">
                           <span class="flex items-center justify-start w-full group gap-x-1 whitespace-nowrap">
                              <span class="text-sm font-semibold fi-ta-header-cell-label text-gray-950 dark:text-white">
                              Total Revenue
                              </span>
                              <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                           </span>
                        </th>
                        <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6 fi-table-header-cell-company-cut" style=";">
                           <span class="flex items-center justify-start w-full group gap-x-1 whitespace-nowrap">
                              <span class="text-sm font-semibold fi-ta-header-cell-label text-gray-950 dark:text-white">
                              Partner's Income
                              </span>
                              <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                           </span>
                        </th>
                        <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6 fi-table-header-cell-company-cut" style=";">
                           <span class="flex items-center justify-start w-full group gap-x-1 whitespace-nowrap">
                              <span class="text-sm font-semibold fi-ta-header-cell-label text-gray-950 dark:text-white">
                              Company's Commission
                              </span>
                              <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                           </span>
                        </th>
                        <!--[if ENDBLOCK]><![endif]-->
                        <!--[if BLOCK]><![endif]-->                            <!--[if ENDBLOCK]><![endif]-->
                        <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
                        <!--[if ENDBLOCK]><![endif]-->
                        <!--[if ENDBLOCK]><![endif]-->
                     </tr>
                  </thead>
                  <!--[if ENDBLOCK]><![endif]-->
                  <tbody class="divide-y divide-gray-200 whitespace-nowrap dark:divide-white/5">
                    @foreach ($this->carBreakdown as $car)
                        <tr class="text-sm">
                            <td class="px-4 py-2">
                                <img src="{{ Storage::url($car->image) }}" alt="" class="object-contain w-14 h-14">
                            </td>
                            <td class="px-4 py-2 text-gray-900 dark:text-white">{{ $car->name }}</td>
                            <td class="px-4 py-2 text-gray-700 dark:text-white">{{ $car->bookings->count() }}</td>
                            <td class="px-4 py-2">
                                 {{-- <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-semibold ring-1
                                    {{ $car->partner
                                       ? 'ring-primary-500 text-blue-700 dark:ring-primary-300 dark:text-blue-300'
                                       : 'ring-gray-400 text-gray-700 dark:ring-gray-300 dark:text-gray-300' }}">
                                    {{ $car->partner?->name ?? 'Company-Owned' }}
                                 </span> --}}
                                 <div class="flex justify-start text-lg">
                                    <x-filament::badge color="primary">
                                          {{ $car->partner?->name }}
                                    </x-filament::badge>
                                 </div>

                            </td>
                            <td class="px-4 py-2 text-gray-800 dark:text-gray-200">₱{{ number_format($car->bookings->sum('paid_amount'), 2) }}</td>
                            <td class="px-4 py-2 font-bold text-green-600 dark:text-green-400">₱{{ number_format($car->bookings->sum('partner_commission'), 2) }}</td>
                            <td class="px-4 py-2 font-bold text-yellow-600 dark:text-yellow-400">₱{{ number_format($car->bookings->sum('company_earnings'), 2) }}</td>
                        </tr>
                        @endforeach
                  </tbody>
                  <!--[if BLOCK]><![endif]--><!--[if ENDBLOCK]><![endif]-->
               </table>
               <!--[if ENDBLOCK]><![endif]-->
            </div>
           
         </div>
      </div>
   </div>
</x-filament::page>