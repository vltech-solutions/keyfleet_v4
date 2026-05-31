<footer class="py-12 mt-24 bg-white border-t border-gray-200">
    <div class="px-6 mx-auto max-w-7xl">
        <div class="flex flex-col items-center justify-between gap-6 text-sm text-gray-500 md:flex-row">

            <!-- Left: Company Info -->
            <div class="text-center md:text-left">
                <p class="font-semibold text-gray-700">&copy; {{ date('Y') }} {{ config('app.name', 'VL Tech') }}</p>
                <p class="mt-1">All rights reserved. Designed with care for modern fleet operations.</p>
            </div>

            <!-- Middle: Navigation Links -->
            <div class="flex flex-wrap justify-center gap-4">
                <a href="/terms-of-service" class="transition hover:text-blue-600">Terms of Service</a>
                <a href="/privacy-policy" class="transition hover:text-blue-600">Privacy Policy</a>
                <a href="/contact-us" class="transition hover:text-blue-600">Contact Us</a>
            </div>

            <!-- Right: Social Links -->
            <div class="flex justify-center gap-4">
                <a href="https://www.facebook.com/profile.php?id=61577048618076" target="_blank" class="text-gray-400 transition hover:text-blue-600" aria-label="Twitter">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M22.675 0h-21.35C.6 0 0 .6 0 1.326v21.348C0 23.4.6 24 1.325 24H12.82v-9.294H9.692v-3.622h3.128V8.413c0-3.1 1.893-4.788 4.659-4.788 1.325 0 2.464.098 2.795.143v3.24l-1.918.001c-1.504 0-1.796.715-1.796 1.763v2.31h3.587l-.467 3.622h-3.12V24h6.116C23.4 24 24 23.4 24 22.674V1.326C24 .6 23.4 0 22.675 0z"/>
                    </svg>

                </a>
                <a href="https://www.tiktok.com/@keyfleethub" class="text-gray-400 transition hover:text-blue-600"  target="_blank" aria-label="TikTok">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 48 48" aria-hidden="true">
                        <path d="M41.5 14.8c-3.6 0-6.9-1.3-9.5-3.5V31c0 6.3-5.1 11.4-11.4 11.4S9.2 37.3 9.2 31s5.1-11.4 11.4-11.4c.9 0 1.8.1 2.7.4v5.5c-.9-.6-1.9-.9-2.7-.9-3.1 0-5.6 2.5-5.6 5.6S17.5 36.8 20.6 36.8c3.1 0 5.6-2.5 5.6-5.6V6.5h6.2c.5 4.5 4.3 8 8.8 8.3v6z"/>
                    </svg>

                </a>
                <a href="#" class="text-gray-400 transition hover:text-blue-600" target="_blank" aria-label="LinkedIn">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M4.98 3.5C3.34 3.5 2 4.83 2 6.47s1.34 2.97 2.98 2.97 2.98-1.33 2.98-2.97S6.62 3.5 4.98 3.5zM2.4 21h5.2V9.5H2.4V21zM9.6 21h5.2v-5.8c0-1.9 2.4-2 2.4 0V21H22V13c0-5.1-5.7-4.9-7.4-2.4V9.5H9.6V21z" />
                    </svg>
                </a>
            </div>

        </div>
    </div>
</footer>
