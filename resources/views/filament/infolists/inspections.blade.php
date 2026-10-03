@php
    $record = $getRecord();

    $pre = $record->preInspection;
    $post = $record->postInspection;

    $functionKeys = collect()
        ->merge($pre?->functions ? array_keys($pre->functions) : [])
        ->merge($post?->functions ? array_keys($post->functions) : [])
        ->unique()
        ->values();

    $tireKeys = collect()
        ->merge($pre?->tires ? array_keys($pre->tires) : [])
        ->merge($post?->tires ? array_keys($post->tires) : [])
        ->unique()
        ->values();

    $odoDifference = $pre && $post
        ? max(0, (float) $post->odo - (float) $pre->odo)
        : null;
@endphp

<div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-white/10 dark:bg-gray-900">

    {{-- Header --}}
    <div class="flex flex-col gap-3 border-b border-gray-200 px-5 py-4 dark:border-white/10 sm:flex-row sm:items-center sm:justify-between">

        <div>
            <p class="text-xs font-medium uppercase tracking-wider text-gray-400">
                Vehicle Inspection
            </p>

            <h2 class="mt-1 text-base font-semibold text-gray-900 dark:text-white">
                Pre & Post Inspection Comparison
            </h2>
        </div>


        <div class="flex items-center gap-5 text-xs">

            <div class="flex items-center gap-2">
                <span class="h-2 w-2 rounded-full {{ $pre ? 'bg-emerald-500' : 'bg-gray-300 dark:bg-gray-600' }}"></span>

                <span class="text-gray-500 dark:text-gray-400">
                    Pre {{ $pre ? 'available' : 'missing' }}
                </span>
            </div>


            <div class="flex items-center gap-2">
                <span class="h-2 w-2 rounded-full {{ $post ? 'bg-emerald-500' : 'bg-gray-300 dark:bg-gray-600' }}"></span>

                <span class="text-gray-500 dark:text-gray-400">
                    Post {{ $post ? 'available' : 'missing' }}
                </span>
            </div>

        </div>

    </div>


    {{-- Quick Overview --}}
    <div class="grid grid-cols-1 border-b border-gray-200 dark:border-white/10 sm:grid-cols-3">

        <div class="border-b border-gray-100 px-5 py-4 dark:border-white/5 sm:border-b-0 sm:border-r">
            <p class="text-xs text-gray-400">
                Pre Inspection
            </p>

            <p class="mt-1 text-sm font-medium text-gray-800 dark:text-gray-200">
                {{ $pre?->created_at?->format('M d, Y · h:i A') ?? 'Not available' }}
            </p>
        </div>


        <div class="border-b border-gray-100 px-5 py-4 dark:border-white/5 sm:border-b-0 sm:border-r">
            <p class="text-xs text-gray-400">
                Post Inspection
            </p>

            <p class="mt-1 text-sm font-medium text-gray-800 dark:text-gray-200">
                {{ $post?->created_at?->format('M d, Y · h:i A') ?? 'Not available' }}
            </p>
        </div>


        <div class="px-5 py-4">
            <p class="text-xs text-gray-400">
                Distance Recorded
            </p>

            <p class="mt-1 text-sm font-medium text-gray-800 dark:text-gray-200">
                {{ $odoDifference !== null
                    ? number_format($odoDifference) . ' KM'
                    : '—' }}
            </p>
        </div>

    </div>


    {{-- DESKTOP --}}
    <div class="hidden md:block">

        <table class="w-full table-fixed text-left">

            <thead class="border-b border-gray-200 bg-gray-50/60 dark:border-white/10 dark:bg-white/[0.02]">

                <tr class="text-[11px] font-medium uppercase tracking-wider text-gray-400">

                    <th class="px-5 py-3">
                        Item
                    </th>

                    <th class="px-5 py-3">
                        Pre Inspection
                    </th>

                    <th class="px-5 py-3">
                        Post Inspection
                    </th>

                </tr>

            </thead>


            <tbody class="divide-y divide-gray-100 dark:divide-white/5">

                {{-- General --}}
                <tr class="bg-gray-50/60 dark:bg-white/[0.02]">
                    <td colspan="3" class="px-5 py-2.5 text-xs font-semibold text-gray-500 dark:text-gray-400">
                        General
                    </td>
                </tr>


                <tr>

                    <td class="px-5 py-4 text-sm font-medium text-gray-700 dark:text-gray-200">
                        Inspection Date
                    </td>

                    <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">
                        {{ $pre?->created_at?->format('M d, Y · h:i A') ?? '—' }}
                    </td>

                    <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">
                        {{ $post?->created_at?->format('M d, Y · h:i A') ?? '—' }}
                    </td>

                </tr>


                <tr>

                    <td class="px-5 py-4 text-sm font-medium text-gray-700 dark:text-gray-200">
                        Odometer
                    </td>

                    <td class="px-5 py-4 text-sm font-semibold text-gray-900 dark:text-white">
                        {{ $pre ? number_format($pre->odo) . ' KM' : '—' }}
                    </td>

                    <td class="px-5 py-4">

                        <div class="flex items-center gap-2">

                            <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                {{ $post ? number_format($post->odo) . ' KM' : '—' }}
                            </span>

                            @if($odoDifference !== null && $odoDifference > 0)
                                <span class="text-xs text-gray-400">
                                    +{{ number_format($odoDifference) }} KM
                                </span>
                            @endif

                        </div>

                    </td>

                </tr>


                <tr>

                    <td class="px-5 py-4 text-sm font-medium text-gray-700 dark:text-gray-200">
                        Fuel Level
                    </td>

                    <td class="px-5 py-4">
                        @if($pre)
                            @include('filament.components.fuel-level-comparison', [
                                'value' => $pre->gas
                            ])
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </td>

                    <td class="px-5 py-4">
                        @if($post)
                            @include('filament.components.fuel-level-comparison', [
                                'value' => $post->gas
                            ])
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </td>

                </tr>


                {{-- Functions --}}
                @if($functionKeys->isNotEmpty())

                    <tr class="bg-gray-50/60 dark:bg-white/[0.02]">
                        <td colspan="3" class="px-5 py-2.5 text-xs font-semibold text-gray-500 dark:text-gray-400">
                            Systems & Functions
                        </td>
                    </tr>


                    @foreach($functionKeys as $key)

                        @php
                            $preValue = data_get($pre?->functions, $key);
                            $postValue = data_get($post?->functions, $key);

                            $changed = ! is_null($preValue)
                                && ! is_null($postValue)
                                && $preValue !== $postValue;
                        @endphp

                        <tr>

                            <td class="px-5 py-4 text-sm font-medium text-gray-700 dark:text-gray-200">
                                {{ ucwords(str_replace('_', ' ', $key)) }}
                            </td>


                            <td class="px-5 py-4">

                                @if(is_null($preValue))

                                    <span class="text-sm text-gray-400">
                                        —
                                    </span>

                                @else

                                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold
                                        {{ $preValue
                                            ? 'text-emerald-600 dark:text-emerald-400'
                                            : 'text-rose-600 dark:text-rose-400' }}"
                                    >
                                        <x-filament::icon
                                            :icon="$preValue
                                                ? 'heroicon-m-check-circle'
                                                : 'heroicon-m-x-circle'"
                                            class="h-4 w-4"
                                        />

                                        {{ $preValue ? 'PASS' : 'FAIL' }}
                                    </span>

                                @endif

                            </td>


                            <td class="px-5 py-4">

                                <div class="flex items-center gap-3">

                                    @if(is_null($postValue))

                                        <span class="text-sm text-gray-400">
                                            —
                                        </span>

                                    @else

                                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold
                                            {{ $postValue
                                                ? 'text-emerald-600 dark:text-emerald-400'
                                                : 'text-rose-600 dark:text-rose-400' }}"
                                        >
                                            <x-filament::icon
                                                :icon="$postValue
                                                    ? 'heroicon-m-check-circle'
                                                    : 'heroicon-m-x-circle'"
                                                class="h-4 w-4"
                                            />

                                            {{ $postValue ? 'PASS' : 'FAIL' }}
                                        </span>

                                    @endif


                                    @if($changed)
                                        <span class="text-[10px] font-medium text-amber-600 dark:text-amber-400">
                                            Changed
                                        </span>
                                    @endif

                                </div>

                            </td>

                        </tr>

                    @endforeach

                @endif


                {{-- Tires --}}
                @if($tireKeys->isNotEmpty())

                    <tr class="bg-gray-50/60 dark:bg-white/[0.02]">
                        <td colspan="3" class="px-5 py-2.5 text-xs font-semibold text-gray-500 dark:text-gray-400">
                            Tire Condition
                        </td>
                    </tr>


                    @foreach($tireKeys as $key)

                        @php
                            $preTire = data_get($pre?->tires, $key);
                            $postTire = data_get($post?->tires, $key);

                            $changed = filled($preTire)
                                && filled($postTire)
                                && $preTire !== $postTire;
                        @endphp

                        <tr>

                            <td class="px-5 py-4 text-sm font-medium text-gray-700 dark:text-gray-200">
                                {{ ucwords(str_replace('_', ' ', $key)) }}
                            </td>

                            <td class="px-5 py-4 text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">
                                {{ $preTire ?: '—' }}
                            </td>

                            <td class="px-5 py-4">

                                <span class="text-xs font-semibold uppercase
                                    {{ $changed
                                        ? 'text-amber-600 dark:text-amber-400'
                                        : 'text-gray-600 dark:text-gray-300' }}"
                                >
                                    {{ $postTire ?: '—' }}
                                </span>

                                @if($changed)
                                    <span class="ml-2 text-[10px] text-amber-600 dark:text-amber-400">
                                        Changed
                                    </span>
                                @endif

                            </td>

                        </tr>

                    @endforeach

                @endif


                {{-- Damage --}}
                <tr class="bg-gray-50/60 dark:bg-white/[0.02]">
                    <td colspan="3" class="px-5 py-2.5 text-xs font-semibold text-gray-500 dark:text-gray-400">
                        Reported Damage & Visual Findings
                    </td>
                </tr>


                <tr>

                    <td class="px-5 py-4 align-top text-sm font-medium text-gray-700 dark:text-gray-200">
                        Damage Summary
                    </td>

                    <td class="px-5 py-4 align-top">
                        @include('filament.components.inspection-items-list', [
                            'inspection' => $pre
                        ])
                    </td>

                    <td class="px-5 py-4 align-top">
                        @include('filament.components.inspection-items-list', [
                            'inspection' => $post
                        ])
                    </td>

                </tr>

            </tbody>

        </table>

    </div>


    {{-- MOBILE --}}
    <div class="md:hidden">

        {{-- General --}}
        <div>

            <div class="border-b border-gray-100 bg-gray-50/60 px-4 py-2.5 dark:border-white/5 dark:bg-white/[0.02]">
                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400">
                    General
                </p>
            </div>


            <div class="divide-y divide-gray-100 dark:divide-white/5">

                <div class="px-4 py-4">

                    <p class="text-sm font-medium text-gray-800 dark:text-gray-200">
                        Inspection Date
                    </p>

                    <div class="mt-3 grid grid-cols-2 gap-4">

                        <div>
                            <p class="text-[10px] uppercase tracking-wider text-gray-400">
                                Pre
                            </p>

                            <p class="mt-1 text-xs leading-5 text-gray-600 dark:text-gray-300">
                                {{ $pre?->created_at?->format('M d, Y h:i A') ?? '—' }}
                            </p>
                        </div>


                        <div>
                            <p class="text-[10px] uppercase tracking-wider text-gray-400">
                                Post
                            </p>

                            <p class="mt-1 text-xs leading-5 text-gray-600 dark:text-gray-300">
                                {{ $post?->created_at?->format('M d, Y h:i A') ?? '—' }}
                            </p>
                        </div>

                    </div>

                </div>


                <div class="px-4 py-4">

                    <div class="flex items-center justify-between">

                        <p class="text-sm font-medium text-gray-800 dark:text-gray-200">
                            Odometer
                        </p>

                        @if($odoDifference !== null && $odoDifference > 0)
                            <span class="text-xs text-gray-400">
                                +{{ number_format($odoDifference) }} KM
                            </span>
                        @endif

                    </div>


                    <div class="mt-3 grid grid-cols-2 gap-4">

                        <div>
                            <p class="text-[10px] uppercase tracking-wider text-gray-400">
                                Pre
                            </p>

                            <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">
                                {{ $pre ? number_format($pre->odo) . ' KM' : '—' }}
                            </p>
                        </div>


                        <div>
                            <p class="text-[10px] uppercase tracking-wider text-gray-400">
                                Post
                            </p>

                            <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">
                                {{ $post ? number_format($post->odo) . ' KM' : '—' }}
                            </p>
                        </div>

                    </div>

                </div>


                <div class="px-4 py-4">

                    <p class="text-sm font-medium text-gray-800 dark:text-gray-200">
                        Fuel Level
                    </p>


                    <div class="mt-3 grid grid-cols-2 gap-4">

                        <div>
                            <p class="mb-2 text-[10px] uppercase tracking-wider text-gray-400">
                                Pre
                            </p>

                            @if($pre)
                                @include('filament.components.fuel-level-comparison', [
                                    'value' => $pre->gas
                                ])
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </div>


                        <div>
                            <p class="mb-2 text-[10px] uppercase tracking-wider text-gray-400">
                                Post
                            </p>

                            @if($post)
                                @include('filament.components.fuel-level-comparison', [
                                    'value' => $post->gas
                                ])
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </div>

                    </div>

                </div>

            </div>

        </div>


        {{-- Functions --}}
        @if($functionKeys->isNotEmpty())

            <div class="border-t border-gray-200 dark:border-white/10">

                <div class="border-b border-gray-100 bg-gray-50/60 px-4 py-2.5 dark:border-white/5 dark:bg-white/[0.02]">
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400">
                        Systems & Functions
                    </p>
                </div>


                <div class="divide-y divide-gray-100 dark:divide-white/5">

                    @foreach($functionKeys as $key)

                        @php
                            $preValue = data_get($pre?->functions, $key);
                            $postValue = data_get($post?->functions, $key);

                            $changed = ! is_null($preValue)
                                && ! is_null($postValue)
                                && $preValue !== $postValue;
                        @endphp

                        <div class="px-4 py-4">

                            <div class="flex items-center justify-between gap-3">

                                <p class="text-sm font-medium text-gray-800 dark:text-gray-200">
                                    {{ ucwords(str_replace('_', ' ', $key)) }}
                                </p>

                                @if($changed)
                                    <span class="text-[10px] font-medium text-amber-600 dark:text-amber-400">
                                        Changed
                                    </span>
                                @endif

                            </div>


                            <div class="mt-3 grid grid-cols-2 gap-4">

                                <div>
                                    <p class="text-[10px] uppercase tracking-wider text-gray-400">
                                        Pre
                                    </p>

                                    @if(is_null($preValue))

                                        <p class="mt-1 text-xs text-gray-400">
                                            —
                                        </p>

                                    @else

                                        <p class="mt-1 text-xs font-semibold
                                            {{ $preValue
                                                ? 'text-emerald-600 dark:text-emerald-400'
                                                : 'text-rose-600 dark:text-rose-400' }}"
                                        >
                                            {{ $preValue ? 'PASS' : 'FAIL' }}
                                        </p>

                                    @endif
                                </div>


                                <div>
                                    <p class="text-[10px] uppercase tracking-wider text-gray-400">
                                        Post
                                    </p>

                                    @if(is_null($postValue))

                                        <p class="mt-1 text-xs text-gray-400">
                                            —
                                        </p>

                                    @else

                                        <p class="mt-1 text-xs font-semibold
                                            {{ $postValue
                                                ? 'text-emerald-600 dark:text-emerald-400'
                                                : 'text-rose-600 dark:text-rose-400' }}"
                                        >
                                            {{ $postValue ? 'PASS' : 'FAIL' }}
                                        </p>

                                    @endif
                                </div>

                            </div>

                        </div>

                    @endforeach

                </div>

            </div>

        @endif


        {{-- Tires --}}
        @if($tireKeys->isNotEmpty())

            <div class="border-t border-gray-200 dark:border-white/10">

                <div class="border-b border-gray-100 bg-gray-50/60 px-4 py-2.5 dark:border-white/5 dark:bg-white/[0.02]">
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400">
                        Tire Condition
                    </p>
                </div>


                <div class="divide-y divide-gray-100 dark:divide-white/5">

                    @foreach($tireKeys as $key)

                        @php
                            $preTire = data_get($pre?->tires, $key);
                            $postTire = data_get($post?->tires, $key);

                            $changed = filled($preTire)
                                && filled($postTire)
                                && $preTire !== $postTire;
                        @endphp

                        <div class="px-4 py-4">

                            <div class="flex items-center justify-between gap-3">

                                <p class="text-sm font-medium text-gray-800 dark:text-gray-200">
                                    {{ ucwords(str_replace('_', ' ', $key)) }}
                                </p>

                                @if($changed)
                                    <span class="text-[10px] font-medium text-amber-600 dark:text-amber-400">
                                        Changed
                                    </span>
                                @endif

                            </div>


                            <div class="mt-3 grid grid-cols-2 gap-4">

                                <div>
                                    <p class="text-[10px] uppercase tracking-wider text-gray-400">
                                        Pre
                                    </p>

                                    <p class="mt-1 text-xs font-semibold uppercase text-gray-600 dark:text-gray-300">
                                        {{ $preTire ?: '—' }}
                                    </p>
                                </div>


                                <div>
                                    <p class="text-[10px] uppercase tracking-wider text-gray-400">
                                        Post
                                    </p>

                                    <p class="mt-1 text-xs font-semibold uppercase
                                        {{ $changed
                                            ? 'text-amber-600 dark:text-amber-400'
                                            : 'text-gray-600 dark:text-gray-300' }}"
                                    >
                                        {{ $postTire ?: '—' }}
                                    </p>
                                </div>

                            </div>

                        </div>

                    @endforeach

                </div>

            </div>

        @endif


        {{-- Damage --}}
        <div class="border-t border-gray-200 dark:border-white/10">

            <div class="border-b border-gray-100 bg-gray-50/60 px-4 py-2.5 dark:border-white/5 dark:bg-white/[0.02]">
                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400">
                    Reported Damage & Visual Findings
                </p>
            </div>


            <div class="divide-y divide-gray-100 dark:divide-white/5">

                <div class="px-4 py-4">

                    <p class="mb-3 text-xs font-medium text-gray-400">
                        Pre Inspection
                    </p>

                    @include('filament.components.inspection-items-list', [
                        'inspection' => $pre
                    ])

                </div>


                <div class="px-4 py-4">

                    <p class="mb-3 text-xs font-medium text-gray-400">
                        Post Inspection
                    </p>

                    @include('filament.components.inspection-items-list', [
                        'inspection' => $post
                    ])

                </div>

            </div>

        </div>

    </div>

</div>