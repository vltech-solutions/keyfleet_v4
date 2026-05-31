<div>
    @if ($record->contact_number)
        <div class="flex items-center gap-1">
            <x-heroicon-o-phone class="w-4 h-4 text-gray-500" />
            {{ $record->contact_number }}
        </div>
    @endif

    @if ($record->email)
        <div class="flex items-center gap-1">
            <x-heroicon-o-envelope class="w-4 h-4 text-gray-500" />
            {{ $record->email }}
        </div>
    @endif

    @if ($record->facebook_name)
        <div class="flex items-center gap-1">
            <x-heroicon-o-user-circle class="w-4 h-4 text-gray-500" />
            <a href="{{ $record->facebook_name }}" target="_blank" class="text-primary-600 hover:underline">
                View Facebook Profile
            </a>
        </div>
    @endif
</div>
