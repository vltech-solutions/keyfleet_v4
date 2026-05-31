<div
    @if($eventClickEnabled)
        wire:click.stop="onEventClick('{{ $event['id'] }}')"
    @endif
    class="px-1 py-1 text-center rounded-lg cursor-pointer"
    title="Renter: {{ $event['renter_name'] }}"
>
    @php
        // Determine event image path
        $imagePath = $event['image'];
        $imageUrl = ($event['image'] && Storage::disk('public')->exists($imagePath))
            ? Storage::url($imagePath)
            : Storage::url('images/default-car.png');

        // Count how many events exist in the current day
        $eventCount = count($events);
    @endphp

    {{-- Adjust image size depending on how many events are in the day --}}
    @if($eventCount === 1)
        {{-- Large image for single event --}}
        <img
            src="{{ $imageUrl }}"
            alt="Car Image"
            loading="lazy"
            class="mx-auto"
            style="max-width: 90%; margin-top: -2px; height: auto; width: auto;"
        />
    @elseif($eventCount === 2)
        {{-- Medium size for 2 events --}}
        <img
            src="{{ $imageUrl }}"
            alt="Car Image"
            loading="lazy"
            class="mx-auto"
            style="max-height: 50px; max-width: 110px; margin-top: -10px; margin-bottom: 3px; height: auto; width: auto;"
        />
    @elseif($eventCount > 12)
        {{-- Tiny size for 4-column layout (13+ events) --}}
        <img
            src="{{ $imageUrl }}"
            alt="Car Image"
            loading="lazy"
            class="mx-auto"
            style="max-height: 18px; max-width: 35px; margin-top: -4px; margin-bottom: 1px; height: auto; width: auto;"
        />
    @elseif($eventCount > 6)
        {{-- Extra small for 3-column layout (7–12 events) --}}
        <img
            src="{{ $imageUrl }}"
            alt="Car Image"
            loading="lazy"
            class="mx-auto"
            style="max-height: 25px; max-width: 50px; margin-top: -6px; margin-bottom: 2px; height: auto; width: auto;"
        />
    @else
        {{-- Default small size for 3–6 events --}}
        <img
            src="{{ $imageUrl }}"
            alt="Car Image"
            loading="lazy"
            class="mx-auto"
            style="max-height: 35px; max-width: 70px; margin-top: -4px; height: auto; width: auto;"
        />
    @endif
</div>
