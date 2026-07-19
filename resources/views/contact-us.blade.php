<x-layouts.guest>
    <section class="max-w-4xl mx-auto px-6 py-20 bg-white dark:bg-gray-900 transition-colors duration-300">
        <!-- Header -->
        <div class="text-center mb-12">
            <span class="inline-block px-4 py-1.5 text-xs font-bold tracking-wider text-indigo-700 dark:text-indigo-300 uppercase bg-indigo-100 dark:bg-indigo-900/30 rounded-full">Get in Touch</span>
            <h1 class="mt-4 text-4xl sm:text-5xl font-extrabold text-gray-900 dark:text-white">
                Contact <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-blue-600 dark:from-indigo-400 dark:to-blue-400">Us</span>
            </h1>
            <p class="mt-4 text-lg text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
                Have questions, feedback, or need support? We're here to help. Reach out to us anytime.
            </p>
        </div>

        <!-- Contact Options Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-12">
            <!-- Email Card -->
            <div class="p-6 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 hover:shadow-md transition-all duration-300 group">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-12 h-12 rounded-xl bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center group-hover:bg-indigo-200 dark:group-hover:bg-indigo-800/50 transition-colors">
                        <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900 dark:text-white">Email Support</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">We'll respond within 24 hours</p>
                    </div>
                </div>
                <a href="mailto:support@keyfleet.com" 
                   class="inline-flex items-center gap-2 text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 font-medium transition-colors">
                    support@keyfleet.com
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                </a>
            </div>

            <!-- Phone Card -->
            <div class="p-6 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 hover:shadow-md transition-all duration-300 group">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center group-hover:bg-blue-200 dark:group-hover:bg-blue-800/50 transition-colors">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900 dark:text-white">Phone Support</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Mon-Fri, 9AM - 6PM</p>
                    </div>
                </div>
                <a href="tel:+639195438297" 
                   class="inline-flex items-center gap-2 text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-medium transition-colors">
                    +63 (919) 543-8297
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                </a>
            </div>
        </div>

        <!-- FAQ Section -->
        <div class="mt-12 p-6 bg-gray-50 dark:bg-gray-800/50 rounded-2xl border border-gray-100 dark:border-gray-700">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 text-center">Frequently Asked Questions</h3>
            <div class="space-y-4">
                <div class="p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700">
                    <p class="font-medium text-gray-900 dark:text-white">How do I start a free trial?</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Simply click the "Start Free Trial" button on our homepage and follow the registration steps. No credit card required.</p>
                </div>
                <div class="p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700">
                    <p class="font-medium text-gray-900 dark:text-white">What payment methods do you accept?</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">We accept all major credit cards and bank transfers through our secure payment gateway, Paymongo.</p>
                </div>
                <div class="p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700">
                    <p class="font-medium text-gray-900 dark:text-white">Can I cancel my subscription anytime?</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Yes, you can cancel your subscription at any time through your account settings. No questions asked.</p>
                </div>
            </div>
        </div>

        <!-- CTA Section -->
        <div class="mt-12 text-center">
            <div class="bg-gradient-to-r from-indigo-600 to-blue-600 dark:from-indigo-700 dark:to-blue-700 rounded-2xl p-8 md:p-12 text-white shadow-xl">
                <h3 class="text-2xl font-bold mb-2">Ready to Get Started?</h3>
                <p class="text-indigo-100 dark:text-indigo-200 mb-6">Join hundreds of rental businesses using Keyfleet today.</p>
                <a href="{{ route('tenant.register') }}" 
                   class="inline-block px-8 py-3 bg-white dark:bg-gray-100 text-indigo-600 dark:text-indigo-700 font-semibold rounded-full hover:bg-gray-50 dark:hover:bg-white hover:shadow-lg transition-all duration-300 hover:-translate-y-0.5 active:scale-95">
                    Start Free Trial
                </a>
            </div>
        </div>
    </section>
</x-layouts.guest>