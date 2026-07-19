<div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 transition-colors duration-300">
    <div class="max-w-7xl mx-auto px-6 py-16 sm:py-24 lg:py-32">
        <style>
            .fi-input {
                color: #353535 !important;
            }
            .fi-fo-field-wrp-label > span {
                color: #353535 !important;
            }
            .dark .fi-input {
                color: #e5e7eb !important;
            }
            .dark .fi-fo-field-wrp-label > span {
                color: #e5e7eb !important;
            }
        </style>
        
        <div class="flex flex-col lg:flex-row items-center gap-16">
            <!-- Left Content -->
            <div class="w-full lg:w-1/2 flex flex-col justify-start">
                <!-- Badge -->
                <div class="inline-flex items-center px-4 py-2 mb-6 text-sm font-medium text-blue-800 dark:text-blue-300 rounded-full bg-blue-100/80 dark:bg-blue-900/30 backdrop-blur-sm border border-blue-200/50 dark:border-blue-800/50">
                    <span class="w-2 h-2 mr-2 bg-green-500 rounded-full animate-pulse"></span>
                    14-day free trial — No credit card required
                </div>

                <!-- Headline -->
                <h1 class="text-4xl sm:text-5xl md:text-6xl font-extrabold leading-tight tracking-tight mb-6 text-gray-900 dark:text-white">
                    Start Your
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-purple-600 dark:from-blue-400 dark:to-purple-400">
                        Free Trial
                    </span>
                    Today
                </h1>

                <!-- Subtext -->
                <p class="max-w-xl text-lg sm:text-xl text-gray-600 dark:text-gray-400 mb-8 leading-relaxed">
                    Join hundreds of rental businesses already streamlining operations with Keyfleet.
                    Explore the full platform with zero risk or setup hassle.
                </p>

                <!-- Trust Indicators -->
                <div class="flex flex-wrap items-center gap-6 mb-8">
                    <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                        <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span>100+ companies trust us</span>
                    </div>
                    <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                        <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span>4.9/5 average rating</span>
                    </div>
                    <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                        <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span>98% would recommend</span>
                    </div>
                </div>

                <!-- Image -->
                <div class="relative mt-2">
                    <div class="absolute -top-4 -left-4 z-10 px-3 py-1.5 text-xs font-semibold text-white bg-blue-600 dark:bg-blue-500 rounded-lg shadow-lg">
                        ⚡ Live demo
                    </div>
                    <div class="relative overflow-hidden rounded-2xl shadow-xl border border-gray-200/50 dark:border-gray-700/50">
                        <img 
                            src="{{ asset('images/tenant-register.gif') }}" 
                            alt="Small dashboard preview"
                            class="w-full max-h-[300px] object-contain"
                            loading="lazy"
                        />
                    </div>
                </div>
            </div>

            <!-- Right: Form Card -->
            <div class="w-full lg:w-1/2">
                <div class="bg-white/90 dark:bg-gray-800/90 backdrop-blur-sm border border-gray-200/50 dark:border-gray-700/50 rounded-2xl shadow-2xl p-8 text-left">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-2xl font-semibold text-gray-800 dark:text-white">Create Your Account</h2>
                        <span class="text-xs font-medium text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/30 px-3 py-1 rounded-full">Secure</span>
                    </div>

                    <form wire:submit.prevent="submit" class="space-y-6">
                        {{ $this->form }}

                        <button
                            type="submit"
                            class="w-full px-6 py-3.5 font-semibold text-white bg-gradient-to-r from-blue-600 to-purple-600 dark:from-blue-500 dark:to-purple-500 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-[1.02] active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed"
                            wire:loading.attr="disabled"
                            wire:loading.class="opacity-50 cursor-not-allowed"
                        >
                            <span wire:loading.remove class="flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                                Start My Free Trial
                            </span>
                            <span wire:loading class="flex items-center justify-center gap-2">
                                <svg class="animate-spin w-5 h-5 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Processing...
                            </span>
                        </button>

                        <p class="text-xs text-gray-500 dark:text-gray-400 text-center mt-4">
                            By signing up, you agree to our
                            <a href="/terms-of-service" class="text-blue-600 dark:text-blue-400 hover:underline font-medium">Terms of Service</a>
                            and
                            <a href="/privacy-policy" class="text-blue-600 dark:text-blue-400 hover:underline font-medium">Privacy Policy</a>.
                        </p>

                        <!-- Security Badge -->
                        <div class="flex items-center justify-center gap-2 text-xs text-gray-400 dark:text-gray-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            <span>Your data is secure and encrypted</span>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Features Section -->
        <div class="mt-24">
            <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-12 text-center">
                What You'll Get During the Trial
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center p-6 bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm rounded-2xl border border-gray-200/50 dark:border-gray-700/50 shadow-sm hover:shadow-lg transition-all duration-300 hover:-translate-y-1">
                    <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 rounded-full bg-blue-100 dark:bg-blue-900/30">
                        <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">Full Access</h4>
                    <p class="text-gray-600 dark:text-gray-400">Every premium feature unlocked for 14 days</p>
                </div>

                <div class="text-center p-6 bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm rounded-2xl border border-gray-200/50 dark:border-gray-700/50 shadow-sm hover:shadow-lg transition-all duration-300 hover:-translate-y-1">
                    <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 rounded-full bg-green-100 dark:bg-green-900/30">
                        <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192L5.636 18.364M12 2.25a9.75 9.75 0 11-9.75 9.75 9.75 9.75 0 019.75-9.75z"/>
                        </svg>
                    </div>
                    <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">24/7 Support</h4>
                    <p class="text-gray-600 dark:text-gray-400">We're here to help, anytime during your trial</p>
                </div>

                <div class="text-center p-6 bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm rounded-2xl border border-gray-200/50 dark:border-gray-700/50 shadow-sm hover:shadow-lg transition-all duration-300 hover:-translate-y-1">
                    <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 rounded-full bg-purple-100 dark:bg-purple-900/30">
                        <svg class="w-8 h-8 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">No Commitment</h4>
                    <p class="text-gray-600 dark:text-gray-400">Cancel anytime — no credit card needed</p>
                </div>
            </div>
        </div>

        <x-filament-actions::modals />

        <script>
            window.addEventListener('redirect-after-delay', () => {
                setTimeout(() => {
                    window.location.href = '/app';
                }, 1000);
            });
        </script>
    </div>
</div>