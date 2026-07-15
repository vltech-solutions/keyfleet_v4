@props(['car', 'company'])

@php
    $thumbnail = $car->images->firstWhere('image_type', 'thumbnail') ?? $car->images->first();
    $imageUrl = $thumbnail && !empty($thumbnail->path) 
        ? Storage::disk('s3')->temporaryUrl($thumbnail->path, now()->addMinutes(5))
        : ($car->image ? Storage::url($car->image) : null);
    
    $defaultImage = 'data:image/svg+xml,'.urlencode('
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 300" width="400" height="300">
            <rect width="400" height="300" fill="#f3f4f6"/>
            <text x="200" y="150" font-family="Arial" font-size="24" fill="#9ca3af" text-anchor="middle">No Image</text>
            <svg x="160" y="60" width="80" height="80" fill="#d1d5db">
                <path d="M20 40 L60 40 L60 20 L20 20 Z"/>
                <path d="M40 60 L40 70 L60 70 L60 60 Z"/>
            </svg>
        </svg>
    ');
@endphp

<a href="{{ route('car.details', ['tenant' => $company->slug, 'car' => $car->id]) }}" 
   class="block group h-full">
    <div class="bg-white dark:bg-gray-800 rounded-2xl overflow-hidden shadow-sm  duration-300 border border-gray-100 dark:border-gray-700 h-full flex flex-col cursor-pointer">
        
        <!-- Image Container - Only this scales on hover -->
        <div class="relative overflow-hidden aspect-[4/3] bg-gray-100 dark:bg-gray-700 flex-shrink-0">
            <div class="w-full h-full group-hover:scale-105 transition-transform duration-500">
                @if($imageUrl)
                    <img src="{{ $imageUrl }}" 
                        alt="{{ $car->brand }} {{ $car->model }}"
                        class="w-full h-full object-cover"
                        loading="lazy">
                @else
                    <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-600">
                        <svg class="w-20 h-20 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7h8m-8 4h8m-8 4h8M4 4h16v16H4z"/>
                        </svg>
                    </div>
                @endif
            </div>
            
            <!-- Status Badges - Simple Tailwind style -->
            <div class="absolute top-3 left-3 flex flex-col gap-1.5">
                @if($car->is_available)
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">
                        Available
                    </span>
                @endif
                
                @if($car->is_featured ?? false)
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 flex items-center gap-1">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        Featured
                    </span>
                @endif
            </div>
            
            <!-- Discount Badge (if applicable) -->
            @if($car->discount_percent ?? false)
                <div class="absolute top-3 right-3">
                    <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">
                        -{{ $car->discount_percent }}%
                    </span>
                </div>
            @endif
        </div>
        
        <!-- Content - No hover transform -->
        <div class="p-3 sm:p-5 flex-grow flex flex-col">
            <div class="flex-grow">
                <!-- Car Title & Type -->
                <div class="flex items-start justify-between mb-1 sm:mb-2">
                    <div class="min-w-0 flex-1">
                        <h4 class="text-md font-bold text-gray-900 dark:text-white truncate">{{ $car->brand }} {{ $car->model }}</h4>
                        <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-gray-100 text-gray-700 dark:bg-gray-900/30 dark:text-gray-400">
                            {{ $car->carType->car_type ?? 'Uncategorized' }}
                        </span>
                    </div>
                </div>
                
                <!-- Car Specs - 2 Columns x 2 Rows -->
                <div class="grid grid-cols-2 gap-x-2 sm:gap-x-4 gap-y-1 sm:gap-y-2 text-[10px] sm:text-xs text-gray-500 dark:text-gray-400 mt-2 sm:mt-3 pt-2 sm:pt-3 border-t border-gray-100 dark:border-gray-700">
                    <!-- Row 1: Seats -->
                    <span class="flex items-center gap-1 sm:gap-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 sm:h-4 sm:w-4 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                        <span class="truncate">
                            @if($car->seat_count)
                                {{ $car->seat_count }} seats
                            @else
                                <span class="text-gray-400 dark:text-gray-500">No data</span>
                            @endif
                        </span>
                    </span>
                    
                    <!-- Row 1: Gas/Fuel -->
                    <span class="flex items-center gap-1 sm:gap-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 sm:h-4 sm:w-4 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="3" x2="15" y1="22" y2="22"></line>
                            <line x1="4" x2="14" y1="9" y2="9"></line>
                            <path d="M14 22V4a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v18"></path>
                            <path d="M14 13h2a2 2 0 0 1 2 2v2a2 2 0 0 0 2 2a2 2 0 0 0 2-2V9.83a2 2 0 0 0-.59-1.42L18 5"></path>
                        </svg>
                        <span class="truncate">
                            @if($car->fuel_type)
                                {{ $car->fuel_type }}
                            @else
                                <span class="text-gray-400 dark:text-gray-500">No data</span>
                            @endif
                        </span>
                    </span>
                    
                    <!-- Row 2: Year Model -->
                    <span class="flex items-center gap-1 sm:gap-1.5">
                        <svg class="h-3 w-3 sm:h-4 sm:w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span class="truncate">
                            @if($car->year)
                                {{ $car->year }}
                            @else
                                <span class="text-gray-400 dark:text-gray-500">No data</span>
                            @endif
                        </span>
                    </span>
                    
                    <!-- Row 2: Transmission -->
                    <span class="flex items-center gap-1 sm:gap-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"></path><circle cx="7" cy="17" r="2"></circle><path d="M9 17h6"></path><circle cx="17" cy="17" r="2"></circle></svg>
                        <span class="truncate">
                            @if($car->transmission)
                                {{ $car->transmission }}
                            @else
                                <span class="text-gray-400 dark:text-gray-500">No data</span>
                            @endif
                        </span>
                    </span>
                </div>
            </div>
            
            <!-- Price & Book Button Row -->
            <div class="mt-3 sm:mt-4 flex items-center justify-between gap-3 pt-3 sm:pt-4 border-t border-gray-100 dark:border-gray-700">
                <!-- Price -->
                <div class="flex flex-col">
                    <span class="text-lg sm:text-2xl font-medium text-gray-900 dark:text-white">
                        ₱{{ number_format($car->price_starts_at ?? $car->price_per_day ?? 0, 2) }}  
                        <span class="text-[10px] sm:text-xs text-gray-500 dark:text-gray-400">/per day</span>
                    </span>
                </div>
                
                {{-- <!-- Book Button - Hidden on mobile, visible on desktop -->
                <span class="hidden sm:inline-flex flex-shrink-0 px-4 sm:px-6 py-2 sm:py-3 bg-[var(--tw-primary)] text-white rounded-xl hover:opacity-90 transition font-medium text-xs sm:text-sm shadow-lg hover:shadow-[var(--tw-primary)]/20 whitespace-nowrap">
                    View
                </span> --}}
                
                <!-- Mobile Arrow Indicator -->
                <span class="flex items-center text-[var(--tw-primary)]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </span> 
            </div>
        </div>
    </div>
</a>