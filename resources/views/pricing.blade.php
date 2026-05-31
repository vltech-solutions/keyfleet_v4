<x-layouts.guest>
  <section class="px-4 py-20 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto mb-16 text-center">
      <h2 class="text-5xl font-extrabold text-gray-900">Simple, Transparent Pricing</h2>
      <p class="mt-4 text-lg text-gray-600">Choose a plan that fits your fleet. No hidden fees, no surprises.</p>
    </div>

    <div class="grid grid-cols-1 gap-8 mx-auto sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 max-w-7xl">
      @foreach ($plans->take(4) as $plan)
        @php
          $monthlyPrice = $plan->prices->firstWhere('billing_cycle', 'monthly');
          $isPopular = $plan->car_limit === 7;
        @endphp

        <div class="relative p-6 transition bg-white border shadow-md rounded-2xl hover:shadow-xl">
          @if ($isPopular)
            <span class="absolute top-0 right-0 px-3 py-1 text-xs font-semibold text-white bg-indigo-600 rounded-br-2xl rounded-tl-xl">
              Most Popular
            </span>
          @endif

          <h3 class="mb-1 text-2xl font-bold text-gray-800">{{ $plan->name }}</h3>
          <p class="mb-4 text-sm text-gray-500">Manage up to {{ $plan->car_limit }} vehicles</p>

          <div class="text-4xl font-extrabold text-indigo-600">
            @if ($monthlyPrice)
              &#8369;{{ number_format($monthlyPrice->price, 0) }}
              <span class="text-base font-normal text-gray-500">/mo</span>
            @else
              <span class="text-base font-normal text-gray-400">No monthly price</span>
            @endif
          </div>

          <ul class="mt-6 space-y-2 text-sm text-gray-600">
            <li class="flex items-start gap-2"><span>✔</span> Manage {{ $plan->car_limit }} vehicles</li>
            <li class="flex items-start gap-2"><span>✔</span> Advanced reports</li>
            <li class="flex items-start gap-2"><span>✔</span> All premium features</li>
            <li class="flex items-start gap-2"><span>✔</span> {{ $plan->car_limit <= 15 ? 'Support' : 'Priority Support' }}</li>
          </ul>

          <div class="mt-8">
            <a href="#"
              class="w-full inline-block bg-gradient-to-r from-indigo-600 to-blue-600 text-white text-center py-2.5 px-4 rounded-lg font-semibold hover:from-indigo-700 hover:to-blue-700 transition">
              @if($plan->car_limit == 3)
                Get Started
              @elseif($plan->car_limit == 7)
                Choose Plan
              @else
                Go Enterprise        
              @endif
            </a>
          </div>

          @if ($plan->prices->count())
            <p class="mt-4 text-xs text-center text-gray-400">
              @foreach ($plan->prices as $price)
                @if($price->billing_cycle !== 'monthly')
                  &#8369;{{ number_format($price->price, 0) }} / {{ $price->billing_cycle }}{!! !$loop->last ? ' &bull; ' : '' !!}
                @endif
              @endforeach
            </p>
          @endif
        </div>
      @endforeach
    </div>
  </section>
</x-layouts.guest>
