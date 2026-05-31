<div class="max-w-7xl mx-auto px-6 py-16 sm:py-24 lg:py-32 text-center lg:text-left">
    <style>
        .fi-input {
            color: #353535 !important;
        }
        .fi-fo-field-wrp-label > span {
            color: #353535 !important;
        }
    </style>
    <div class="flex flex-col lg:flex-row items-center gap-16">
        <div class="w-full lg:w-1/2 flex flex-col justify-start">
    <!-- Text Content -->
    <div>
        <!-- Badge -->
        <div class="inline-flex items-center px-4 py-2 mb-6 text-sm font-medium text-blue-800 rounded-full bg-blue-100">
            <span class="w-2 h-2 mr-2 bg-green-500 rounded-full animate-pulse"></span>
            14-day free trial — No credit card required
        </div>

        <!-- Headline -->
        <h1 class="text-4xl sm:text-5xl md:text-6xl font-extrabold leading-tight tracking-tight mb-6 text-gray-900">
            Start Your
            <span class="text-transparent bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text">
                Free Trial
            </span>
            Today
        </h1>

        <!-- Subtext -->
        <p class="max-w-xl text-lg sm:text-xl text-gray-600 mb-10 leading-relaxed">
            Join hundreds of rental businesses already streamlining operations with Keyfleet.
            Explore the full platform with zero risk or setup hassle.
        </p>
    </div>

    <!-- Image Below Text -->
    <div class="mt-4">
        <img 
            src="{{ asset('images/tenant-register.gif') }}" 
            alt="Small dashboard preview"
            class="w-full max-h-[300px] object-contain rounded-xl shadow-md"
            loading="lazy"
        />
    </div>
</div>



        <!-- Right: Form Card -->
        <div class="w-full lg:w-1/2">
            <div class="bg-white border border-gray-200 rounded-2xl shadow-xl p-8 text-left">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Create Your Account</h2>

                <form wire:submit.prevent="submit" class="space-y-6">
                    {{ $this->form }}

                    <button
                        type="submit"
                        class="w-full px-6 py-3 font-semibold text-white bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg shadow-lg transition duration-300 hover:from-blue-700 hover:to-purple-700 hover:scale-[1.02]"
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-50 cursor-not-allowed"
                    >
                        <span wire:loading.remove>Start My Free Trial</span>
                        <span wire:loading>Processing...</span>
                    </button>

                    <p class="text-sm text-gray-500 text-center mt-4">
                        By signing up, you agree to our
                        <a href="/terms-of-service" class="text-blue-600 hover:underline">Terms of Service</a> and
                        <a href="/privacy-policy" class="text-blue-600 hover:underline">Privacy Policy</a>.
                    </p>
                </form>
            </div>
        </div>
    </div>

    <!-- Features (optional, can keep or remove) -->
    <div class="mt-24">
        <h3 class="text-2xl font-bold text-gray-900 mb-12 text-center">What You’ll Get During the Trial</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
            <div class="text-center">
                <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 rounded-full bg-blue-100">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <h4 class="text-lg font-semibold text-gray-900 mb-1">Full Access</h4>
                <p class="text-gray-600">Every premium feature unlocked for 14 days</p>
            </div>

            <div class="text-center">
                <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 rounded-full bg-green-100">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192L5.636 18.364M12 2.25a9.75 9.75 0 11-9.75 9.75 9.75 9.75 0 019.75-9.75z" />
                    </svg>
                </div>
                <h4 class="text-lg font-semibold text-gray-900 mb-1">24/7 Support</h4>
                <p class="text-gray-600">We’re here to help, anytime during your trial</p>
            </div>

            <div class="text-center">
                <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 rounded-full bg-purple-100">
                    <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h4 class="text-lg font-semibold text-gray-900 mb-1">No Commitment</h4>
                <p class="text-gray-600">Cancel anytime — no credit card needed</p>
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
