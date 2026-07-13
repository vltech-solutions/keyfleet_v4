@auth
<div x-data="{}" class="flex items-center mr-2">
    <button
        @click="$dispatch('open-chatbot')"
        class="flex items-center justify-center w-9 h-9 text-gray-500 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-all duration-200 group relative"
        title="Ask Otto - AI Assistant"
    >
        <!-- Otto SVG Icon - Smaller for button -->
        <svg class="w-5 h-5 group-hover:scale-110 transition-transform duration-200" 
             viewBox="0 0 128 128" 
             fill="none" 
             xmlns="http://www.w3.org/2000/svg">
            <defs>
                <linearGradient id="ottoHaloBtn" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#38bdf8" />
                    <stop offset="100%" stop-color="#0369a1" />
                </linearGradient>
                <linearGradient id="ottoFaceBtn" x1="0%" y1="0%" x2="0%" y2="100%">
                    <stop offset="0%" stop-color="#0f172a" />
                    <stop offset="100%" stop-color="#1e293b" />
                </linearGradient>
                <filter id="ottoGlowBtn" x="-20%" y="-20%" width="140%" height="140%">
                    <feGaussianBlur stdDeviation="3" result="blur" />
                    <feComposite in="SourceGraphic" in2="blur" operator="over" />
                </filter>
            </defs>

            <!-- Outer Steering Wheel / Halo Ring -->
            <circle cx="64" cy="64" r="54" stroke="url(#ottoHaloBtn)" stroke-width="4" stroke-dasharray="28 6 12 6" />
            <circle cx="64" cy="64" r="46" stroke="#38bdf8" stroke-width="1.5" stroke-opacity="0.3" />

            <!-- Inner Hub / Robot Head Container -->
            <rect x="34" y="38" width="60" height="52" rx="26" fill="url(#ottoFaceBtn)" stroke="url(#ottoHaloBtn)" stroke-width="3" />

            <!-- Robot Ears / Side Nodes -->
            <rect x="28" y="52" width="6" height="24" rx="3" fill="url(#ottoHaloBtn)" />
            <rect x="94" y="52" width="6" height="24" rx="3" fill="url(#ottoHaloBtn)" />

            <!-- Digital Visor Screen -->
            <rect x="44" y="48" width="40" height="24" rx="12" fill="#020617" stroke="#38bdf8" stroke-width="1" />

            <!-- Glowing Eyes -->
            <circle cx="54" cy="60" r="3.5" fill="#38bdf8" filter="url(#ottoGlowBtn)" />
            <circle cx="74" cy="60" r="3.5" fill="#38bdf8" filter="url(#ottoGlowBtn)" />

            <!-- Friendly Smile Line -->
            <path d="M58 68 Q64 72 70 68" stroke="#38bdf8" stroke-width="2" stroke-linecap="round" fill="none" />

            <!-- Lower Chassis Connection -->
            <path d="M64 90 L64 114" stroke="url(#ottoHaloBtn)" stroke-width="4" stroke-linecap="round" />
            <path d="M42 84 L24 98" stroke="url(#ottoHaloBtn)" stroke-width="3" stroke-linecap="round" />
            <path d="M86 84 L104 98" stroke="url(#ottoHaloBtn)" stroke-width="3" stroke-linecap="round" />
        </svg>

        <!-- Online Status Dot -->
        <span class="absolute -top-0.5 -right-0.5 flex h-2.5 w-2.5">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-green-500"></span>
        </span>

        <!-- Tooltip on hover -->
        <span class="absolute -bottom-8 left-1/2 -translate-x-1/2 whitespace-nowrap text-[10px] font-medium text-white dark:text-gray-200 bg-gray-800 dark:bg-gray-700 px-2 py-0.5 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none">
            Ask OTTO
        </span>
    </button>
</div>
@endauth