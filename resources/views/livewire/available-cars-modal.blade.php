<div>
    <x-filament::modal id="available-cars" wire:model="showAvailableCarsModal">
        <x-slot name="header">
            <h2 class="text-lg font-bold">Available Cars</h2>
        </x-slot>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3">
            @foreach ($availableCars as $car)
                <div class="overflow-hidden border rounded-lg shadow-sm">
                    <img src="{{ Storage::url($car->image) }}" class="object-cover w-full h-40" alt="Car image">
                    <div class="p-4">
                        <div class="font-semibold">{{ $car->name }}</div>
                        <div class="text-sm text-gray-600">{{ $car->brand }} {{ $car->model }} ({{ $car->year }})</div>
                        <div class="mt-1 text-xs text-gray-500">Color: {{ $car->color }} | Seats: {{ $car->seat_count }}</div>
                    </div>
                </div>
            @endforeach
        </div>

        <x-slot name="footer">
            <x-filament::button wire:click="$set('showAvailableCarsModal', false)">
                Close
            </x-filament::button>
        </x-slot>
    </x-filament::modal>
</div>
