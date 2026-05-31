<x-filament::page>
    @php
        function bladePlaceholder($key) {
            return '{{' . $key . '}}';
        }

        $placeholders = [
            'date_today' => 'Date Today',
            'created_at' => 'Date Created',
            'start_datetime' => 'Start Date & Time',
            'end_datetime' => 'End Date & Time',
            'renter_name' => 'Renter Name',
            'renter_address' => 'Renter\'s Address',
            'contact_number' => 'Contact Number',
            'destination' => 'Destination',
            'other_drivers' => 'Other Drivers',
            'delivery_address' => 'Delivery Address',
            'return_address' => 'Return Address',
            'daily_rate' => 'Daily Rate',
            'days_rented' => 'Days Rented',
            'extend_hours' => 'Extend Hours',
            'extend_due' => 'Extend Due',
            'total_rent_due' => 'Total Rent Due',
            'delivery_fee' => 'Delivery Fee',
            'discount' => 'Discount',
            'total_due' => 'Total Due',
            'paid_amount' => 'Paid Amount',
            'balance' => 'Balance',
            'fuel_charge' => 'Fuel Charge',
            'out_of_bounds' => 'Out of Bounds',
            'rfid' => 'RFID',
            'damages' => 'Damages',
            'carwash_fee' => 'Carwash Fee',
            'driver_fee' => 'Driver Fee',
            'security_deposit' => 'Security Deposit',
            'remarks' => 'Remarks',
            'car.brand' => 'Car Brand',
            'car.name' => 'Car Name',
            'car.model' => 'Car Model',
            'car.plate_number' => 'Car Plate Number',
            'car.year' => 'Car Year',
            'car.color' => 'Car Color',
            'car.fuel_type' => 'Car Fuel Type',
            'car.coding' => 'Car Coding Day',
        ];
    @endphp

    <div class="flex flex-col gap-8 md:flex-row" x-data="{ 
        search: '',
        items: {{ json_encode($placeholders) }},
        getPlaceholder(key) {
            return '@{{' + key + '}}';
        }
    }">
        <div class="w-full md:w-1/3 lg:w-1/4">
            <div class="sticky top-24 p-5  rounded-2xl bg-white/50 backdrop-blur-sm shadow-md dark:bg-gray-900 dark:border-gray-800 transition-all">
                
                <div class="mb-4">
                    <h4 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <x-heroicon-o-variable class="w-4 h-4 text-primary-500" />
                        Dynamic Fields
                    </h4>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Drag fields into the editor area</p>
                </div>

                <div class="relative mb-4">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <x-heroicon-m-magnifying-glass class="w-4 h-4 text-gray-400" />
                    </span>
                    <input 
                        x-model="search"
                        type="text" 
                        placeholder="Search fields..." 
                        class="block w-full pl-9 pr-3 py-2 text-xs border border-gray-200 rounded-lg bg-white dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 focus:ring-primary-500 focus:border-primary-500"
                    >
                </div>

                <div class="h-[500px] overflow-y-auto pr-2 custom-scrollbar space-y-2">
                    <template x-for="(label, key) in items" :key="key">
                        <div
                            x-show="label.toLowerCase().includes(search.toLowerCase())"
                            x-transition
                            class="group relative p-3 text-xs font-medium text-gray-700 bg-white border border-gray-200 rounded-xl cursor-grab active:cursor-grabbing hover:border-primary-500 hover:text-primary-600 shadow-sm transition-all dark:bg-gray-800 dark:border-gray-700 dark:text-white dark:hover:border-primary-400 dark:hover:text-primary-400"
                            draggable="true"
                            {{-- Call the helper function here --}}
                            @dragstart="$event.dataTransfer.setData('text/plain', getPlaceholder(key))"
                        >
                            <div class="flex items-center justify-between">
                                <span x-text="label"></span>
                                <x-heroicon-m-bars-2 class="w-3 h-3 text-gray-400 group-hover:text-primary-500" />
                            </div>
                            
                            <div class="absolute hidden group-hover:block left-0 -top-8 bg-gray-900 text-white text-[10px] px-2 py-1 rounded shadow-lg z-50 whitespace-nowrap">
                                Drag to add <span x-text="getPlaceholder(key)"></span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <div class="w-full md:w-2/3 lg:w-3/4">
            <div class="flex items-center justify-between mb-6 bg-white dark:bg-gray-900 p-4 rounded-xl shadow-md">
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Contract Template</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Design your dynamic rental agreement</p>
                </div>
                <x-filament::button
                    wire:click="save"
                    type="button"
                    size="lg"
                    icon="heroicon-m-check-badge"
                    class="shadow-primary-500/20 shadow-lg"
                >
                    Save Contract
                </x-filament::button>
            </div>

            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm overflow-hidden">
                <div class="p-2 bg-gray-50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-800 flex gap-2">
                    <div class="w-3 h-3 rounded-full bg-red-400/50"></div>
                    <div class="w-3 h-3 rounded-full bg-yellow-400/50"></div>
                    <div class="w-3 h-3 rounded-full bg-green-400/50"></div>
                </div>
                <div class="p-6">
                    {{ $this->form }}
                </div>
            </div>
        </div>
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #e5e7eb;
            border-radius: 10px;
        }
        /* .dark .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #374151;
        } */

        /* Force the container to ignore dark mode styles */
        .forced-light-mode {
            background-color: white !important;
            color: black !important;
        }

        /* Ensure the TinyMCE toolbar stays light even in dark mode */
        .forced-light-mode .tox-tinymce {
            border-color: #e5e7eb !important;
        }

        .forced-light-mode .tox .tox-toolbar, 
        .forced-light-mode .tox .tox-edit-area__inline,
        .forced-light-mode .tox .tox-menubar {
            background-color: #fff !important;
        }

        /* Fix for the text color inside the toolbar buttons */
        .forced-light-mode .tox .tox-tbtn {
            color: #374151 !important;
        }
    </style>
</x-filament::page>