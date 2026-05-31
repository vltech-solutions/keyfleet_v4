@props([
    'images' => [],
    'carName' => 'Car'
])

<section class="mb-8 animate-fade-up" 
    x-data="{ 
        mainImage: 0, 
        lightbox: false,
        totalImages: {{ count($images) }},
        scrollToThumb(index) {
            const container = this.$refs.thumbContainer;
            if(!container) return;
            const thumb = container.children[index];
            container.scrollTo({
                left: thumb.offsetLeft - container.offsetWidth / 2 + thumb.offsetWidth / 2,
                behavior: 'smooth'
            });
        }
    }">
    
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        {{-- Main Image Display --}}
        <div class="md:col-span-3">
            <div class="aspect-[16/10] rounded-3xl overflow-hidden bg-gray-100 cursor-zoom-in group relative shadow-sm border border-gray-200/50"
                @click="lightbox = true"
                x-ref="mainImageContainer">
                
                @foreach($images as $i => $img)
                    <img
                        x-show="mainImage === {{ $i }}"
                        x-transition:enter="transition ease-out duration-500"
                        x-transition:enter-start="opacity-0 scale-105"
                        x-transition:enter-end="opacity-100 scale-100"
                        src="{{ $img }}"
                        alt="{{ $carName }} view {{ $i + 1 }}"
                        class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                    >
                @endforeach

                <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                
                <div class="absolute bottom-4 right-4 bg-white/90 backdrop-blur-md text-gray-900 text-xs font-bold px-4 py-2 rounded-full opacity-0 group-hover:opacity-100 translate-y-2 group-hover:translate-y-0 transition-all duration-300 flex items-center gap-2 shadow-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
                    </svg>
                    VIEW GALLERY
                </div>
            </div>
        </div>

        {{-- Thumbnails --}}
        <div class="relative">
            <div x-ref="thumbContainer"
                class="flex md:flex-col gap-3 overflow-x-auto md:overflow-y-auto no-scrollbar snap-x snap-mandatory pb-2 md:pb-0 scroll-smooth"
                x-init="$nextTick(() => { if($refs.mainImageContainer) $el.style.maxHeight = $refs.mainImageContainer.offsetHeight + 'px' })">
                
                @foreach($images as $i => $img)
                    <button
                        type="button"
                        @click="mainImage = {{ $i }}; scrollToThumb({{ $i }})"
                        class="relative flex-shrink-0 w-20 h-20 md:w-full md:h-auto md:aspect-square rounded-2xl overflow-hidden transition-all duration-300 snap-center group"
                        :class="mainImage === {{ $i }} ? 'ring-2 ring-blue-600 ring-offset-2' : 'ring-1 ring-gray-200 opacity-70 hover:opacity-100'">
                        
                        <img src="{{ $img }}" class="w-full h-full object-cover transform transition-transform duration-500 group-hover:scale-110">
                    </button>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Lightbox --}}
    <template x-if="lightbox">
        <div class="fixed inset-0 z-[200] bg-black/95 flex items-center justify-center p-4"
            x-transition.opacity
            @click.self="lightbox = false" 
            @keydown.escape.window="lightbox = false">
            
            <button @click="lightbox = false" class="absolute top-6 right-6 text-white/70 hover:text-white transition-colors z-[210]">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>

            <button @click="mainImage = (mainImage === 0) ? totalImages - 1 : mainImage - 1"
                    class="absolute left-4 text-white/50 hover:text-white transition-colors p-2 z-[210]">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
            </button>

            <div class="relative max-w-5xl max-h-[85vh] flex items-center justify-center">
                @foreach($images as $i => $img)
                    <img x-show="mainImage === {{ $i }}"
                        x-transition:enter="transition duration-300"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        src="{{ $img }}"
                        class="max-w-full max-h-[85vh] rounded-lg shadow-2xl object-contain">
                @endforeach
            </div>

            <button @click="mainImage = (mainImage === totalImages - 1) ? 0 : mainImage + 1"
                    class="absolute right-4 text-white/50 hover:text-white transition-colors p-2 z-[210]">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
            </button>

            <div class="absolute bottom-8 left-1/2 -translate-x-1/2 bg-white/10 backdrop-blur-md px-4 py-1 rounded-full text-white text-sm">
                <span x-text="mainImage + 1"></span> / <span x-text="totalImages"></span>
            </div>
        </div>
    </template>
</section>