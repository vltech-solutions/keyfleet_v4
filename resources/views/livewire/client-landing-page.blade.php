<div x-data="{ 
        filterOpen: false, 
        scrolled: false,
        darkMode: localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)
    }" 
    x-init="
        window.addEventListener('scroll', () => { scrolled = window.scrollY > 50 });
        $watch('darkMode', val => {
            if (val) {
                document.documentElement.classList.add('dark');
                localStorage.setItem('theme', 'dark');
            } else {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('theme', 'light');
            }
        });
        if (darkMode) document.documentElement.classList.add('dark');
        
        window.addEventListener('scroll-to-cars', () => {
            document.getElementById('cars')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    "
    x-on:filter-applied.window="filterOpen = false"
    style="--primary-color: {{ $primaryColor }};"
    class="antialiased selection:bg-[var(--tw-primary)] selection:text-white bg-white dark:bg-gray-950 transition-colors duration-300"
>
    <style>
        :root { --tw-primary: {{ $primaryColor }}; }
        .hide-scrollbar::-webkit-scrollbar { display: none; }
        .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        [x-cloak] { display: none !important; }
        
        .banner-bg {
            position: relative;
            background-image: url('https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?w=1600&q=80');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }
        .banner-bg::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(0,0,0,0.85) 0%, rgba(0,0,0,0.5) 50%, rgba(0,0,0,0.3) 100%);
            pointer-events: none;
        }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.15);
        }
        .dark .glass-card {
            background: rgba(0, 0, 0, 0.4);
            border-color: rgba(255, 255, 255, 0.1);
        }
        
        .search-input {
            transition: all 0.3s ease;
        }
        .search-input:focus {
            box-shadow: 0 0 0 3px rgba(var(--tw-primary-rgb), 0.3);
            border-color: var(--tw-primary);
        }
        
        .car-stat-card {
            transition: all 0.3s ease;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.08);
        }
        .car-stat-card:hover {
            background: rgba(255,255,255,0.1);
            transform: translateY(-2px);
        }

        .floating-shape {
            position: absolute;
            border-radius: 50%;
            opacity: 0.05;
            pointer-events: none;
        }
    </style>
    
    <!-- After the navbar component -->
    <x-booking.navbar :company="$company" :companyLogo="$companyLogo" />

    <!-- Login Modal - Outside navbar scope -->
    <div x-data="{ 
        loginModalOpen: false,
        uploading: false,
        repeat_token: '',
        errorMessage: '',
        successMessage: '',
        async handleQR(e) {
            const file = e.target.files[0];
            if (!file) return;
            
            // Validate file size (5MB max)
            if (file.size > 5 * 1024 * 1024) {
                this.errorMessage = 'File size exceeds 5MB limit.';
                e.target.value = '';
                return;
            }
            
            // Validate file type
            const allowedTypes = ['image/png', 'image/jpeg', 'image/jpg', 'image/gif', 'image/webp'];
            if (!allowedTypes.includes(file.type)) {
                this.errorMessage = 'Invalid file type. Please upload PNG, JPG, GIF, or WEBP image.';
                e.target.value = '';
                return;
            }
            
            this.uploading = true;
            this.errorMessage = '';
            this.successMessage = '';
            
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
                        this.repeat_token = code.data;
                        await this.submitToken();
                    } else {
                        this.errorMessage = 'QR code not detected. Please try a clearer image.';
                    }
                    this.uploading = false;
                    e.target.value = '';
                };
            };
            reader.readAsDataURL(file);
        },
        async submitToken() {
            if (!this.repeat_token) {
                this.errorMessage = 'Please provide a valid QR code.';
                return;
            }
            
            try {
                const response = await fetch('{{ route("customer.login") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ repeat_token: this.repeat_token })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.successMessage = 'Login successful! Redirecting...';
                    this.errorMessage = '';
                    setTimeout(() => {
                        window.location.href = data.redirect_url;
                    }, 1000);
                } else {
                    this.errorMessage = data.message || 'Invalid QR code. Please try again.';
                    this.successMessage = '';
                }
            } catch (error) {
                this.errorMessage = 'An error occurred. Please try again.';
                this.successMessage = '';
            }
        }
    }"
    x-init="
        $watch('loginModalOpen', (value) => {
            if (value) {
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = '';
                errorMessage = '';
                successMessage = '';
                repeat_token = '';
            }
        });
        
        // Listen for the open-login-modal event from navbar
        window.addEventListener('open-login-modal', () => {
            loginModalOpen = true;
        });
    "
    x-cloak>
        
        <div x-show="loginModalOpen" 
            class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            @click.away="loginModalOpen = false">
            
            <div class="bg-white dark:bg-gray-900 rounded-3xl max-w-md w-full shadow-2xl overflow-hidden">
                <!-- Header -->
                <div class="px-6 pt-6 pb-4 border-b border-gray-100 dark:border-gray-800">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">Login with QR Code</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Upload your QR code to access your bookings</p>
                        </div>
                        <button @click="loginModalOpen = false" 
                                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <!-- Body -->
                <div class="p-6">
                    <!-- Error Message -->
                    <div x-show="errorMessage" 
                        x-transition
                        class="mb-4 p-3 bg-red-50 dark:bg-red-900/20 rounded-xl border border-red-200 dark:border-red-800">
                        <p class="text-sm text-red-600 dark:text-red-400 flex items-center gap-2">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span x-text="errorMessage"></span>
                        </p>
                    </div>
                    
                    <!-- Success Message -->
                    <div x-show="successMessage" 
                        x-transition
                        class="mb-4 p-3 bg-green-50 dark:bg-green-900/20 rounded-xl border border-green-200 dark:border-green-800">
                        <p class="text-sm text-green-600 dark:text-green-400 flex items-center gap-2">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span x-text="successMessage"></span>
                        </p>
                    </div>
                    
                    <!-- QR Upload Area -->
                    <div class="relative border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-2xl p-8 text-center hover:border-blue-500 dark:hover:border-blue-400 transition-colors">
                        <div class="flex flex-col items-center gap-4">
                            <div class="w-16 h-16 rounded-full bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center">
                                <template x-if="!uploading">
                                    <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </template>
                                <template x-if="uploading">
                                    <svg class="animate-spin w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </template>
                            </div>
                            
                            <div>
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                    <span x-show="!uploading">Upload your QR code image</span>
                                    <span x-show="uploading">Processing...</span>
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">PNG, JPG, GIF or WEBP (max 5MB)</p>
                            </div>
                            
                            <label class="relative inline-flex items-center px-6 py-2.5 bg-[var(--tw-primary)] text-white text-sm font-medium rounded-xl cursor-pointer hover:opacity-90 transition-opacity">
                                <span x-show="!uploading">Choose File</span>
                                <span x-show="uploading">Processing...</span>
                                <input type="file" 
                                    accept="image/png,image/jpeg,image/jpg,image/gif,image/webp" 
                                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" 
                                    @change="handleQR" 
                                    :disabled="uploading">
                            </label>
                            
                            <!-- File size warning -->
                            <p class="text-[10px] text-gray-400 dark:text-gray-500">
                                Maximum file size: 5MB
                            </p>
                        </div>
                    </div>
                    
                    <!-- Manual Token Input -->
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Or enter token manually</label>
                        <div class="flex gap-2">
                            <input type="text" 
                                x-model="repeat_token" 
                                placeholder="Paste your token here"
                                class="flex-1 px-4 py-2 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl focus:border-blue-500 focus:ring-0 transition-all dark:text-white text-sm">
                            <button @click="submitToken" 
                                    class="px-6 py-2 bg-[var(--tw-primary)] text-white rounded-xl hover:opacity-90 transition font-medium text-sm whitespace-nowrap">
                                Login
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
    
    
    <!-- Modern Hero Banner - Premium Design with Client Primary Color -->
    <section class="relative overflow-hidden min-h-[85vh] flex items-center">
        
        <!-- Background with Premium Gradient and Abstract Shapes -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <!-- Primary gradient background -->
            <div class="absolute inset-0" style="background: linear-gradient(135deg, var(--primary-color) 0%, #0f172a 60%, #0f172a 100%);"></div>
            
            <!-- Animated glowing orbs -->
            <div class="absolute top-10 right-10 w-72 h-72 rounded-full blur-3xl animate-pulse" style="background: rgba(var(--primary-rgb), 0.2);"></div>
            <div class="absolute bottom-10 left-10 w-96 h-96 rounded-full blur-3xl animate-pulse delay-700" style="background: rgba(var(--primary-rgb), 0.15);"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] rounded-full blur-3xl" style="background: rgba(var(--primary-rgb), 0.08);"></div>
            
            <!-- Abstract geometric shapes -->
            <div class="absolute top-20 left-20 opacity-10">
                <svg class="w-64 h-64" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <polygon points="50,0 100,50 50,100 0,50" stroke="white" stroke-width="0.5"/>
                </svg>
            </div>
            <div class="absolute bottom-20 right-20 opacity-10">
                <svg class="w-80 h-80" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="10" y="10" width="80" height="80" stroke="white" stroke-width="0.5" transform="rotate(45 50 50)"/>
                </svg>
            </div>
            
            <!-- Subtle grid pattern -->
            <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGRlZnM+PHBhdHRlcm4gaWQ9ImdyaWQiIHdpZHRoPSI2MCIgaGVpZ2h0PSI2MCIgcGF0dGVyblVuaXRzPSJ1c2VyU3BhY2VPblVzZSI+PHBhdGggZD0iTSA2MCAwIEwgMCAwIDAgNjAiIGZpbGw9Im5vbmUiIHN0cm9rZT0icmdiYSgyNTUsMjU1LDI1NSwwLjA2KSIgc3Ryb2tlLXdpZHRoPSIxIi8+PC9wYXR0ZXJuPjwvZGVmcz48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSJ1cmwoI2dyaWQpIi8+PC9zdmc+')]">
            </div>
            
            <!-- Bottom fade gradient -->
            <div class="absolute bottom-0 left-0 right-0 h-32 bg-gradient-to-t from-white dark:from-gray-900 to-transparent"></div>
        </div>

        <div class="relative w-full max-w-[1440px] mx-auto px-6 md:px-12 py-16 md:py-24 z-10">
            
            <div class="max-w-6xl mx-auto">
                <!-- Centered Hero Content -->
                <div class="text-center space-y-8">
                    <!-- Status Badge -->
                    <div class="mt-3 inline-flex items-center gap-3 bg-white/10 backdrop-blur-md px-6 py-3 rounded-full border border-white/20 shadow-xl">
                        <span class="relative flex h-3 w-3">
                            <span class="absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75 animate-ping"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-400"></span>
                        </span>
                        <span class="text-sm font-semibold text-white/90">{{ $totalCars ?? 240 }}+ Premium Vehicles Available</span>
                    </div>

                    <!-- Main Headline -->
                    <h1 class="text-5xl sm:text-6xl lg:text-7xl xl:text-8xl font-black leading-[1.1] tracking-tight">
                        <span style="background: linear-gradient(to right, white, rgba(255,255,255,0.7), white); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                            {{ $headerText ?? 'Drive Beyond' }}
                        </span>
                    </h1>
                    
                    <!-- Subheader -->
                    <p class="text-lg md:text-xl text-white/70 max-w-2xl mx-auto leading-relaxed font-light">
                        {{ $subheader ?? 'Unlock a fleet of premium cars. Effortless booking, zero hassle, and 24/7 support to keep you moving.' }}
                    </p>
                </div>

                <!-- Premium Search Card -->
                <div class="max-w-5xl mx-auto mt-12">
                    <div class="bg-white/10 backdrop-blur-2xl rounded-3xl p-6 md:p-8 shadow-2xl border border-white/20">
                        
                        <form wire:submit.prevent="searchCars" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <!-- Vehicle Type -->
                            <div class="md:col-span-1">
                                <label class="block text-white/80 text-xs font-semibold uppercase tracking-wider mb-2">
                                    Vehicle Type
                                </label>
                                <div class="relative">
                                    <select wire:model="selectedVehicleType" 
                                        class="w-full bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl px-5 py-4 text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-white/30 focus:border-transparent appearance-none transition-all">
                                        <option value="All" class="text-gray-900">All Types</option>
                                        @foreach($types as $type)
                                            <option value="{{ $type }}" class="text-gray-900">{{ $type }}</option>
                                        @endforeach
                                    </select>
                                    <svg class="absolute right-5 top-1/2 -translate-y-1/2 w-5 h-5 text-white/40 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </div>
                            </div>

                            <!-- Start Date -->
                            <div class="md:col-span-1">
                                <label class="block text-white/80 text-xs font-semibold uppercase tracking-wider mb-2">
                                    Pick Up
                                </label>
                                <div class="relative">
                                    <input type="date" 
                                        wire:model="startDate" 
                                        min="{{ Carbon\Carbon::now()->format('Y-m-d') }}"
                                        class="w-full bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl px-5 py-4 pr-12 text-white focus:outline-none focus:ring-2 focus:ring-white/30 focus:border-transparent transition-all appearance-none [&::-webkit-calendar-picker-indicator]:opacity-0 [&::-webkit-calendar-picker-indicator]:absolute [&::-webkit-calendar-picker-indicator]:right-3 [&::-webkit-calendar-picker-indicator]:w-6 [&::-webkit-calendar-picker-indicator]:h-6 [&::-webkit-calendar-picker-indicator]:cursor-pointer">
                                    <svg class="absolute right-4 top-1/2 -translate-y-1/2 w-5 h-5 text-white/40 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                            </div>

                            <!-- End Date -->
                            <div class="md:col-span-1">
                                <label class="block text-white/80 text-xs font-semibold uppercase tracking-wider mb-2">
                                    Return
                                </label>
                                <div class="relative">
                                    <input type="date" 
                                        wire:model="endDate" 
                                        min="{{ Carbon\Carbon::now()->addDay()->format('Y-m-d') }}"
                                        class="w-full bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl px-5 py-4 pr-12 text-white focus:outline-none focus:ring-2 focus:ring-white/30 focus:border-transparent transition-all appearance-none [&::-webkit-calendar-picker-indicator]:opacity-0 [&::-webkit-calendar-picker-indicator]:absolute [&::-webkit-calendar-picker-indicator]:right-3 [&::-webkit-calendar-picker-indicator]:w-6 [&::-webkit-calendar-picker-indicator]:h-6 [&::-webkit-calendar-picker-indicator]:cursor-pointer">
                                    <svg class="absolute right-4 top-1/2 -translate-y-1/2 w-5 h-5 text-white/40 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                            </div>

                            <!-- Search Button -->
                            <div class="md:col-span-1">
                                <label class="block text-white/80 text-xs font-semibold uppercase tracking-wider mb-2">
                                    &nbsp;
                                </label>
                                <button type="submit" 
                                    class="w-full text-white font-bold py-4 px-8 rounded-2xl transition-all duration-300 shadow-lg active:scale-[0.98] flex items-center justify-center gap-3 text-base whitespace-nowrap"
                                    style="background: var(--primary-color); box-shadow: 0 10px 30px rgba(var(--primary-rgb), 0.3); hover:box-shadow: 0 15px 40px rgba(var(--primary-rgb), 0.5);"
                                    onmouseover="this.style.boxShadow='0 15px 40px rgba(var(--primary-rgb), 0.5)'"
                                    onmouseout="this.style.boxShadow='0 10px 30px rgba(var(--primary-rgb), 0.3)'">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                    </svg>
                                    Find Cars
                                </button>
                            </div>
                        </form>

                        <!-- Quick Filters -->
                        <div class="mt-6 flex flex-wrap items-center justify-center gap-2 pt-4 border-t border-white/10">
                            <span class="text-white/40 text-xs font-medium uppercase tracking-wider mr-2">Popular:</span>
                            @php
                                $popularTypes = ['SUV', 'Sedan', 'Luxury', 'Electric'];
                            @endphp
                            @foreach($popularTypes as $quickType)
                                @if($types->contains($quickType))
                                    <button type="button" 
                                        wire:click="selectedVehicleType = '{{ $quickType }}'"
                                        class="text-xs px-4 py-2 rounded-full bg-white/10 backdrop-blur-sm text-white/80 hover:text-white transition-all border border-white/10 font-medium"
                                        style="hover:background: var(--primary-color); hover:border-color: var(--primary-color);"
                                        onmouseover="this.style.background='var(--primary-color)'; this.style.borderColor='var(--primary-color)';"
                                        onmouseout="this.style.background=''; this.style.borderColor='';">
                                        {{ $quickType }}
                                    </button>
                                @endif
                            @endforeach
                            <button type="button" 
                                wire:click="selectedVehicleType = 'All'"
                                class="text-xs px-4 py-2 rounded-full text-white transition-all border font-medium"
                                style="background: rgba(var(--primary-rgb), 0.3); border-color: rgba(var(--primary-rgb), 0.4); hover:background: var(--primary-color);"
                                onmouseover="this.style.background='var(--primary-color)'"
                                onmouseout="this.style.background='rgba(var(--primary-rgb), 0.3)'">
                                All Vehicles
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Trust Indicators -->
                <div class="flex flex-wrap items-center justify-center gap-8 mt-12">
                    <div class="flex items-center gap-2 text-white/60">
                        <svg class="w-5 h-5 text-emerald-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-sm font-medium">100% Secure Booking</span>
                    </div>
                    <div class="flex items-center gap-2 text-white/60">
                        <svg class="w-5 h-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-sm font-medium">24/7 Customer Support</span>
                    </div>
                    <div class="flex items-center gap-2 text-white/60">
                        <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        <span class="text-sm font-medium">4.9/5 Customer Rating</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Cars Section -->
    <section id="cars" class="py-12 md:py-20 bg-gray-50 dark:bg-gray-900 transition-colors duration-300">
        <div class="max-w-[1440px] mx-auto px-4 md:px-10">
            
            <!-- Section Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between mb-10 gap-4">
                <div>
                    <span class="inline-block text-[var(--tw-primary)] font-bold text-xs uppercase tracking-wider mb-3 bg-[var(--tw-primary)]/10 px-4 py-1.5 rounded-full">
                        Premium Fleet
                    </span>
                    <h2 class="text-3xl md:text-4xl font-black text-gray-900 dark:text-white">
                        Available <span class="text-[var(--tw-primary)]">Vehicles</span>
                        <span class="ml-3 text-sm font-medium text-gray-400 bg-white dark:bg-gray-800 px-3 py-1 rounded-full">
                            {{ count($cars) }}
                        </span>
                    </h2>
                </div>
                
                <div class="flex items-center gap-3">
                    <!-- Sort Dropdown -->
                    <select wire:model="sortBy" wire:change="applyFilter"
                        class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[var(--tw-primary)]">
                        <option value="popular">Most Popular</option>
                        <option value="price_low">Price: Low to High</option>
                        <option value="price_high">Price: High to Low</option>
                        <option value="newest">Newest First</option>
                    </select>
                    
                    <button @click="filterOpen = true" 
                        class="group flex items-center gap-2 px-5 py-2.5 bg-gray-900 dark:bg-[var(--tw-primary)] text-white rounded-xl hover:opacity-90 transition-all font-medium text-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 group-hover:rotate-12 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                        <span>Filters</span>
                        @if($selectedBrand !== 'All' || $selectedType !== 'All' || $selectedTransmission !== 'All')
                            <span class="relative flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span>
                            </span>
                        @endif
                    </button>
                </div>
            </div>

            <!-- Active Filters -->
            @if($selectedBrand !== 'All' || $selectedType !== 'All' || $selectedTransmission !== 'All' || $searchTerm)
                <div class="flex flex-wrap items-center gap-2 mb-6 p-3 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                    <span class="text-sm text-gray-500 dark:text-gray-400 mr-2">Active filters:</span>
                    @if($selectedBrand !== 'All')
                        <span class="inline-flex items-center gap-1 px-3 py-1 bg-gray-100 dark:bg-gray-700 rounded-full text-sm">
                            Brand: {{ $selectedBrand }}
                            <button wire:click="selectBrand('All')" class="text-gray-400 hover:text-red-500">×</button>
                        </span>
                    @endif
                    @if($selectedType !== 'All')
                        <span class="inline-flex items-center gap-1 px-3 py-1 bg-gray-100 dark:bg-gray-700 rounded-full text-sm">
                            Type: {{ $selectedType }}
                            <button wire:click="selectType('All')" class="text-gray-400 hover:text-red-500">×</button>
                        </span>
                    @endif
                    @if($selectedTransmission !== 'All')
                        <span class="inline-flex items-center gap-1 px-3 py-1 bg-gray-100 dark:bg-gray-700 rounded-full text-sm">
                            Transmission: {{ $selectedTransmission }}
                            <button wire:click="selectTransmission('All')" class="text-gray-400 hover:text-red-500">×</button>
                        </span>
                    @endif
                    @if($searchTerm)
                        <span class="inline-flex items-center gap-1 px-3 py-1 bg-gray-100 dark:bg-gray-700 rounded-full text-sm">
                            Search: "{{ $searchTerm }}"
                            <button wire:click="searchTerm = ''" class="text-gray-400 hover:text-red-500">×</button>
                        </span>
                    @endif
                    <button wire:click="resetFilters" class="text-sm text-[var(--tw-primary)] hover:underline font-medium">Clear all</button>
                </div>
            @endif

            <!-- Car Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-6">
                @forelse($cars as $car)
                    <div class="group">
                        <div class="">
                            <x-booking.car-tile :car="$car" :company="$company" />
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-20 text-center bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700">
                        <div class="inline-flex p-6 bg-gray-50 dark:bg-gray-900 rounded-full text-gray-300 dark:text-gray-700 mb-6">
                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 9.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h4 class="text-xl font-bold text-gray-900 dark:text-white mb-2">No vehicles found</h4>
                        <p class="text-gray-500 dark:text-gray-400">Try adjusting your filters or search terms.</p>
                        <button wire:click="resetFilters" class="mt-4 px-6 py-2.5 bg-[var(--tw-primary)] text-white rounded-xl hover:opacity-90 transition font-medium">Reset Filters</button>
                    </div>
                @endforelse
            </div>

            {{-- <!-- Load More -->
            @if(count($cars) > 0 && count($cars) < \App\Models\Car::where('company_id', $this->company->id)->where('is_available', true)->count())
                <div class="text-center mt-12">
                    <button wire:click="loadMore" 
                        class="px-8 py-3.5 bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition font-medium hover:border-[var(--tw-primary)]">
                        Load More Cars
                    </button>
                </div>
            @endif --}}
        </div>
    </section>

    <!-- Sliding Filter -->
    <x-booking.sliding-filter 
        :filterOpen="false" 
        :brands="$brands"
        :types="$types"
        :transmissions="$transmissions"
        :selectedBrand="$selectedBrand"
        :selectedType="$selectedType"
        :selectedTransmission="$selectedTransmission"
    />

    <x-booking.footer :company="$company" />
    
    <style>
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-30px) rotate(5deg); }
        }
    </style>
</div>