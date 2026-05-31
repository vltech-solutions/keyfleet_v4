<x-filament-panels::page>
    <x-booking-details-for-inspection :booking="$booking" />

    @if($inspectionType === 'post')
        <div class="p-4 mb-4 text-sm text-gray-800 bg-yellow-100 rounded-lg dark:bg-yellow-900 dark:text-yellow-200">
            You are performing a <strong>Post Vehicle Inspection</strong>. Pre inspection data has been preloaded for reference.
        </div>
    @endif


    <form wire:submit="save" class="space-y-6">
        <div class="filament-form-container">
            {{ $this->form }}
        </div>
        <div class="flex justify-end gap-3 mt-6">
            <x-filament::button href="/admin/bookings" color="gray" tag="a">
                Cancel
            </x-filament::button>
            <x-filament::button type="submit" color="primary" size="lg" >
                Finalize {{ ucfirst($inspectionType) }} Inspection
            </x-filament::button>
        </div>
    </form>
    <x-filament-actions::modals />
</x-filament-panels::page>