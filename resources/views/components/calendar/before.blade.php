<div class="flex flex-col w-full gap-2">
    {{-- Row: Full Layout --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        
        {{-- Left Side: Prev / Next --}}
        <div class="flex items-center gap-2">
            <x-filament::button wire:click="goToPreviousMonth">Prev</x-filament::button>
            <x-filament::button wire:click="goToNextMonth">Next</x-filament::button>
        </div>

        {{-- Right Side: Select + Download --}}
        <div class="flex items-center gap-2">
            <select wire:model.live="carId" class="w-32 px-2 py-1 border rounded sm:w-40 dark:text-gray-800">
                <option value="">All Cars</option>
                @foreach(\App\Models\Car::all() as $car)
                    <option value="{{ $car->id }}">{{ $car->name }}</option>
                @endforeach
            </select>

            @if (auth()->user()->hasActiveSubscription())
                <x-filament::button @click="capture">
                    Download
                </x-filament::button>
            @endif
        </div>

    </div>
</div>
