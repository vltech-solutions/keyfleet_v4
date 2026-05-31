<div x-data="{ loading: true }" class="w-full">
    {{-- Desktop View (md and up) --}}
    <div class="relative w-full" style="padding-top: 56.25%;">
        <iframe
            x-on:load="loading = false"
            src="{{ $previewUrl }}"
            class="absolute top-0 left-0 w-full h-full border rounded-md"
            frameborder="0"
            loading="eager"
        ></iframe>

        <div x-show="loading" class="absolute top-0 left-0 flex items-center justify-center w-full h-full bg-white dark:bg-gray-900">
            <span class="text-sm text-gray-500 dark:text-gray-300">Loading contract preview...</span>
        </div>
    </div>

    {{-- Mobile View (sm only) --}}
    {{-- <div class="mt-4 text-center md:hidden">
        <a 
            href="{{ $previewUrl }}" 
            target="_blank" 
            class="inline-block px-4 py-2 text-sm font-medium text-white transition rounded bg-primary-600 hover:bg-primary-700"
        >
            Tap to view contract PDF
        </a>
        <p class="mt-2 text-xs text-gray-500">Preview will open in a new tab.</p>
    </div> --}}
</div>
