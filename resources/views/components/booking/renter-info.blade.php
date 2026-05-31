@props([
    'company' => null,
])

<div class="space-y-6" 
     x-data="{ 
        uploading: false,
        async handleQR(e) {
            const file = e.target.files[0];
            if (!file) return;
            this.uploading = true;
            const reader = new FileReader();
            reader.onload = async (event) => {
                const image = new Image();
                image.src = event.target.result;
                image.onload = async () => {
                    const canvas = document.createElement('canvas');
                    canvas.width = image.width;
                    canvas.height = image.height;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(image, 0, 0);
                    const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                    const code = jsQR(imageData.data, imageData.width, imageData.height);
                    if (code) {
                        @this.set('repeat_token', code.data);
                        await @this.submitRepeatToken();
                    } else {
                        alert('QR code not detected. Please try a clearer image.');
                    }
                    this.uploading = false;
                    e.target.value = '';
                };
            };
            reader.readAsDataURL(file);
        }
     }">

    <div class="relative group overflow-hidden border border-gray-200 dark:border-gray-700 rounded-2xl bg-white dark:bg-gray-800 transition-all hover:border-blue-300 dark:hover:border-blue-800">
        <div class="p-4 flex flex-col sm:flex-row items-center gap-4">
            <div class="flex-shrink-0 flex items-center justify-center w-12 h-12 rounded-xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400">
                <template x-if="!uploading">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                    </svg>
                </template>
                <template x-if="uploading">
                    <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </template>
            </div>
            
            <div class="flex-1 text-center sm:text-left">
                <h4 class="text-sm font-bold text-gray-900 dark:text-white">Quick Auto-fill</h4>
                <p class="text-xs text-gray-500 dark:text-gray-400">Upload your QR code to instantly fill your details.</p>
            </div>

            <label class="relative inline-flex items-center px-4 py-2 bg-gray-900 dark:bg-white text-white dark:text-gray-900 text-xs font-bold rounded-lg cursor-pointer hover:opacity-90 transition-opacity">
                <span>Select QR Image</span>
                <input type="file" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" @change="handleQR" :disabled="uploading">
            </label>
        </div>
        @error('repeat_token') 
            <div class="px-4 pb-3 text-[11px] text-red-500 font-medium border-t border-red-50 dark:border-red-900/20 bg-red-50/30 dark:bg-red-900/10 pt-2">
                {{ $message }}
            </div>
        @enderror
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div class="md:col-span-2">
            <label class="block mb-1.5 text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Full Name</label>
            <input type="text" wire:model.defer="name" placeholder="Full Name"
                class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:border-blue-500 focus:ring-0 transition-all dark:text-white">
            @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block mb-1.5 text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Contact Number</label>
            <input type="text" wire:model.defer="contact" placeholder="09123456789"
                class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:border-blue-500 focus:ring-0 transition-all dark:text-white">
            @error('contact') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block mb-1.5 text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Email Address</label>
            <input type="email" wire:model.defer="email" placeholder="email@example.com"
                class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:border-blue-500 focus:ring-0 transition-all dark:text-white">
            @error('email') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        <div class="md:col-span-2">
            <label class="block mb-1.5 text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Facebook Profile Link</label>
            <input type="url" wire:model.defer="facebook" placeholder="https://facebook.com/username"
                class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:border-blue-500 focus:ring-0 transition-all dark:text-white">
        </div>

        <div class="md:col-span-2">
            <label class="block mb-1.5 text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Home Address</label>
            <textarea wire:model.defer="address" rows="2" placeholder="Complete Address"
                class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:border-blue-500 focus:ring-0 transition-all dark:text-white resize-none"></textarea>
            @error('address') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        
    </div>

    <div class="mb-4">
        <label class="block mb-1 text-sm font-medium dark:text-white">How did you find us? <span class="text-red-500">*</span></label>
        <select wire:model.live="source"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg dark:text-white focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:focus:border-blue-400">
            <option value="">Please select one</option>
            @if(!empty($company->sources))
                @foreach($company->sources as $source)
                    <option value="{{ $source->id }}">{{ $source->source }}</option>
                @endforeach
            @endif
        </select>
        @error('source') 
        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
        @enderror
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
</div>