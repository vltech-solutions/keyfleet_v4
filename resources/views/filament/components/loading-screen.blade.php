<style>
    /* 1. Sidebar & Topbar Cleanup for PWA/Mobile View */
    @media (max-width: 1024px) {
        .fi-sidebar, 
        .fi-topbar nav, 
        .fi-topbar-open-sidebar-btn {
            display: none !important;
        }
    }

    /* 2. Loading Screen Core Styles */
    #loading-screen {
        transition: opacity 0.5s ease-out;
    }

    /* 3. Modern Spinner Animation */
    .modern-spinner {
        width: 48px;
        height: 48px;
        border: 3px solid rgba(229, 231, 235, 0.2); /* Light gray transparent */
        border-top-color: #0284c7; /* Your Primary 600 color */
        border-radius: 50%;
        animation: keyfleet-spin 0.8s linear infinite;
    }

    /* 4. Progress Bar Animation */
    .progress-bar-container {
        width: 100px;
        height: 2px;
        background: rgba(229, 231, 235, 0.1);
        border-radius: 999px;
        overflow: hidden;
        margin-top: 1.5rem;
    }

    .progress-line {
        width: 100%;
        height: 100%;
        background-color: #0284c7;
        transform: translateX(-100%);
        animation: progress-slide 1.5s infinite ease-in-out;
    }

    @keyframes keyfleet-spin {
        to { transform: rotate(360deg); }
    }

    @keyframes progress-slide {
        0% { transform: translateX(-100%); }
        50% { transform: translateX(0%); }
        100% { transform: translateX(100%); }
    }

    .dark #loading-screen {
        background-color: #030712; /* Gray 950 */
    }
</style>

<div id="loading-screen" class="fixed inset-0 z-[100] flex flex-col items-center justify-center bg-white dark:bg-gray-950">
    <div class="relative flex items-center justify-center">
        <div class="absolute w-20 h-20 bg-primary-500/10 blur-3xl rounded-full"></div>
        
        <div class="modern-spinner"></div>
    </div>

    <div class="flex flex-col items-center">
        <p class="mt-6 text-[10px] font-bold tracking-[0.4em] text-gray-400 dark:text-gray-500 uppercase">
            Loading Keyfleet
        </p>
        
        <div class="progress-bar-container" style="display:none;">
            <div class="progress-line"></div>
        </div>
    </div>
</div>

<script>
    /**
     * Keyfleet Loader Controller
     * Handles initial page load and Livewire SPA navigation
     */
    const loader = document.getElementById('loading-screen');

    const hideKeyfleetLoader = () => {
        if (!loader) return;
        loader.style.opacity = '0';
        setTimeout(() => {
            loader.style.display = 'none';
        }, 500);
    };

    const showKeyfleetLoader = () => {
        if (!loader) return;
        loader.style.display = 'flex';
        loader.style.opacity = '1';
    };

    // Initial Load - Fired when everything (images, etc.) is ready
    window.addEventListener('load', () => {
        setTimeout(hideKeyfleetLoader, 600); // 0.6s delay for smooth entrance
    });

    // Livewire v3 Navigation Hooks
    document.addEventListener('livewire:navigating', () => {
        showKeyfleetLoader();
    });

    document.addEventListener('livewire:navigated', () => {
        // Fast hide on navigation complete
        setTimeout(hideKeyfleetLoader, 400);
    });
</script>