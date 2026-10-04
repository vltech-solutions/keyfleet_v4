<x-filament-panels::page>
    <form wire:submit='changePassword' class='max-w-2xl space-y-6'>
        {{ $this->form }}

        <div class='flex justify-end'>
            <x-filament::button type='submit' icon='heroicon-o-key'>
                Change Password
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
