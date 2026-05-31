@props(['enabledRequirements' => []])

<div class="space-y-6">
    <div class="pb-2 border-b dark:border-gray-700">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white text-balance">Upload Requirements</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400">The owner requires these documents to verify your booking.</p>
    </div>

    <div class="grid grid-cols-1 gap-6">
        @php
            $requirementTypes = \App\Models\RequirementTypes::whereIn('id', $enabledRequirements)->get();
        @endphp

        @forelse($requirementTypes as $req)
            <div class="space-y-2">
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">
                    {{ $req->label }} @if($req->required) <span class="text-red-500">*</span> @endif
                </label>
                
                @if($req->helper)
                    <p class="text-xs text-gray-500 mb-2">{{ $req->helper }}</p>
                @endif

                <div 
                    x-data="{ isUploading: false, progress: 0 }"
                    x-on:livewire-upload-start="isUploading = true"
                    x-on:livewire-upload-finish="isUploading = false"
                    x-on:livewire-upload-error="isUploading = false"
                    x-on:livewire-upload-progress="progress = $event.detail.progress"
                    class="relative"
                >
                    <div class="flex items-center justify-center w-full">
                        <label class="flex flex-col items-center justify-center w-full h-40 border-2 border-dashed rounded-2xl cursor-pointer transition-all
                            {{ $errors->has('requirements.'.$req->id) ? 'border-red-300 bg-red-50 dark:bg-red-900/10' : 'border-gray-300 bg-gray-50 hover:bg-gray-100 dark:bg-gray-800 dark:border-gray-600 dark:hover:border-gray-500' }}">
                            
                            <div class="flex flex-col items-center justify-center pt-5 pb-6 text-center">
                                @php
                                    $previewUrl = $this->getRequirementUrl($req->id);
                                @endphp

                                @if($previewUrl)
                                    <div class="relative group">
                                        <img src="{{ $previewUrl }}" class="h-24 w-40 object-cover rounded-lg mb-2 shadow-md border-2 border-blue-500">
                                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center rounded-lg">
                                            <span class="text-white text-[10px] font-bold uppercase">Replace Photo</span>
                                        </div>
                                    </div>
                                    <p class="text-[10px] text-blue-600 dark:text-blue-400 font-bold uppercase tracking-wider">
                                        {{ is_string($this->requirements[$req->id] ?? null) ? 'Verified from Profile' : 'Newly Uploaded' }}
                                    </p>
                                @else
                                    <svg class="w-8 h-8 mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                    </svg>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 px-4">
                                        Click to upload <span class="font-bold text-blue-600">Image</span>
                                    </p>
                                @endif
                            </div>

                            <input type="file" wire:model="requirements.{{ $req->id }}" class="hidden" accept="image/*" />
                        </label>
                    </div>

                    <div x-show="isUploading" class="absolute inset-0 bg-white/80 dark:bg-gray-900/80 rounded-2xl flex flex-col items-center justify-center z-10">
                        <div class="w-2/3 bg-gray-200 rounded-full h-1.5 mb-2 overflow-hidden">
                            <div class="bg-blue-600 h-1.5 rounded-full transition-all duration-300" :style="`width: ${progress}%` text-white"></div>
                        </div>
                        <span class="text-[10px] font-bold text-blue-600" x-text="progress + '%'"></span>
                    </div>
                </div>

                @error('requirements.'.$req->id) 
                    <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p> 
                @enderror
            </div>
        @empty
            <div class="text-center py-10 bg-gray-50 dark:bg-gray-800 rounded-2xl border border-dashed border-gray-300 dark:border-gray-700">
                <p class="text-sm text-gray-500">No specific requirements needed for this car.</p>
            </div>
        @endforelse
    </div>
</div>