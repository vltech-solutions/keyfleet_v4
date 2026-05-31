@php
    $record = $getRecord();
    $pre = $record->preInspection;
    $post = $record->postInspection;

    // Dynamically fetch keys from JSON columns to handle different vehicle types/configs
    $functionKeys = collect([])
        ->merge($pre?->functions ? array_keys($pre->functions) : [])
        ->merge($post?->functions ? array_keys($post->functions) : [])
        ->unique();

    $tireKeys = collect([])
        ->merge($pre?->tires ? array_keys($pre->tires) : [])
        ->merge($post?->tires ? array_keys($post->tires) : [])
        ->unique();
@endphp

<div class="overflow-x-auto border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-800">
    <table class="w-full text-sm text-left">
        <thead class="bg-gray-100 dark:bg-gray-700">
            <tr>
                <th class="px-4 py-3 font-bold uppercase text-xs text-gray-600 dark:text-white">Field</th>
                <th class="px-4 py-3 font-bold uppercase text-xs text-gray-600 dark:text-white">Pre-Inspection</th>
                <th class="px-4 py-3 font-bold uppercase text-xs text-gray-600 dark:text-white">Post-Inspection</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-600">

            <tr class="bg-gray-200/50 dark:bg-gray-900/50">
                <td colspan="3" class="px-4 py-1 font-bold text-[10px] uppercase tracking-widest text-gray-800">General Overview</td>
            </tr>
            <tr>
                <td class="px-4 py-2 font-semibold">Inspection Date</td>
                <td class="px-4 py-2">{{ $pre ? date('M d, Y h:i A', strtotime($pre->created_at)) : 'N/A' }}</td>
                <td class="px-4 py-2">{{ $post ? date('M d, Y h:i A', strtotime($post->created_at)) : 'N/A' }}</td>
            </tr>
            <tr>
                <td class="px-4 py-2 font-semibold">Odometer</td>
                <td class="px-4 py-2 text-primary-600 font-bold">{{ $pre ? number_format($pre->odo) . ' KM' : '-' }}</td>
                <td class="px-4 py-2 text-primary-600 font-bold">{{ $post ? number_format($post->odo) . ' KM' : '-' }}</td>
            </tr>
            <tr>
                <td class="px-4 py-2 font-semibold">Fuel Level</td>
                <td class="px-4 py-2">
                    @if($pre) @include('filament.components.fuel-level-comparison', ['value' => $pre->gas]) @else - @endif
                </td>
                <td class="px-4 py-2">
                    @if($post) @include('filament.components.fuel-level-comparison', ['value' => $post->gas]) @else - @endif
                </td>
            </tr>

            <tr class="bg-gray-200/50 dark:bg-gray-900/50">
                <td colspan="3" class="px-4 py-1 font-bold text-[10px] uppercase tracking-widest text-gray-800">Systems & Functions</td>
            </tr>
            @foreach($functionKeys as $key)
                <tr>
                    <td class="px-4 py-2 font-semibold">{{ ucwords(str_replace('_', ' ', $key)) }}</td>
                    <td class="px-4 py-2">
                        @if($pre && isset($pre->functions[$key]))
                            <div class="flex items-center gap-1.5 {{ $pre->functions[$key] ? 'text-success-600' : 'text-danger-600' }}">
                                @if($pre->functions[$key]) <x-heroicon-m-check-circle class="w-4 h-4"/> @else <x-heroicon-m-x-circle class="w-4 h-4"/> @endif
                                <span class="text-xs font-bold">{{ $pre->functions[$key] ? 'PASS' : 'FAIL' }}</span>
                            </div>
                        @else
                            <span class="text-gray-400 italic">No Data</span>
                        @endif
                    </td>
                    <td class="px-4 py-2">
                        @if($post && isset($post->functions[$key]))
                            <div class="flex items-center gap-1.5 {{ $post->functions[$key] ? 'text-success-600' : 'text-danger-600' }}">
                                @if($post->functions[$key]) <x-heroicon-m-check-circle class="w-4 h-4"/> @else <x-heroicon-m-x-circle class="w-4 h-4"/> @endif
                                <span class="text-xs font-bold">{{ $post->functions[$key] ? 'PASS' : 'FAIL' }}</span>
                            </div>
                        @else
                            <span class="text-gray-400 italic">No Data</span>
                        @endif
                    </td>
                </tr>
            @endforeach

            <tr class="bg-gray-200/50 dark:bg-gray-900/50">
                <td colspan="3" class="px-4 py-1 font-bold text-[10px] uppercase tracking-widest text-gray-800">Tire Condition</td>
            </tr>
            @foreach($tireKeys as $key)
                <tr>
                    <td class="px-4 py-2 font-semibold">{{ ucwords(str_replace('_', ' ', $key)) }}</td>
                    <td class="px-4 py-2 uppercase text-xs font-bold">{{ $pre->tires[$key] ?? '-' }}</td>
                    <td class="px-4 py-2 uppercase text-xs font-bold {{ ($pre && $post && ($pre->tires[$key] ?? '') !== ($post->tires[$key] ?? '')) ? 'text-warning-600 underline' : '' }}">
                        {{ $post->tires[$key] ?? '-' }}
                    </td>
                </tr>
            @endforeach

            <tr class="bg-gray-200/50 dark:bg-gray-900/50">
                <td colspan="3" class="px-4 py-1 font-bold text-[10px] uppercase tracking-widest text-gray-800">Reported Damages & Visuals</td>
            </tr>
            <tr>
                <td class="px-4 py-2 font-semibold align-top text-danger-600">Damage Summary</td>
                <td class="px-4 py-2 align-top">
                    @include('filament.components.inspection-items-list', ['inspection' => $pre])
                </td>
                <td class="px-4 py-2 align-top">
                    @include('filament.components.inspection-items-list', ['inspection' => $post])
                </td>
            </tr>

            {{-- <tr class="border-t-2">
                <td class="px-4 py-2 font-semibold">Inspected By</td>
                <td class="px-4 py-2 italic">{{ $pre->inspected_by ?? 'N/A' }}</td>
                <td class="px-4 py-2 italic">{{ $post->inspected_by ?? 'N/A' }}</td>
            </tr> --}}

        </tbody>
    </table>
</div>