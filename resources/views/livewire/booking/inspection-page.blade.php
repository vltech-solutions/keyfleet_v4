<div class="rounded-lg p-1 mx-auto mt-5 bg-white dark:bg-gray-950 min-h-screen flex flex-col transition-colors duration-300" style="margin-left:-15px !important;margin-right:-15px !important;">
    <div class="flex items-center gap-4 mb-6 p-4">
        {{-- Back Button (Icon only or small circle for better mobile fit) --}}
        <a href="{{ url("app/{$booking->company->slug}/bookings/{$booking->id}/view") }}" 
        class="flex items-center justify-center w-10 h-10 rounded-full bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-800 text-gray-500 hover:text-primary-600 shadow-sm transition-all active:scale-90">
            <x-heroicon-m-arrow-left class="w-5 h-5" />
        </a>

        {{-- Title and Vehicle Info --}}
        <div>
            <h1 class="text-lg  text-gray-900 dark:text-white leading-tight uppercase ">
                {{ $inspectionType }} Inspection
            </h1>
            <p class="text-[10px] text-gray-500 dark:text-gray-400 font-bold uppercase ">
                {{ $vehicle_name }}
            </p>
        </div>
    </div>
  	<style>
        .bottom-navbar{
			display:none !important;
        }
  	</style>

    <div class="sticky top-0 bg-white/80 dark:bg-transparent backdrop-blur-md sm:border-b border-gray-100 dark:border-gray-800">
        @php
            $steps = [
                'Odometer'  => 'o-stop-circle',
                'Exterior'  => 'o-truck',
                'Interior'  => 'o-user-circle',
                'Functions' => 'o-cog-6-tooth',
                'Tires'     => 'o-lifebuoy',
                'Confirm'   => 'o-pencil-square',
            ];
            $totalSteps = count($steps);
            $stepNames = array_keys($steps);
            $currentStepName = $stepNames[$currentStep - 1] ?? 'Inspection';
        @endphp

        {{-- 1. DESKTOP/TABLET STEPPER (Visible on md and up) --}}
        <div class="hidden md:flex items-center justify-between px-8 py-6 max-w-5xl mx-auto">
            @foreach($steps as $stepName => $icon)
                @php $stepNum = $loop->iteration; @endphp
                
                <div class="flex flex-col items-center min-w-[80px] space-y-2">
                    <div @class([
                        'w-9 h-9 rounded-full flex items-center justify-center transition-all duration-500',
                        // Active Step
                        'bg-primary-600 text-white ring-4 ring-primary-50 dark:ring-primary-900/30 scale-110' => $currentStep == $stepNum,
                        // Completed Step
                        'bg-green-500 text-white shadow-green-100 dark:shadow-none' => $currentStep > $stepNum,
                        // Upcoming Step
                        'bg-gray-100 dark:bg-gray-800 text-gray-400 dark:text-gray-500' => $currentStep < $stepNum
                    ])>
                        @if($currentStep > $stepNum) 
                            <x-heroicon-m-check class="w-5 h-5" /> 
                        @else 
                            <x-dynamic-component :component="'heroicon-' . $icon" class="w-5 h-5" />
                        @endif
                    </div>
                    
                    <span @class([
                        'text-xs uppercase transition-colors',
                        'text-primary-600 dark:text-primary-400' => $currentStep == $stepNum,
                        'text-gray-400 dark:text-gray-500' => $currentStep != $stepNum
                    ])>
                        {{ $stepName }}
                    </span>
                </div>

                {{-- Optional Connector Line between steps --}}
                @if(!$loop->last)
                    <div class="flex-1 h-px bg-gray-100 dark:bg-gray-800 mx-2 mb-6"></div>
                @endif
            @endforeach
        </div>

        {{-- 2. MOBILE COMPACT HEADER (Visible only on small screens) --}}
        <div class="md:hidden px-4 py-3 flex items-center justify-between">
            <div class="flex flex-col">
                <div class="flex items-center gap-2">
                    <span class="flex items-center justify-center w-5 h-5 rounded-full bg-primary-600 text-[10px] font-black text-white">
                        {{ $currentStep }}
                    </span>
                    <span class="text-[10px] font-black uppercase tracking-[0.15em] text-gray-400 dark:text-gray-500">
                        Step {{ $currentStep }} of {{ $totalSteps }}
                    </span>
                </div>
                <h2 class="text-base font-bold text-gray-900 dark:text-white mt-0.5">
                    {{ $currentStepName }}
                </h2>
            </div>

            {{-- Circular Progress Indicator --}}
            <div class="relative flex items-center justify-center">
                <svg class="w-10 h-10 transform -rotate-90">
                    <circle cx="20" cy="20" r="18" stroke="currentColor" stroke-width="3" fill="transparent" class="text-gray-100 dark:text-gray-800" />
                    <circle cx="20" cy="20" r="18" stroke="currentColor" stroke-width="3" fill="transparent" 
                        class="text-primary-600 transition-all duration-700 ease-in-out"
                        stroke-dasharray="{{ 2 * pi() * 18 }}"
                        stroke-dashoffset="{{ (2 * pi() * 18) * (1 - ($currentStep / $totalSteps)) }}"
                        stroke-linecap="round" />
                </svg>
                <span class="absolute text-[9px] font-black text-gray-600 dark:text-gray-400">
                    {{ round(($currentStep / $totalSteps) * 100) }}%
                </span>
            </div>
        </div>
    </div>

    @if($currentStep == 1)
        <div class="w-full p-6 space-y-6 mb-6">
            <div class="space-y-2" 
                x-data="{ 
                    level: @entangle('data.fuel_level').live,
                    dragging: false,
                    updateFromEvent(e) {
                        const rect = this.$refs.track.getBoundingClientRect();
                        const clientX = e.touches ? e.touches[0].clientX : e.clientX;
                        const position = (clientX - rect.left) / rect.width;
                        let val = Math.round(position * 20) * 5; 
                        this.level = Math.max(0, Math.min(100, val));
                    }
                }"
                @mouseup.window="dragging = false"
                @mousemove.window="if(dragging) updateFromEvent($event)">
                
                <div class="flex justify-between items-center px-1 mb-1">
                    <label class="block text-xs font-black text-gray-400 dark:text-gray-500">Current Fuel</label>
                    <span :class="{
                        'bg-red-500': level <= 25,
                        'bg-amber-500': level > 25 && level <= 50,
                        'bg-emerald-500': level > 50
                    }" class="text-[10px] font-black px-3 py-1 rounded-full text-white transition-all duration-300 shadow-sm">
                        <span x-text="level"></span>%
                    </span>
                </div>

                {{-- Container for both Labels and Track --}}
                <div class="relative pt-6 pb-2 touch-none cursor-pointer group" 
                    x-ref="track"
                    @mousedown="dragging = true; updateFromEvent($event)"
                    @touchstart="dragging = true; updateFromEvent($event)"
                    @touchmove.prevent="updateFromEvent($event)"
                    @touchend="dragging = false">
                    
                    {{-- Labels (Positioned at the very top) --}}
                    {{-- Visual Track Container --}}
                    <div class="relative flex items-center h-8">
                        {{-- Progress Track --}}
                        <div class="h-5 w-full bg-gray-100 dark:bg-gray-800/50 rounded-full overflow-hidden border border-gray-200 dark:border-gray-700/30 relative">
                            <div class="h-full transition-all duration-150 ease-out"
                                :style="`width: ${level}%; background-color: ${level <= 25 ? '#ef4444' : (level <= 50 ? '#f59e0b' : '#10b981')}`">
                            </div>

                            <div class="absolute inset-0 flex justify-evenly pointer-events-none">
                                <div class="w-px h-full bg-black/5 dark:bg-white/5"></div>
                                <div class="w-px h-full bg-black/5 dark:bg-white/5"></div>
                                <div class="w-px h-full bg-black/5 dark:bg-white/5"></div>
                            </div>
                        </div>

                        {{-- Thumb (Now vertically centered within the h-8 flex container) --}}
                        <div class="absolute w-8 h-8 bg-white dark:bg-gray-100 border-[3px] border-gray-900 dark:border-gray-600 rounded-full shadow-xl transition-all duration-150 pointer-events-none z-10"
                            :style="`left: calc(${level}% - 1rem)`">
                            <div class="absolute inset-0 m-auto w-0.5 h-3 bg-gray-300 rounded-full"></div>
                        </div>
                    </div>

                    <div class="absolute inset-x-0  flex justify-between pointer-events-none px-0.5">
                        <div class="flex flex-col items-center w-4"><div class="w-px h-1 bg-gray-300 dark:bg-gray-700 mb-1"></div><span class="text-[9px] font-black text-gray-400">E</span></div>
                        <div class="flex flex-col items-center w-6"><div class="w-px h-1 bg-gray-300 dark:bg-gray-700 mb-1"></div><span class="text-[9px] font-black text-gray-400">1/4</span></div>
                        <div class="flex flex-col items-center w-6"><div class="w-px h-1 bg-gray-300 dark:bg-gray-700 mb-1"></div><span class="text-[9px] font-black text-gray-400">1/2</span></div>
                        <div class="flex flex-col items-center w-6"><div class="w-px h-1 bg-gray-300 dark:bg-gray-700 mb-1"></div><span class="text-[9px] font-black text-gray-400">3/4</span></div>
                        <div class="flex flex-col items-center w-4"><div class="w-px h-1 bg-gray-300 dark:bg-gray-700 mb-1"></div><span class="text-[9px] font-black text-gray-400">F</span></div>
                    </div>
                </div>

                <input type="hidden" name="fuel_level" x-model="level">
            </div>

            <div class="space-y-2">
                <label class="block text-sm font-bold text-gray-700 dark:text-gray-500">Odometer Reading (KM)</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <x-heroicon-m-stop-circle class="w-5 h-5 text-gray-400 dark:text-gray-500" />
                    </div>
                    <input type="number" 
                        wire:model.defer="data.odometer" 
                        inputmode="numeric" 
                        pattern="[0-9]*"
                        class="block w-full pl-10 pr-4 py-4 bg-gray-50 dark:bg-gray-800 border-none rounded-2xl focus:ring-2 focus:ring-primary-500 text-lg dark:text-white dark:placeholder-gray-500 transition-colors" 
                        placeholder="000,000">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                    <label class="block text-sm font-bold  dark:text-gray-500 ">Autosweep (₱)</label>
                    <input type="number" 
                        wire:model.defer="data.autosweep_balance" 
                        inputmode="decimal" 
                        step="0.01"
                        class="block w-full px-4 py-4 bg-gray-50 dark:bg-gray-800 border-none rounded-2xl focus:ring-2 focus:ring-primary-500 text-lg dark:text-white dark:placeholder-gray-500 transition-colors" 
                        placeholder="0.00">
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-bold  dark:text-gray-500 ">Easytrip (₱)</label>
                    <input type="number" 
                        wire:model.defer="data.easytrip_balance" 
                        inputmode="decimal" 
                        step="0.01"
                        class="block w-full px-4 py-4 bg-gray-50 dark:bg-gray-800 border-none rounded-2xl focus:ring-2 focus:ring-primary-500 text-lg dark:text-white dark:placeholder-gray-500 transition-colors" 
                        placeholder="0.00">
                </div>
            </div>
        </div>
    @endif

    @if($currentStep == 2)
        <div class="flex-1 flex flex-col items-center justify-center w-full bg-gray-50 dark:bg-gray-900 p-2 overflow-hidden transition-colors">
            
            {{-- Instruction text --}}
            <div class="mb-2 shrink-0">
                <span class="text-sm  text-gray-400 dark:text-gray-500 ">
                    Select part to record damage
                </span>
            </div>

            <div class="relative w-full flex items-center justify-center" 
                style="height: calc(100vh - 280px); max-height: 600px;">
                
                <div class="relative h-full aspect-[1/2]">
                    <img src="{{ \Storage::url('images/car-inspection.png') }}" 
                        alt="Car Inspection" 
                        class="h-full w-full object-contain select-none pointer-events-none dark:opacity-80 dark:brightness-110 transition-all">

                    @foreach($points as $number => $coords)
                        @php 
                            // Check if there is pre-existing damage OR if a new photo has been uploaded/exists
                            $hasPhoto = !empty($inspectionData[$number]['photo']) || !empty($inspectionData[$number]['pre_photo']);
                            $hasPreDamage = ($inspectionData[$number]['condition'] ?? '') === 'damaged';
                            
                            // The condition to turn the button RED
                            $isMarked = $hasPhoto || $hasPreDamage;
                            
                            $isSelected = $selectedPoint == $number;
                        @endphp
                        <button 
                            type="button"
                            wire:key="point-{{ $number }}"
                            wire:click="selectPoint({{ $number }})"
                            @class([
                                'absolute flex items-center justify-center w-8 h-8 text-xs  transition-all rounded-full border-2 shadow-lg transform -translate-x-1/2 -translate-y-1/2 touch-manipulation',
                                // Priority 1: Selected State (Green)
                                'bg-green-600 text-white border-white scale-125 z-20 shadow-green-500/50' => $isSelected,
                                // Priority 2: Uploaded/Pre-existing Damage (Red)
                                'bg-red-500 text-white border-red-700 z-15 animate-pulse' => $isMarked && !$isSelected,
                                // Priority 3: Default (Yellow)
                                'bg-yellow-400 text-black border-yellow-600 z-10 dark:border-yellow-500/50' => !$isSelected && !$isMarked
                            ])
                            style="top: {{ $coords['top'] }}%; left: {{ $coords['left'] }}%;">
                            {{ $number }}
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    @if($currentStep == 3)
        {{-- Main Container --}}
        <div class="flex-1 flex flex-col items-center justify-center w-full bg-gray-50 dark:bg-gray-900 p-2 overflow-hidden transition-colors">
            
            {{-- Instruction text --}}
            <div class="mb-2 shrink-0">
                <span class="text-sm  text-gray-400 dark:text-gray-500">
                    Select part to record damage
                </span>
            </div>

            {{-- The Image Wrapper --}}
            <div class="relative w-full flex items-center justify-center" 
                style="height: calc(100vh - 280px); max-height: 600px;">
                
                <div class="relative h-full aspect-[1/2]">
                    {{-- Car Image with opacity and brightness adjustments for dark mode --}}
                    <img src="{{ \Storage::url('images/car-inspection.png') }}" 
                        alt="Interior Inspection" 
                        class="h-full w-full object-contain select-none pointer-events-none opacity-60 dark:opacity-40 dark:brightness-125 transition-all">

                    @foreach($interiorPoints as $number => $coords)
                        @php 
                            $zoneId = "Interior_{$number}";
                            
                            // Check if there is pre-existing damage OR if a new photo has been uploaded
                            $hasPhoto = !empty($inspectionData[$zoneId]['photo']) || !empty($inspectionData[$zoneId]['pre_photo']);
                            $hasPreDamage = ($inspectionData[$zoneId]['condition'] ?? '') === 'damaged';
                            
                            $isMarked = $hasPhoto || $hasPreDamage;
                            
                            $isSelected = $selectedPoint == $zoneId;
                        @endphp
                        <button 
                            type="button"
                            wire:key="int-point-{{ $number }}"
                            wire:click="selectPoint({{ $number }})"
                            @class([
                                'absolute flex items-center justify-center w-10 h-10 text-sm  transition-all rounded-full border-2 shadow-xl transform -translate-x-1/2 -translate-y-1/2 touch-manipulation',
                                'bg-green-600 text-white border-white scale-125 z-20 shadow-green-500/40' => $isSelected,
                                'bg-red-500 text-white border-red-700 z-15 animate-pulse' => $isMarked && !$isSelected,
                                'bg-yellow-400 text-black border-yellow-600 z-10 dark:border-yellow-500/50' => !$isSelected && !$isMarked
                            ])
                            style="top: {{ $coords['top'] }}%; left: {{ $coords['left'] }}%;">
                            {{ $number }}
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    @if($currentStep == 4)
        <div class="flex-1 flex flex-col w-full bg-white dark:bg-gray-950 p-6 overflow-y-auto transition-colors">
            <div class="mb-8">
                <h3 class="text-lg  text-gray-900 dark:text-white mb-1">Functional Check</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Select all parts that are currently working correctly.</p>
            </div>

            <div class="space-y-3 mb-10">
                @php
                    $allFunctions = [
                        'all_lights' => ['label' => 'All Lights', 'icon' => 'o-light-bulb'],
                        'doors_and_locks' => ['label' => 'Doors & Locks', 'icon' => 'o-lock-closed'],
                        'wipers' => ['label' => 'Wipers & Washer', 'icon' => 'o-cloud'],
                        'aircon' => ['label' => 'Airconditioning', 'icon' => 'o-sun'],
                        'handbrake' => ['label' => 'Handbrake/Park', 'icon' => 'o-hand-raised'],
                        'power_windows_sunroof' => ['label' => 'Windows & Sunroof', 'icon' => 'o-square-3-stack-3d'],
                        'radio_infotainment' => ['label' => 'Radio/Infotainment', 'icon' => 'o-musical-note'],
                        'horn' => ['label' => 'Horn Function', 'icon' => 'o-megaphone'],
                        'dashcam' => ['label' => 'Dashcam Recording', 'icon' => 'o-video-camera'],
                        'fuel_cap' => ['label' => 'Fuel Cap/Door', 'icon' => 'o-beaker'],
                    ];
                @endphp

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 mb-10">
                    @foreach($allFunctions as $key => $info)
                        @php $isDone = $functions[$key] ?? false; @endphp
                        
                        <button 
                            type="button"
                            wire:key="fn-{{ $key }}"
                            wire:click="$set('functions.{{ $key }}', {{ $isDone ? 'false' : 'true' }})"
                            @class([
                                'w-full flex items-center justify-between p-4 rounded-2xl border-2 transition-all duration-200',
                                // Active State
                                'border-primary-600 bg-primary-50 dark:bg-primary-900/10 shadow-sm ring-1 ring-primary-600/20' => $isDone,
                                // Inactive State
                                'border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-900' => !$isDone
                            ])>
                            
                            <div class="flex items-center gap-3">
                                {{-- <div @class([
                                    'p-2 rounded-xl shrink-0 transition-colors',
                                    'bg-gray-100 text-white shadow-md shadow-gray-200 dark:shadow-none' => $isDone,
                                    'bg-gray-200 dark:bg-gray-800 text-primary dark:text-white' => !$isDone
                                ])>
                                    <x-dynamic-component :component="'heroicon-' . $info['icon']" class="w-5 h-5" />
                                </div> --}}
                                <span @class([
                                    'font-bold text-sm text-left transition-colors',
                                    'text-primary-900 dark:text-gray-400' => $isDone,
                                    'text-gray-600 dark:text-gray-400' => !$isDone
                                ])>
                                    {{ $info['label'] }}
                                </span>
                            </div>

                            <div @class([
                                'w-6 h-6 rounded-full flex items-center justify-center shrink-0 transition-all border-2',
                                'bg-primary-600 border-primary-600 scale-110' => $isDone,
                                'bg-transparent border-gray-200 dark:border-gray-700' => !$isDone
                            ])>
                                @if($isDone)
                                    <x-heroicon-m-check class="w-4 h-4 text-white" />
                                @endif
                            </div>
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    @if($currentStep == 5)
        <div class="flex-1 flex flex-col w-full bg-white dark:bg-gray-950 p-6 overflow-y-auto pb-32 transition-colors">
            <div class="mb-8">
                <h3 class="text-lg  text-gray-900 dark:text-white mb-1">Tires & Equipment</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Check tread depth and verify emergency kit presence.</p>
            </div>

            {{-- Tire Condition Grid --}}
            <div class="grid grid-cols-2 gap-4 mb-10">
                @foreach([
                    'front_left' => 'Front Left',
                    'front_right' => 'Front Right',
                    'rear_left' => 'Rear Left',
                    'rear_right' => 'Rear Right'
                ] as $key => $label)
                    <div class="space-y-2">
                        <label class=" text-gray-400 dark:text-gray-500 px-1">{{ $label }}</label>
                        <select wire:model.live="tires.{{ $key }}" 
                            @class([
                                'w-full p-4 rounded-2xl  font-bold text-sm focus:ring-2 transition-all appearance-none cursor-pointer',
                                // Green: Good
                                'bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-400 focus:ring-green-500' => ($tires[$key] ?? '') == 'Good',
                                // Yellow: Low
                                'bg-yellow-50 text-yellow-700 dark:bg-yellow-900/20 dark:text-yellow-400 focus:ring-yellow-500' => ($tires[$key] ?? '') == 'Low Tread',
                                // Red: Flat
                                'bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400 focus:ring-red-500' => ($tires[$key] ?? '') == 'Flat/Damaged',
                                // Default State
                                'bg-gray-50 dark:bg-gray-900 text-gray-600 dark:text-gray-400' => empty($tires[$key])
                            ])>
                            <option value="Good">Good</option>
                            <option value="Low Tread">Low Tread</option>
                            <option value="Flat/Damaged">Flat/Damaged</option>
                        </select>
                    </div>
                @endforeach
            </div>

            {{-- Accessories Checklist --}}
            <div class="space-y-4">
                <h3 class="text-sm  text-gray-400 dark:text-gray-500   px-1">
                    Emergency Equipment
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach([
                        'spare_tire' => ['label' => 'Spare Tire', 'icon' => 'o-circle-stack'],
                        'tools_jack' => ['label' => 'Jack & Tire Spanner', 'icon' => 'o-wrench-screwdriver'],
                        'early_warning' => ['label' => 'Early Warning Device', 'icon' => 'o-exclamation-triangle'],
                        'fire_extinguisher' => ['label' => 'Fire Extinguisher', 'icon' => 'o-fire']
                    ] as $key => $item)
                        @php $isPresent = $tires[$key] ?? false; @endphp
                        
                        <button 
                            type="button"
                            wire:key="equip-{{ $key }}"
                            wire:click="$set('tires.{{ $key }}', {{ $isPresent ? 'false' : 'true' }})"
                            @class([
                                'w-full flex items-center justify-between p-4 rounded-2xl border-2 transition-all duration-200 active:scale-[0.98]',
                                // Active State
                                'border-primary-600 bg-primary-50 dark:bg-primary-900/10 shadow-sm' => $isPresent,
                                // Inactive State
                                'border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-900' => !$isPresent
                            ])>
                            
                            <div class="flex items-center gap-3">
                                {{-- <div @class([
                                    'p-2 rounded-xl shrink-0 transition-colors',
                                    'bg-primary-600 text-white shadow-md shadow-primary-200 dark:shadow-none' => $isPresent,
                                    'bg-gray-200 dark:bg-gray-800 text-gray-500 dark:text-gray-400' => !$isPresent
                                ])>
                                    <x-dynamic-component :component="'heroicon-' . $item['icon']" class="w-5 h-5" />
                                </div> --}}
                                
                                <span @class([
                                    'font-bold text-sm text-left transition-colors',
                                    'text-primary-900 dark:text-primary-400' => $isPresent,
                                    'text-gray-600 dark:text-gray-400' => !$isPresent
                                ])>
                                    {{ $item['label'] }}
                                </span>
                            </div>

                            <div @class([
                                'w-6 h-6 rounded-full flex items-center justify-center shrink-0 transition-all border-2',
                                'bg-primary-600 border-primary-600 scale-110' => $isPresent,
                                'bg-transparent border-gray-200 dark:border-gray-700' => !$isPresent
                            ])>
                                @if($isPresent)
                                    <x-heroicon-m-check class="w-4 h-4 text-white" />
                                @endif
                            </div>
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    @if($currentStep == 6)
        <div class="flex-1 flex flex-col w-full bg-white dark:bg-gray-900 p-6 pb-32 overflow-y-auto transition-colors">
            
            {{-- Header Summary --}}
            <div class="mb-8">
                <h3 class="text-lg  text-gray-900 dark:text-white mb-1">Final Confirmation</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Please review details and sige.</p>
            </div>

            {{-- Signee Details --}}
            <div class="space-y-6 mb-8">
                {{-- Name Input --}}
                <div>
                    <label class="block text-sm dark:text-gray-500 ">Renter's Name</label>
                    <input type="text" 
                        wire:model="signee_name" 
                        placeholder="Enter full name"
                        {{-- Changed text-sm to text-base to prevent iOS zoom --}}
                        class="w-full bg-white dark:bg-gray-950 border border-gray-200 dark:border-gray-100 rounded-2xl py-4 px-4 text-base text-gray-900 dark:text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-all shadow-sm">
                </div>

                {{-- General Remarks --}}
                <div>
                    <label class="block text-sm  dark:text-gray-500 ">General Remarks (Optional)</label>
                    <textarea wire:model="general_notes" 
                        rows="4" 
                        placeholder="Type any overall observations here..."
                        {{-- Changed text-sm to text-base to prevent iOS zoom --}}
                        class="w-full bg-white dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-2xl py-4 px-4 text-base font-medium text-gray-600 dark:text-gray-400 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 transition-all resize-none shadow-sm"></textarea>
                </div>
            </div>

            {{-- The Signature Pad --}}
            <div class="bg-white dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-md overflow-hidden shadow-sm"
                x-data="signaturePad(@entangle('signature'))"
                wire:ignore>
                
                <div class="flex justify-between items-center px-4 py-3 border-b border-gray-100 dark:border-gray-800">
                    <span class="text-sm   text-gray-400 ">Draw Signature</span>
                    <button type="button" @click="clear" class="text-sm  text-red-500   hover:underline">
                        Clear Pad
                    </button>
                </div>

                <div class="relative w-full h-64 bg-gray-50 dark:bg-gray-900/50 rounded-md overflow-hidden border border-gray-200 dark:border-gray-800">
                    {{-- Remove h-64 from the canvas, keep it on the parent div --}}
                    <canvas x-ref="canvas" class="w-full h-full touch-none cursor-crosshair"></canvas>
                </div>
                
                <div class="py-3 px-4 bg-gray-50/50 dark:bg-gray-900/30 border-t border-gray-100 dark:border-gray-800 text-center">
                    <p class="text-[9px] text-gray-400 font-bold  ">
                        I acknowledge the vehicle condition as recorded above
                    </p>
                </div>
            </div>


        </div>
    @endif

    <div class="fixed bottom-0 left-0 z-50 w-full bg-white/80 dark:bg-transparent backdrop-blur-lg  px-4 pt-4 pb-safe-offset-4">
      <div class="max-w-7xl mx-auto flex items-center gap-4 mb-6">

          @if($currentStep > 1)
              <button wire:click="back" 
                  @click="window.scrollTo({top: 0, behavior: 'smooth'})"
                  wire:loading.attr="disabled"
                  class="flex-1 py-4 border-2 border-gray-200/50 dark:border-gray-800/50 text-gray-700 dark:text-gray-300 rounded-2xl font-bold flex items-center justify-center active:scale-[0.95] transition-all hover:bg-gray-100 dark:hover:bg-gray-800/40 disabled:opacity-50">
                  <x-heroicon-m-chevron-left class="w-5 h-5 mr-1" />
                  Back
              </button>
          @endif

          <button 
              type="button"
              wire:click="{{ $currentStep == 6 ? 'saveInspection' : 'next' }}" 
              @click="if (@js($currentStep) !== 6) window.scrollTo({top: 0, behavior: 'smooth'})"
              wire:loading.attr="disabled"
              class="flex-[2] py-4 bg-primary-600 hover:bg-primary-700 text-white rounded-2xl font-bold flex items-center justify-center shadow-lg shadow-primary-500/20 active:scale-[0.95] transition-all disabled:opacity-70">

              <svg wire:loading wire:target="saveInspection" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>

              <span>
                  {{ $currentStep == 6 ? 'Complete Inspection' : 'Continue' }}
              </span>

              <x-heroicon-m-chevron-right wire:loading.remove wire:target="saveInspection" class="w-5 h-5 ml-1" />
          </button>
      </div>
  </div>

  <div class="h-32"></div>

    <div 
        v-if="activeZone"
        @class([
            'fixed inset-0 bg-black/40 z-40 transition-opacity duration-300',
            'opacity-100' => $activeZone,
            'opacity-0 pointer-events-none' => !$activeZone
        ])
        wire:click="closeZone">
    </div>

    <div 
        @class([
            'fixed inset-0 bg-black/40 z-40 transition-opacity duration-300',
            'opacity-100' => $activeZone,
            'opacity-0 pointer-events-none' => !$activeZone
        ])
        wire:click="closeZone">
    </div>

    <div 
        x-data="{ 
            y: 0, 
            startH: 0,
            isDragging: false,
            active: @entangle('activeZone'),
            close() {
                this.y = 0;
                this.isDragging = false;
                this.$wire.closeZone();
            }
        }"
        x-init="$watch('active', value => { if(!value) y = 0 })"
        @class([
            'fixed inset-x-0 bottom-0 z-50 w-full touch-none',
            'bg-white dark:bg-gray-900 shadow-[0_-20px_50px_-12px_rgba(0,0,0,0.2)] rounded-t-[2.5rem] max-h-[85vh]'
        ])
        :style="active ? `transform: translateY(${y}px)` : `transform: translateY(100%)`"
        :class="isDragging ? '' : 'transition-transform duration-300 ease-out'"
    >
        <div class="w-full flex justify-center pt-4 pb-8 cursor-grab active:cursor-grabbing select-none"
            @mousedown="isDragging = true; startH = $event.clientY"
            @mousemove.window="if(isDragging) { let diff = $event.clientY - startH; y = Math.max(0, diff); }"
            @mouseup.window="if(isDragging) { if(y > 160) close(); else y = 0; isDragging = false; }"
            @touchstart="isDragging = true; startH = $event.touches[0].clientY"
            @touchmove="if(isDragging) { let diff = $event.touches[0].clientY - startH; y = Math.max(0, diff); }"
            @touchend="if(y > 160) close(); else y = 0; isDragging = false;">
            <div class="w-14 h-1.5 bg-gray-200 dark:bg-gray-800 rounded-full"></div>
        </div>

        @if($activeZone)
            <div class="flex flex-col h-full max-h-[80vh] overflow-hidden">
                <div class="flex items-center justify-between px-8 py-5 border-b border-gray-50 dark:border-gray-800/50">
                    <div>
                        <h2 class="text-xl text-gray-900 dark:text-white font-black">Zone {{ $activeZone }}</h2>
                        <p class="text-sm text-gray-400 dark:text-gray-500 font-bold">Inspection Details</p>
                    </div>
                    <button @click="close()" class="p-2.5 bg-gray-50 dark:bg-gray-800 rounded-full text-gray-400 dark:text-gray-500 hover:text-black dark:hover:text-white transition">
                        <x-heroicon-o-x-mark class="w-5 h-5" />
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto p-6 space-y-8 no-scrollbar overscroll-contain">
                    <div
                         x-data="{ 
                            localPreview: null,
                            handleFile(e) {
                                const file = e.target.files[0];
                                if (file) {
                                    this.localPreview = URL.createObjectURL(file);
                                }
                            }
                        }" 
                        @photo-removed-{{ $activeZone }}.window="localPreview = null"
                         class="flex flex-col items-center w-full">
                        <label class="relative w-full border-2 border-dashed border-gray-200 dark:border-gray-800 rounded-[2rem] p-2 flex flex-col items-center justify-center cursor-pointer hover:bg-primary-50/50 dark:hover:bg-primary-900/10 hover:border-primary-200 dark:hover:border-primary-800 transition group overflow-hidden">
                            
                            <input type="file" 
                                wire:model="inspectionData.{{ $activeZone }}.photo" 
                                @change="handleFile"
                                wire:key="camera-input-{{ $activeZone }}"
                                class="hidden" 
                                accept="image/*" 
                                capture="environment"
                                >
                            <template x-if="localPreview">
                              <div class="relative w-full h-64 rounded-[1.8rem] overflow-hidden">
                                  {{-- The Image --}}
                                  <img :src="localPreview" class="w-full h-full object-cover">

                                  {{-- Badge: New Capture --}}
                                  <div class="absolute top-4 left-4 px-3 py-1 bg-primary-600 text-white text-sm rounded-full shadow-lg font-bold">
                                      New Capture
                                  </div>

                                  {{-- Remove Button --}}
                                  <button type="button" 
                                      @click.stop="localPreview = null; $wire.removePhoto('{{ $activeZone }}')" 
                                      class="absolute top-4 right-4 p-2 bg-red-500 text-white rounded-full shadow-lg z-20 hover:bg-red-600 transition"
                                  >
                                      <x-heroicon-s-x-mark class="w-4 h-4" />
                                  </button>

                                  {{-- Loading Spinner Overlay --}}
                                  <div wire:loading wire:target="inspectionData.{{ $activeZone }}.photo" 
                                      class="absolute inset-0 bg-black/40 flex items-center justify-center z-10">
                                      <div class="flex flex-col items-center gap-2">
                                          <svg class="animate-spin h-8 w-8 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                          </svg>
                                          <span class="text-white text-xs font-bold">Uploading...</span>
                                      </div>
                                  </div>
                              </div>
                          </template>
                          	
                          	<template x-if="!localPreview">
                            @php
                                $currentPhoto = $inspectionData[$activeZone]['photo'] ?? null;
                                $prePhoto = $inspectionData[$activeZone]['pre_photo'] ?? null;
                                $hasTempPhoto = $currentPhoto && method_exists($currentPhoto, 'temporaryUrl');
                            @endphp

                            @if($hasTempPhoto)
                                <div class="relative w-full h-64 rounded-[1.8rem] overflow-hidden" 
                                    wire:loading.remove wire:target="inspectionData.{{ $activeZone }}.photo">
                                    
                                    {{-- Alpine.js prevents the 'broken image' icon by hiding the <img> until it is actually loaded --}}
                                    <div x-data="{ loaded: false }" class="w-full h-full bg-gray-100 dark:bg-gray-800">
                                        <img src="{{ $this->getPhotoPreview($activeZone) }}" 
                                            x-show="loaded"
                                            @load="loaded = true"
                                            class="w-full h-full object-cover transition-opacity duration-300"
                                            :class="loaded ? 'opacity-100' : 'opacity-0'">
                                        
                                        {{-- Placeholder while the browser decodes the high-res camera photo --}}
                                        <div x-show="!loaded" class="absolute inset-0 flex items-center justify-center">
                                            <x-heroicon-o-photo class="w-10 h-10 text-gray-300 animate-pulse" />
                                        </div>
                                    </div>

                                    <div class="absolute top-4 left-4 px-3 py-1 bg-primary-600 text-white text-sm rounded-full shadow-lg font-bold">New Capture</div>
                                    
                                    <button type="button" 
                                        {{-- Use the new PHP method instead of $set --}}
                                        wire:click.stop="removePhoto('{{ $activeZone }}')" 
                                        class="absolute top-4 right-4 p-2 bg-red-500 text-white rounded-full shadow-lg z-20 hover:bg-red-600 transition"
                                    >
                                        <x-heroicon-s-x-mark class="w-4 h-4" />
                                    </button>
                                </div>
                            @elseif($prePhoto)
                                <div class="relative w-full h-64 rounded-[1.8rem] overflow-hidden bg-gray-100 dark:bg-gray-800"
                                    wire:loading.remove wire:target="inspectionData.{{ $activeZone }}.photo">
                                    <img src="{{ $this->getPrePhotoUrl($activeZone) }}" class="w-full h-full object-cover brightness-75 group-hover:brightness-90 transition-all" onerror="this.src='https://placehold.co/600x400?text=Photo+Not+Found'">
                                    <div class="absolute inset-0 flex flex-col items-center justify-center bg-black/20 group-hover:bg-black/10 transition-colors">
                                        <div class="bg-red-600 text-white text-sm px-4 py-1.5 rounded-full shadow-xl mb-2 font-bold">Pre-existing Damage</div>
                                        <div class="bg-white/20 backdrop-blur-md p-3 rounded-full text-white border border-white/30 group-hover:scale-110 transition-transform">
                                            <x-heroicon-o-camera class="w-8 h-8" />
                                        </div>
                                        <p class="text-white text-sm font-bold mt-2 opacity-80">Tap to re-capture</p>
                                    </div>
                                </div>
                            @else
                                <div class="py-12 flex flex-col items-center" 
                                    wire:loading.remove wire:target="inspectionData.{{ $activeZone }}.photo">
                                    <div class="bg-primary-50 dark:bg-primary-900/20 p-4 rounded-full text-primary-600 dark:text-primary-400 mb-3 group-hover:scale-110 transition-transform">
                                        <x-heroicon-o-camera class="w-10 h-10" />
                                    </div>
                                    <span class="text-xs text-primary-700 dark:text-primary-400 font-bold ">Capture Image</span>
                                </div>
                            @endif

                            {{-- Loading overlay with z-index fix --}}
                            <div wire:loading wire:target="inspectionData.{{ $activeZone }}.photo" class="absolute inset-0 bg-white/90 dark:bg-gray-900/90 backdrop-blur-sm flex items-center justify-center z-50 rounded-[1.8rem]">
                                <div class="flex flex-col items-center gap-2">
                                    <svg class="animate-spin h-8 w-8 text-primary-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span class="text-sm font-bold text-gray-500">Processing Photo...</span>
                                </div>
                            </div>
                          </template>
                        </label>
                    </div>

                    <div>
                        <p class="font-bold text-gray-800 dark:text-gray-200 mb-4 text-xs uppercase">Select Current Condition</p>
                        
                        {{-- Initialize Alpine with the current state from Livewire --}}
                        <div x-data="{ 
                            {{-- Initialize from PHP, but don't 'entangle' it --}}
                            selected: @js($inspectionData[$activeZone]['condition'] ?? 'good')
                        }" 
                        {{-- Watch for changes to activeZone to reset the local selection --}}
                        x-effect="selected = @js($inspectionData[$activeZone]['condition'] ?? 'good')"
                        class="grid grid-cols-3 gap-3">
                        
                        @foreach(['Good', 'Fair', 'Damaged'] as $status)
                            @php $statusLower = strtolower($status); @endphp
                            
                            <button type="button" 
                                @click="
                                    selected = '{{ $statusLower }}';
                                    {{-- Manually tell Livewire to update the array --}}
                                    $wire.set('inspectionData.{{ $activeZone }}.condition', '{{ $statusLower }}');
                                "
                                :class="selected === '{{ $statusLower }}' 
                                    ? 'border-primary-600 bg-primary-50 dark:bg-primary-900 text-primary-700 dark:text-primary-400 shadow-sm' 
                                    : 'border-gray-50 dark:border-gray-800 bg-gray-50 dark:bg-gray-800 text-gray-400 dark:text-gray-500'"
                                class="py-4 px-1 rounded-2xl border-2 text-center text-xs font-black transition-all"
                            >
                                <div class="flex flex-col items-center gap-2">
                                    <template x-if="selected === '{{ $statusLower }}'">
                                        <x-heroicon-s-check-circle class="w-5 h-5 text-primary-600 dark:text-primary-400" />
                                    </template>
                                    
                                    <template x-if="selected !== '{{ $statusLower }}'">
                                        <div class="w-5 h-5"></div>
                                    </template>

                                    {{ strtoupper($status) }}
                                </div>
                            </button>
                        @endforeach
                    </div>
                    </div>

                    <div class="pb-10">
                        <label class="block font-bold text-gray-800 dark:text-gray-200 mb-2 text-xs">Return Notes</label>
                        <textarea 
                            wire:model.defer="inspectionData.{{ $activeZone }}.notes" 
                            class="w-full border-transparent bg-gray-50 dark:bg-gray-800 dark:text-white rounded-2xl focus:ring-primary-500  p-4 text-base transition-all" 
                            rows="3" 
                            placeholder="Describe current state...">
                        </textarea>
                    </div>
                </div>

                <div class="p-6 bg-white dark:bg-gray-900 border-t border-gray-50 dark:border-gray-800 pb-12">
                    <button @click="close()" class="w-full bg-primary-600 text-white py-4 rounded-2xl font-bold text-lg shadow-xl active:scale-[0.98] transition">
                        Update Zone
                    </button>
                </div>
            </div>
        @endif
    </div>
    {{-- 1. Load the Library via CDN --}}
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('signaturePad', (value) => ({
            signaturePad: null,
            value: value,

            init() {
                this.$nextTick(() => {
                    this.initSignature();
                    
                    // Watch for visibility changes (if Step 6 was hidden)
                    const observer = new MutationObserver(() => {
                        if (this.$refs.canvas.offsetWidth > 0) {
                            this.resizeCanvas();
                        }
                    });
                    
                    observer.observe(this.$el, { attributes: true, attributeFilter: ['style', 'class'] });
                });
            },

            initSignature() {
                const canvas = this.$refs.canvas;
                
                this.signaturePad = new SignaturePad(canvas, {
                    backgroundColor: 'white',
                    penColor: '#000',
                    velocityFilterWeight: 0.7,
                });

                this.resizeCanvas();

                window.addEventListener("resize", () => this.resizeCanvas());

                this.signaturePad.addEventListener("endStroke", () => {
                    this.value = this.signaturePad.toDataURL('image/png');
                });
            },

            resizeCanvas() {
                const canvas = this.$refs.canvas;
                const ratio = Math.max(window.devicePixelRatio || 1, 1);
                
                // This is the fix: manually pulling the parent's computed height 
                // if offsetHeight is being weird.
                const parent = canvas.parentElement;
                
                canvas.width = parent.clientWidth * ratio;
                canvas.height = parent.clientHeight * ratio;
                
                canvas.getContext("2d").scale(ratio, ratio);
                
                // Reset coordinate mapping
                this.signaturePad.clear(); 
                
                // If we already had a signature, we don't want to lose it on resize
                // But for initial setup, this ensures the "dead zone" is gone.
            },

            clear() {
                this.signaturePad.clear();
                this.value = null;
            }
        }))
    });
</script>

<style>
    /* Prevent scrolling the whole page while signing on mobile */
    .touch-none {
        touch-action: none;
    }
    
    /* Ensure the cursor looks like a pen when over the signature area */
    .cursor-crosshair {
        cursor: crosshair;
    }
</style>
</div>