<section id="get-started" class="px-6 py-24 text-white shadow-2xl keyfleet-gradient">
    <div class="flex flex-col items-center justify-between gap-12 mx-auto max-w-7xl lg:flex-row">
        
        <!-- Text Content -->
        <div class="text-center lg:text-left lg:w-1/2">
            <h2 class="mb-4 text-4xl font-extrabold leading-tight sm:text-5xl">
                Get Started in Minutes
            </h2>
            <p class="mb-2 text-xl font-medium opacity-90">No training required. Just sign up and go.</p>
            <p class="mb-8 text-lg opacity-80">Start your 14-day free trial. No credit card needed.</p>
            
            <a href="{{ route('tenant.register') }}"
               class="inline-block px-8 py-4 font-semibold text-blue-800 transition bg-white rounded-full shadow-lg hover:bg-gray-100">
                Start Free Trial
            </a>
        </div>

        <!-- Optional Visual -->
        <div class="lg:w-1/2">
            <img 
                src="{{ Storage::url('images/website/cars.png') }}"
                alt="Quick start visual"
                class="w-full max-w-md mx-auto shadow-lg rounded-xl"
            >
        </div>

    </div>
</section>
