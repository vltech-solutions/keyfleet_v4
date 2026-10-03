<div class="contents">
<x-filament-panels::page>

        @php
            $fieldGroups = [
                'General' => [
                    'date_today' => 'Date Today',
                    'created_at' => 'Date Created',
                    'remarks' => 'Remarks',
                ],

                'Customer' => [
                    'renter_name' => 'Renter Name',
                    'renter_address' => 'Renter Address',
                    'contact_number' => 'Contact Number',
                    'other_drivers' => 'Other Drivers',
                ],

                'Rental Schedule' => [
                    'start_datetime' => 'Start Date & Time',
                    'end_datetime' => 'End Date & Time',
                    'destination' => 'Destination',
                    'delivery_address' => 'Pickup / Delivery Address',
                    'return_address' => 'Return Address',
                ],

                'Vehicle' => [
                    'car.brand' => 'Car Brand',
                    'car.name' => 'Car Name',
                    'car.model' => 'Car Model',
                    'car.plate_number' => 'Plate Number',
                    'car.year' => 'Car Year',
                    'car.color' => 'Car Color',
                    'car.fuel_type' => 'Fuel Type',
                    'car.coding' => 'Coding Day',
                ],

                'Rental Charges' => [
                    'daily_rate' => 'Daily Rate',
                    'days_rented' => 'Days Rented',
                    'extend_hours' => 'Extend Hours',
                    'extend_due' => 'Extension Fee',
                    'total_rent_due' => 'Total Rent',
                    'delivery_fee' => 'Delivery Fee',
                    'driver_fee' => 'Driver Fee',
                    'security_deposit' => 'Security Deposit',
                    'discount' => 'Discount',
                ],

                'Additional Charges' => [
                    'fuel_charge' => 'Fuel Charge',
                    'out_of_bounds' => 'Out of Bounds',
                    'rfid' => 'RFID',
                    'damages' => 'Damage Fees',
                    'carwash_fee' => 'Car Wash Fee',
                ],

                'Payment Summary' => [
                    'total_due' => 'Total Due',
                    'paid_amount' => 'Paid Amount',
                    'balance' => 'Balance',
                ],
            ];
        @endphp
    <div
        x-data="keyfleetContractBuilder(@js($fieldGroups))"
        class="space-y-5"
    >


        {{-- ============================================================
             JAVASCRIPT
        ============================================================ --}}

        <script>
            window.keyfleetContractBuilder = function (groups) {
                return {
                    groups,

                    search: '',

                    fieldsOpen: window.innerWidth >= 1024,

                    feedback: '',

                    feedbackTimeout: null,

                    getPlaceholder(key) {
                        return String.fromCharCode(123, 123)
                            + key
                            + String.fromCharCode(125, 125);
                    },

                    getEditor() {
                        if (
                            typeof window.tinymce !== 'undefined'
                            && window.tinymce.activeEditor
                        ) {
                            return window.tinymce.activeEditor;
                        }

                        return null;
                    },

                    notify(message) {
                        this.feedback = message;

                        if (this.feedbackTimeout) {
                            clearTimeout(this.feedbackTimeout);
                        }

                        this.feedbackTimeout = setTimeout(() => {
                            this.feedback = '';
                        }, 1800);
                    },

                    syncEditor(editor) {
                        if (! editor) {
                            return;
                        }

                        editor.fire('change');
                        editor.fire('input');
                        editor.save();
                    },

                    insertPlaceholder(key, label = null) {
                        const editor = this.getEditor();

                        if (! editor) {
                            this.copyPlaceholder(key, label);

                            return;
                        }

                        editor.focus();

                        editor.insertContent(
                            this.getPlaceholder(key)
                        );

                        this.syncEditor(editor);

                        this.notify(
                            `${label ?? key} inserted`
                        );
                    },

                    async copyPlaceholder(key, label = null) {
                        try {
                            await navigator.clipboard.writeText(
                                this.getPlaceholder(key)
                            );

                            this.notify(
                                `${label ?? key} copied`
                            );
                        } catch (error) {
                            this.notify(
                                'Click inside the contract editor first.'
                            );
                        }
                    },

                    insertHtml(html, message) {
                        const editor = this.getEditor();

                        if (! editor) {
                            this.notify(
                                'Click inside the contract editor first.'
                            );

                            return;
                        }

                        editor.focus();

                        editor.insertContent(html);

                        this.syncEditor(editor);

                        this.notify(message);
                    },

                    insertBlock(type) {
                        const p = (key) => this.getPlaceholder(key);

                        const blocks = {
                            customer: `
                                <h3>Renter Information</h3>

                                <table style="width: 100%; border-collapse: collapse;">
                                    <tbody>
                                        <tr>
                                            <td style="width: 30%; padding: 4px 0;"><strong>Name</strong></td>
                                            <td style="padding: 4px 0;">${p('renter_name')}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 4px 0;"><strong>Address</strong></td>
                                            <td style="padding: 4px 0;">${p('renter_address')}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 4px 0;"><strong>Contact Number</strong></td>
                                            <td style="padding: 4px 0;">${p('contact_number')}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 4px 0;"><strong>Other Drivers</strong></td>
                                            <td style="padding: 4px 0;">${p('other_drivers')}</td>
                                        </tr>
                                    </tbody>
                                </table>

                                <p>&nbsp;</p>
                            `,

                            vehicle: `
                                <h3>Vehicle Information</h3>

                                <table style="width: 100%; border-collapse: collapse;">
                                    <tbody>
                                        <tr>
                                            <td style="width: 30%; padding: 4px 0;"><strong>Vehicle</strong></td>
                                            <td style="padding: 4px 0;">${p('car.brand')} ${p('car.name')}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 4px 0;"><strong>Model</strong></td>
                                            <td style="padding: 4px 0;">${p('car.model')}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 4px 0;"><strong>Plate Number</strong></td>
                                            <td style="padding: 4px 0;">${p('car.plate_number')}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 4px 0;"><strong>Year / Color</strong></td>
                                            <td style="padding: 4px 0;">${p('car.year')} / ${p('car.color')}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 4px 0;"><strong>Fuel Type</strong></td>
                                            <td style="padding: 4px 0;">${p('car.fuel_type')}</td>
                                        </tr>
                                    </tbody>
                                </table>

                                <p>&nbsp;</p>
                            `,

                            schedule: `
                                <h3>Rental Schedule</h3>

                                <table style="width: 100%; border-collapse: collapse;">
                                    <tbody>
                                        <tr>
                                            <td style="width: 30%; padding: 4px 0;"><strong>Pickup</strong></td>
                                            <td style="padding: 4px 0;">${p('start_datetime')}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 4px 0;"><strong>Return</strong></td>
                                            <td style="padding: 4px 0;">${p('end_datetime')}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 4px 0;"><strong>Destination</strong></td>
                                            <td style="padding: 4px 0;">${p('destination')}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 4px 0;"><strong>Pickup Address</strong></td>
                                            <td style="padding: 4px 0;">${p('delivery_address')}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 4px 0;"><strong>Return Address</strong></td>
                                            <td style="padding: 4px 0;">${p('return_address')}</td>
                                        </tr>
                                    </tbody>
                                </table>

                                <p>&nbsp;</p>
                            `,

                            billing: `
                                <h3>Rental Charges</h3>

                                <table style="width: 100%; border-collapse: collapse;">
                                    <tbody>
                                        <tr>
                                            <td style="padding: 4px 0;">Daily Rate</td>
                                            <td style="padding: 4px 0; text-align: right;">${p('daily_rate')}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 4px 0;">Days Rented</td>
                                            <td style="padding: 4px 0; text-align: right;">${p('days_rented')}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 4px 0;">Extension</td>
                                            <td style="padding: 4px 0; text-align: right;">${p('extend_due')}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 4px 0;">Delivery Fee</td>
                                            <td style="padding: 4px 0; text-align: right;">${p('delivery_fee')}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 4px 0;">Driver Fee</td>
                                            <td style="padding: 4px 0; text-align: right;">${p('driver_fee')}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 4px 0;">Security Deposit</td>
                                            <td style="padding: 4px 0; text-align: right;">${p('security_deposit')}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 4px 0;">Discount</td>
                                            <td style="padding: 4px 0; text-align: right;">${p('discount')}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 8px 0; border-top: 1px solid #d1d5db; font-weight: 700;">Total Due</td>
                                            <td style="padding: 8px 0; border-top: 1px solid #d1d5db; text-align: right; font-weight: 700;">${p('total_due')}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 4px 0;">Paid Amount</td>
                                            <td style="padding: 4px 0; text-align: right;">${p('paid_amount')}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 4px 0; font-weight: 700;">Balance</td>
                                            <td style="padding: 4px 0; text-align: right; font-weight: 700;">${p('balance')}</td>
                                        </tr>
                                    </tbody>
                                </table>

                                <p>&nbsp;</p>
                            `,

                            signatures: `
                                <p>&nbsp;</p>
                                <p>&nbsp;</p>

                                <table style="width: 100%; border-collapse: collapse;">
                                    <tbody>
                                        <tr>
                                            <td style="width: 45%; text-align: center; vertical-align: bottom;">
                                                ______________________________
                                                <br>
                                                <strong>${p('renter_name')}</strong>
                                                <br>
                                                Renter
                                            </td>
                                            <td style="width: 10%;"></td>
                                            <td style="width: 45%; text-align: center; vertical-align: bottom;">
                                                ______________________________
                                                <br>
                                                Authorized Representative
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            `,
                        };

                        const labels = {
                            customer: 'Renter information inserted',
                            vehicle: 'Vehicle information inserted',
                            schedule: 'Rental schedule inserted',
                            billing: 'Billing summary inserted',
                            signatures: 'Signature section inserted',
                        };

                        if (! blocks[type]) {
                            return;
                        }

                        this.insertHtml(blocks[type], labels[type]);
                    },

                    starterTemplate() {
                        const editor = this.getEditor();

                        if (! editor) {
                            this.notify('Editor is not ready yet.');

                            return;
                        }

                        const currentContent = editor
                            .getContent({ format: 'text' })
                            .trim();

                        if (
                            currentContent.length > 0
                            && ! window.confirm(
                                'This will replace your current contract content. Continue?'
                            )
                        ) {
                            return;
                        }

                        const p = (key) => this.getPlaceholder(key);

                        const template = `
                            <div style="text-align: center;">
                                <h2>VEHICLE RENTAL AGREEMENT</h2>
                                <p>This Vehicle Rental Agreement is entered into on ${p('date_today')}.</p>
                            </div>

                            <hr>

                            <h3>Renter Information</h3>

                            <table style="width: 100%; border-collapse: collapse;">
                                <tbody>
                                    <tr>
                                        <td style="width: 30%; padding: 4px 0;"><strong>Name</strong></td>
                                        <td>${p('renter_name')}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 4px 0;"><strong>Address</strong></td>
                                        <td>${p('renter_address')}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 4px 0;"><strong>Contact Number</strong></td>
                                        <td>${p('contact_number')}</td>
                                    </tr>
                                </tbody>
                            </table>

                            <p>&nbsp;</p>

                            <h3>Vehicle Information</h3>

                            <table style="width: 100%; border-collapse: collapse;">
                                <tbody>
                                    <tr>
                                        <td style="width: 30%; padding: 4px 0;"><strong>Vehicle</strong></td>
                                        <td>${p('car.brand')} ${p('car.name')}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 4px 0;"><strong>Model</strong></td>
                                        <td>${p('car.model')}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 4px 0;"><strong>Plate Number</strong></td>
                                        <td>${p('car.plate_number')}</td>
                                    </tr>
                                </tbody>
                            </table>

                            <p>&nbsp;</p>

                            <h3>Rental Schedule</h3>

                            <table style="width: 100%; border-collapse: collapse;">
                                <tbody>
                                    <tr>
                                        <td style="width: 30%; padding: 4px 0;"><strong>Pickup</strong></td>
                                        <td>${p('start_datetime')}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 4px 0;"><strong>Return</strong></td>
                                        <td>${p('end_datetime')}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 4px 0;"><strong>Destination</strong></td>
                                        <td>${p('destination')}</td>
                                    </tr>
                                </tbody>
                            </table>

                            <p>&nbsp;</p>

                            <h3>Rental Charges</h3>

                            <table style="width: 100%; border-collapse: collapse;">
                                <tbody>
                                    <tr>
                                        <td style="padding: 4px 0;">Daily Rate</td>
                                        <td style="text-align: right;">${p('daily_rate')}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 4px 0;">Days Rented</td>
                                        <td style="text-align: right;">${p('days_rented')}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 4px 0;">Total Rent</td>
                                        <td style="text-align: right;">${p('total_rent_due')}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 4px 0;">Security Deposit</td>
                                        <td style="text-align: right;">${p('security_deposit')}</td>
                                    </tr>
                                    <tr>
                                        <td style="border-top: 1px solid #d1d5db; padding: 8px 0; font-weight: 700;">Total Due</td>
                                        <td style="border-top: 1px solid #d1d5db; padding: 8px 0; text-align: right; font-weight: 700;">${p('total_due')}</td>
                                    </tr>
                                </tbody>
                            </table>

                            <p>&nbsp;</p>

                            <h3>Terms &amp; Conditions</h3>

                            <ol>
                                <li>The renter agrees to use the vehicle responsibly and only for lawful purposes.</li>
                                <li>The vehicle must be returned on the agreed date and time.</li>
                                <li>Any accident, damage, or vehicle-related incident must be reported immediately.</li>
                                <li>Additional charges may apply according to the agreed rental terms and conditions.</li>
                            </ol>

                            <p>&nbsp;</p>
                            <p>&nbsp;</p>

                            <table style="width: 100%; border-collapse: collapse;">
                                <tbody>
                                    <tr>
                                        <td style="width: 45%; text-align: center;">
                                            ___________________________
                                            <br>
                                            <strong>${p('renter_name')}</strong>
                                            <br>
                                            Renter
                                        </td>
                                        <td style="width: 10%;"></td>
                                        <td style="width: 45%; text-align: center;">
                                            ___________________________
                                            <br>
                                            Authorized Representative
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        `;

                        editor.setContent(template);

                        this.syncEditor(editor);

                        this.notify('Starter contract loaded');
                    },
                };
            };
        </script>


        {{-- ============================================================
             TOAST
        ============================================================ --}}

        <div
            x-show="feedback"
            x-transition
            x-cloak
            class="fixed bottom-6 right-6 z-[100] rounded-lg bg-gray-950 px-4 py-2.5 text-sm font-medium text-white shadow-lg dark:bg-white dark:text-gray-900"
        >
            <span x-text="feedback"></span>
        </div>


        {{-- ============================================================
             HEADER
        ============================================================ --}}

        <div
            class="flex flex-col gap-4 border-b border-gray-200 pb-5 dark:border-white/10 md:flex-row md:items-end md:justify-between"
        >

            <div>
                <p class="text-xs font-medium uppercase tracking-wider text-gray-400">
                    Rental Agreement
                </p>

                <h2 class="mt-1 text-lg font-semibold text-gray-950 dark:text-white">
                    Contract Builder
                </h2>

                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Build your rental agreement without manually typing
                    dynamic booking information.
                </p>
            </div>


            <div class="flex flex-wrap gap-2">

                <button
                    type="button"
                    @click="starterTemplate()"
                    class="inline-flex items-center gap-2 rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-white/10 dark:text-gray-300 dark:hover:bg-white/5"
                >
                    <x-heroicon-o-document-plus class="h-4 w-4" />

                    Use Starter Layout
                </button>


                <x-filament::button
                    wire:click="save"
                    type="button"
                    icon="heroicon-m-check"
                >
                    Save Contract
                </x-filament::button>

            </div>

        </div>


        {{-- ============================================================
             CONTRACT TITLE
        ============================================================ --}}

        <div>
            <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
                Contract Title
            </label>

            <input
                type="text"
                wire:model.defer="contractTitle"
                placeholder="Vehicle Rental Agreement"
                class="mt-1 block max-w-xl rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-gray-900 dark:text-white"
            >

            <p class="mt-1 text-xs text-gray-400">
                Internal name of this contract template.
            </p>
        </div>


        {{-- ============================================================
             QUICK INSERT
        ============================================================ --}}

        <div
            class="flex flex-col gap-3 border-y border-gray-100 py-4 dark:border-white/5 md:flex-row md:items-center"
        >

            <div class="shrink-0">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-gray-400">
                    Insert Section
                </p>
            </div>


            <div class="flex flex-wrap gap-2">

                <button
                    type="button"
                    @click="insertBlock('customer')"
                    class="contract-quick-button"
                >
                    Renter Information
                </button>

                <button
                    type="button"
                    @click="insertBlock('vehicle')"
                    class="contract-quick-button"
                >
                    Vehicle Information
                </button>

                <button
                    type="button"
                    @click="insertBlock('schedule')"
                    class="contract-quick-button"
                >
                    Rental Schedule
                </button>

                <button
                    type="button"
                    @click="insertBlock('billing')"
                    class="contract-quick-button"
                >
                    Billing Summary
                </button>

                <button
                    type="button"
                    @click="insertBlock('signatures')"
                    class="contract-quick-button"
                >
                    Signatures
                </button>

            </div>

        </div>


        {{-- ============================================================
             MOBILE FIELD TOGGLE
        ============================================================ --}}

        <button
            type="button"
            @click="fieldsOpen = ! fieldsOpen"
            class="flex items-center justify-between rounded-lg border border-gray-200 px-4 py-3 text-left dark:border-white/10 lg:hidden"
        >

            <div>
                <p class="text-sm font-semibold text-gray-900 dark:text-white">
                    Dynamic Fields
                </p>

                <p class="mt-0.5 text-xs text-gray-400">
                    Tap a field to insert it.
                </p>
            </div>


            <x-heroicon-m-chevron-down
                class="h-4 w-4 text-gray-400 transition"
                x-bind:class="fieldsOpen ? 'rotate-180' : ''"
            />

        </button>


        {{-- ============================================================
             WORKSPACE
        ============================================================ --}}

        <div class="flex flex-col gap-5 lg:flex-row">

            {{-- Dynamic Fields --}}
            <aside
                x-show="fieldsOpen"
                x-transition
                class="shrink-0 lg:w-[280px]"
            >

                <div class="lg:sticky lg:top-24">

                    <div
                        class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-white/10 dark:bg-gray-900"
                    >

                        <div class="border-b border-gray-100 px-4 py-4 dark:border-white/5">

                            <div class="flex items-center gap-2">

                                <x-heroicon-o-variable class="h-4 w-4 text-primary-500" />

                                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                                    Dynamic Fields
                                </h3>

                            </div>


                            <p class="mt-1.5 text-xs leading-5 text-gray-400">
                                Put your cursor inside the contract, then
                                click a field below.
                            </p>

                        </div>


                        {{-- Search --}}
                        <div class="border-b border-gray-100 p-3 dark:border-white/5">

                            <div class="relative">

                                <x-heroicon-m-magnifying-glass
                                    class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400"
                                />

                                <input
                                    type="text"
                                    x-model="search"
                                    placeholder="Search fields..."
                                    class="block w-full rounded-lg border-gray-200 bg-gray-50 py-2 pl-9 pr-3 text-xs text-gray-900 placeholder:text-gray-400 focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-white/[0.03] dark:text-white"
                                >

                            </div>

                        </div>


                        {{-- Groups --}}
                        <div class="custom-scrollbar max-h-[620px] overflow-y-auto">

                            <template x-for="(items, group) in groups" :key="group">

                                <div class="border-b border-gray-100 last:border-0 dark:border-white/5">

                                    <div class="px-4 pb-1 pt-4">

                                        <p
                                            class="text-[10px] font-semibold uppercase tracking-wider text-gray-400"
                                            x-text="group"
                                        ></p>

                                    </div>


                                    <div class="px-2 pb-3">

                                        <template x-for="(label, key) in items" :key="key">

                                            <button
                                                type="button"
                                                x-show="
                                                    ! search
                                                    || label.toLowerCase().includes(search.toLowerCase())
                                                    || key.toLowerCase().includes(search.toLowerCase())
                                                "
                                                @click="insertPlaceholder(key, label)"
                                                class="group flex w-full items-center justify-between gap-3 rounded-lg px-2.5 py-2 text-left transition hover:bg-gray-50 dark:hover:bg-white/[0.04]"
                                            >

                                                <span class="min-w-0">

                                                    <span
                                                        class="block truncate text-xs font-medium text-gray-700 group-hover:text-primary-600 dark:text-gray-200 dark:group-hover:text-primary-400"
                                                        x-text="label"
                                                    ></span>

                                                    <span
                                                        class="mt-0.5 block truncate font-mono text-[9px] text-gray-400"
                                                        x-text="getPlaceholder(key)"
                                                    ></span>

                                                </span>


                                                <x-heroicon-m-plus
                                                    class="h-4 w-4 shrink-0 text-gray-300 group-hover:text-primary-500 dark:text-gray-600"
                                                />

                                            </button>

                                        </template>

                                    </div>

                                </div>

                            </template>

                        </div>

                    </div>


                    <div class="mt-3 border-l-2 border-primary-500 py-1 pl-3">

                        <p class="text-xs font-medium text-gray-700 dark:text-gray-200">
                            How it works
                        </p>

                        <p class="mt-1 text-[11px] leading-5 text-gray-400">
                            Click inside the editor first. Then click any
                            dynamic field to insert it at the cursor.
                        </p>

                    </div>

                </div>

            </aside>


            {{-- ====================================================
                 EDITOR
            ==================================================== --}}

            <main class="min-w-0 flex-1">

                <div
                    class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-white/10 dark:bg-gray-900"
                >

                    <div
                        class="flex flex-col gap-2 border-b border-gray-100 px-4 py-3 dark:border-white/5 sm:flex-row sm:items-center sm:justify-between"
                    >

                        <div>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                Contract Template
                            </p>

                            <p class="mt-0.5 text-xs text-gray-400">
                                This is the actual content used when
                                generating rental agreements.
                            </p>
                        </div>


                        <div class="flex items-center gap-2 text-[11px] text-gray-400">

                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>

                            Dynamic fields enabled

                        </div>

                    </div>


                    <div class="contract-editor p-3 sm:p-4">
                        {{ $this->form }}
                    </div>

                </div>


                <div
                    class="mt-3 flex flex-col gap-2 text-xs text-gray-400 sm:flex-row sm:items-center sm:justify-between"
                >

                    <p>
                        Fields like

                        <code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-[10px] dark:bg-white/5">@{{renter_name}}</code>

                        are automatically replaced when generating a
                        contract.
                    </p>

                    <p>
                        Save after making changes.
                    </p>

                </div>

            </main>

        </div>


        {{-- ============================================================
             STYLES
        ============================================================ --}}

        <style>
            [x-cloak] {
                display: none !important;
            }

            .contract-quick-button {
                display: inline-flex;
                align-items: center;
                border: 1px solid rgb(229 231 235);
                border-radius: 0.5rem;
                padding: 0.4rem 0.7rem;
                font-size: 0.75rem;
                font-weight: 500;
                color: rgb(75 85 99);
                transition:
                    background-color 150ms ease,
                    border-color 150ms ease,
                    color 150ms ease;
            }

            .contract-quick-button:hover {
                border-color: rgb(var(--primary-400));
                background: rgb(var(--primary-50));
                color: rgb(var(--primary-700));
            }

            .dark .contract-quick-button {
                border-color: rgb(255 255 255 / 0.1);
                color: rgb(209 213 219);
            }

            .dark .contract-quick-button:hover {
                border-color: rgb(var(--primary-500) / 0.5);
                background: rgb(var(--primary-500) / 0.08);
                color: rgb(var(--primary-400));
            }

            .custom-scrollbar::-webkit-scrollbar {
                width: 5px;
            }

            .custom-scrollbar::-webkit-scrollbar-track {
                background: transparent;
            }

            .custom-scrollbar::-webkit-scrollbar-thumb {
                background: #d1d5db;
                border-radius: 999px;
            }

            .dark .custom-scrollbar::-webkit-scrollbar-thumb {
                background: #374151;
            }

            .contract-editor .tox-tinymce {
                min-height: 680px !important;
                border: 1px solid #e5e7eb !important;
                border-radius: 0.65rem !important;
                background: #ffffff !important;
            }

            .contract-editor .tox {
                background: #ffffff !important;
                color: #111827 !important;
            }

            .contract-editor .tox .tox-editor-header,
            .contract-editor .tox .tox-toolbar,
            .contract-editor .tox .tox-toolbar__primary,
            .contract-editor .tox .tox-toolbar-overlord,
            .contract-editor .tox .tox-menubar {
                background: #ffffff !important;
            }

            .contract-editor .tox .tox-toolbar__primary {
                border-bottom: 1px solid #f3f4f6 !important;
            }

            .contract-editor .tox .tox-tbtn {
                color: #374151 !important;
            }

            .contract-editor .tox .tox-tbtn svg {
                fill: #4b5563 !important;
            }

            .contract-editor .tox .tox-statusbar {
                background: #ffffff !important;
                border-top: 1px solid #f3f4f6 !important;
            }

            .contract-editor .tox .tox-edit-area__iframe {
                background: #ffffff !important;
            }

            @media (max-width: 767px) {
                .contract-editor {
                    padding: 0;
                }

                .contract-editor .tox-tinymce {
                    min-height: 560px !important;
                    border-right: 0 !important;
                    border-left: 0 !important;
                    border-radius: 0 !important;
                }
            }
        </style>

    </div>

</x-filament-panels::page>
</div>
