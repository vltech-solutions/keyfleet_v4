@php
    $features = $this->selectedPremiumFeatures ?? [];
    $planPriceId = $this->selectedPlanPriceId ?? null;
@endphp

{{ json_encode($features) }}
@if(!empty($features))
    <div class="space-y-1">
        <h3 class="font-medium text-gray-700">Selected Add-ons:</h3>
        <ul class="list-disc ml-5">
            @foreach($features as $featureId)
                @php
                    $feature = \App\Models\PremiumFeature::find($featureId);
                    $price = 0;

                    if ($feature && $planPriceId) {
                        $planPrice = \App\Models\PlanPrice::find($planPriceId);
                        $billingCycle = strtolower($planPrice->billing_cycle); // monthly, 3months, etc.

                        // $feature->pricing is JSON -> already cast to array
                        $pricingItem = collect($feature->pricing)
                            ->first(fn($p) => strtolower($p['duration']) === strtolower($billingCycle));

                        if ($pricingItem) {
                            $price = (float) $pricingItem['price']; // make sure it's numeric
                        }
                    }
                @endphp

                @if($feature)
                    <li>{{ $feature->feature }} — ₱{{ number_format($price, 2) }}</li>
                @endif
            @endforeach
        </ul>
    </div>
@endif