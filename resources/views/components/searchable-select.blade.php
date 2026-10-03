@props([
    'model',
    'options' => [],
    'label' => null,
    'placeholder' => 'Select an option',
    'searchPlaceholder' => 'Search...',
    'emptyText' => 'No results found',
    'disabled' => false,
])

@php
    /*
    |--------------------------------------------------------------------------
    | Normalize options
    |--------------------------------------------------------------------------
    |
    | Supports:
    |
    | [
    |     'all' => 'All Vehicles',
    |     1 => 'Toyota Vios',
    |     2 => 'Honda City',
    | ]
    |
    | OR:
    |
    | [
    |     [
    |         'value' => '1',
    |         'label' => 'Toyota Vios',
    |         'description' => 'ABC-1234',
    |     ],
    | ]
    |
    */

    $normalizedOptions = collect($options)
        ->map(function ($option, $key) {
            if (is_array($option)) {
                return [
                    'value' => (string) ($option['value'] ?? $option['id'] ?? $key),
                    'label' => (string) ($option['label'] ?? $option['name'] ?? ''),
                    'description' => $option['description'] ?? null,
                ];
            }

            return [
                'value' => (string) $key,
                'label' => (string) $option,
                'description' => null,
            ];
        })
        ->values()
        ->all();
@endphp


<div
    x-data="{
        open: false,
        search: '',

        value: $wire.entangle(@js($model)).live,

        options: @js($normalizedOptions),

        placeholder: @js($placeholder),

        get selectedOption() {
            return this.options.find(
                option => String(option.value) === String(this.value)
            );
        },

        get selectedLabel() {
            return this.selectedOption?.label ?? this.placeholder;
        },

        get filteredOptions() {
            const term = this.search
                .trim()
                .toLowerCase();

            if (! term) {
                return this.options;
            }

            return this.options.filter(option => {
                const haystack = [
                    option.label,
                    option.description ?? '',
                ]
                    .join(' ')
                    .toLowerCase();

                return haystack.includes(term);
            });
        },

        selectOption(option) {
            this.value = option.value;
            this.search = '';
            this.open = false;
        },

        toggle() {
            if (@js($disabled)) {
                return;
            }

            this.open = ! this.open;

            if (this.open) {
                this.$nextTick(() => {
                    this.$refs.searchInput?.focus();
                });
            }
        }
    }"
    @click.outside="open = false"
    @keydown.escape.window="open = false"
    class="relative z-30"
>
    @if($label)
        <label class="mb-2 block text-xs font-medium text-gray-500 dark:text-gray-400">
            {{ $label }}
        </label>
    @endif


    {{-- TRIGGER --}}
    <button
        type="button"
        @click="toggle()"
        @disabled($disabled)
        class="flex w-full items-center justify-between gap-3 rounded-xl border border-gray-300 bg-white px-3 py-2.5 text-left text-sm text-gray-700 shadow-sm transition
            hover:border-gray-400
            focus:border-primary-500
            focus:outline-none
            focus:ring-2
            focus:ring-primary-500/20
            disabled:cursor-not-allowed
            disabled:opacity-60
            dark:border-white/10
            dark:bg-gray-800
            dark:text-gray-200
            dark:hover:border-white/20"
    >
        <span
            class="min-w-0 truncate"
            x-text="selectedLabel"
        ></span>

        <x-filament::icon
            icon="heroicon-m-chevron-up-down"
            class="h-4 w-4 shrink-0 text-gray-400"
        />
    </button>


    {{-- DROPDOWN --}}
    <div
        x-cloak
        x-show="open"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 -translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-1"
        class="absolute left-0 z-[100] mt-2 w-full overflow-hidden rounded-xl border border-gray-200 bg-white shadow-xl dark:border-white/10 dark:bg-gray-900"
    >

        {{-- SEARCH --}}
        <div class="border-b border-gray-100 p-2.5 dark:border-white/5">

            <div class="relative">

                <x-filament::icon
                    icon="heroicon-m-magnifying-glass"
                    class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400"
                />

                <input
                    x-ref="searchInput"
                    x-model="search"
                    type="text"
                    placeholder="{{ $searchPlaceholder }}"
                    class="block w-full rounded-lg border-gray-200 bg-gray-50 py-2 pl-9 pr-3 text-sm text-gray-700
                        focus:border-primary-500
                        focus:ring-primary-500
                        dark:border-white/10
                        dark:bg-white/5
                        dark:text-gray-200"
                />

            </div>

        </div>


        {{-- OPTIONS --}}
        <div class="max-h-64 overflow-y-auto p-1.5">

            <template
                x-for="option in filteredOptions"
                :key="option.value"
            >
                <button
                    type="button"
                    @click="selectOption(option)"
                    class="mt-0.5 flex w-full items-center justify-between gap-3 rounded-lg px-3 py-2.5 text-left transition
                        hover:bg-gray-50
                        dark:hover:bg-white/5"
                    :class="
                        String(value) === String(option.value)
                            ? 'bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-300'
                            : 'text-gray-700 dark:text-gray-200'
                    "
                >

                    <div class="min-w-0">

                        <p
                            class="truncate text-sm font-medium"
                            x-text="option.label"
                        ></p>

                        <p
                            x-show="option.description"
                            class="mt-0.5 truncate text-xs text-gray-400"
                            x-text="option.description"
                        ></p>

                    </div>


                    <x-filament::icon
                        x-show="String(value) === String(option.value)"
                        icon="heroicon-m-check"
                        class="h-4 w-4 shrink-0 text-primary-500"
                    />

                </button>
            </template>


            {{-- EMPTY --}}
            <div
                x-show="filteredOptions.length === 0"
                class="px-3 py-8 text-center"
            >
                <x-filament::icon
                    icon="heroicon-o-magnifying-glass"
                    class="mx-auto h-6 w-6 text-gray-300 dark:text-gray-600"
                />

                <p class="mt-2 text-sm font-medium text-gray-600 dark:text-gray-300">
                    {{ $emptyText }}
                </p>

                <p class="mt-1 text-xs text-gray-400">
                    Try another search term.
                </p>
            </div>

        </div>

    </div>
</div>