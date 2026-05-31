@if($inspection && $inspection->items->count())
    <ul class="space-y-3">
        @foreach($inspection->items as $item)
            {{-- {{ json_encode($item) }} --}}
            <li class="border-b border-gray-100 dark:border-gray-700 pb-2" x-data="{ open: false }">
                <div class="flex justify-between items-start">
                    <span class="font-medium text-xs">Zone {{ $item->zone_id }}</span>
                </div>
                @if($item->notes) <p class="text-[10px] text-gray-500">Rem: {{ $item->notes }}</p> @endif
                @if($item->photo_path)
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('s3')->temporaryUrl($item->photo_path, now()->addMinutes(15)) }}" 
                         @click="open = true" class="mt-1 max-h-16 w-auto rounded border cursor-zoom-in"/>
                    
                    <div x-show="open" class="fixed inset-0 bg-black/80 flex items-center justify-center z-[9999]" @click.away="open = false">
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('s3')->temporaryUrl($item->photo_path, now()->addMinutes(15)) }}" class="max-h-[90vh] max-w-[90vw] shadow-2xl"/>
                        <button @click="open = false" class="absolute top-5 right-5 text-white text-3xl">&times;</button>
                    </div>
                @endif
            </li>
        @endforeach
    </ul>
@else
    <span class="text-gray-400 italic text-xs">No reported issues</span>
@endif