<section class="px-5 py-5 text-white keyfleet-gradient sm:py-32">
    <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="flex flex-col items-center gap-12 lg:flex-row lg:items-start">
            <!-- Left: Text -->
            <div class="flex-1 text-center lg:text-left">
                <h1 class="mb-6 text-4xl font-extrabold leading-tight tracking-tight sm:text-5xl md:text-6xl drop-shadow-md">
                    Run Your Car Rental Business Smarter
                </h1>
                <p class="max-w-xl mx-auto mb-10 text-lg leading-relaxed sm:text-xl md:text-2xl opacity-90 lg:mx-0 drop-shadow-sm">
                    Keyfleet helps rental companies track vehicles, manage bookings, and get paid — all in one simple dashboard.
                </p>
                <div class="flex flex-col justify-center gap-4 sm:flex-row lg:justify-start">
                    <a href="{{ route('tenant.register') }}"
                       class="inline-block px-8 py-4 font-semibold text-blue-800 transition duration-300 ease-in-out transform bg-white rounded-full shadow-lg hover:bg-blue-100 hover:-translate-y-1">
                        Start Free Trial
                    </a>
                    <a href="/pricing"
                       class="inline-block px-8 py-4 font-semibold text-white transition duration-300 ease-in-out border border-white rounded-full hover:bg-white hover:text-blue-800">
                        View Pricing
                    </a>
                </div>
            </div>

            <!-- Right: Video -->
            <div class="flex justify-center flex-1 lg:justify-end">
                <img 
                    src="{{ Storage::url('images/website/hero-image.png') }}" 
                    alt="Small dashboard preview"
                    {{-- class="w-full max-h-[300px] object-contain rounded-xl shadow-md" --}}
                    class="w-full rounded-2xl"
                    loading="lazy"
                />
            </div>
        </div>
    </div>
</section>
<script>
  window.addEventListener('DOMContentLoaded', () => {
    const video = document.getElementById('heroVideo');
    const source = video.querySelector('source');
    source.src = source.dataset.src;
    video.load();
    video.classList.remove('hidden');
  });
</script>
