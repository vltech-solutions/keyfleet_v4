<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\Plan;
use App\Models\PlanPrice;
use App\Mail\SubscriptionPaymentSuccess;
use App\Models\AddonSubscription;
use App\Models\VoucherUsage;
use App\Services\ReferralRewardService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Session;
use Filament\Facades\Filament;

use Illuminate\Support\Facades\Http;

class CheckoutController extends Controller
{
    public function create(Request $request)
    {

        $data = Session::pull('pending_subscription'); 

        if (!$data) {
            return redirect()->route('dashboard')->with('error', 'No subscription info found.');
        }

        $paymentDetails = [];
        if (!empty($data['checkout_id'])) {
            $response = Http::withBasicAuth(config('services.paymongo.secret'), '')
                ->get("https://api.paymongo.com/v1/checkout_sessions/{$data['checkout_id']}");

            $paymentDetails = $response->json('data.attributes.payments.0.attributes');
        }


        $plan = Plan::findOrFail($data['plan_id']);
        $planPrice = PlanPrice::with('plan')->findOrFail($data['plan_price_id']);
        $now = now();

        // Calculate ends_at based on billing cycle
        $endsAt = match ($planPrice->billing_cycle) {
            'monthly'   => $now->copy()->addMonth(),
            '3months'   => $now->copy()->addMonths(3),
            '6months'   => $now->copy()->addMonths(6),
            'annually'  => $now->copy()->addYear(),
            '2years'    => $now->copy()->addYears(2),
            default     => $now->copy()->addMonth(),
        };

        $subscription = Subscription::create([
            'company_id'            => $data['company_id'],
            'plan_id'               => $data['plan_id'],
            'plan_price_id'         => $data['plan_price_id'],
            'starts_at'             => $now,
            'ends_at'               => $endsAt,
            'auto_renew'            => true,
            'voucher_id'            => $data['voucher_id'] ?? null,
            'voucher_code'          => $data['voucher_code'] ?? null,
            'refund_amount'         => $data['refund'] ?? 0,
            'discount_amount'       => $data['discount'] ?? 0,
            'processing_fee'        => $data['processing_fee'] ?? 0,
            'subtotal'              => $data['subtotal'] ?? 0,
            'total_due'             => $data['total'], 
            'payment_source'        => !empty($paymentDetails) ? strtoupper($paymentDetails['source']['type']) : null,
            'paymongo_fee'          => !empty($paymentDetails) ? $paymentDetails['fee']/100 : 0,
            'paid_at'               => !empty($paymentDetails) ? date('Y-m-d H:i:s',$paymentDetails['paid_at']) : now(),
            'net_amount'            => !empty($paymentDetails) ? $paymentDetails['net_amount']/100 : $data['total'],
        ]);

        if (!empty($data['selected_addons'])) {
            foreach ($data['selected_addons'] as $addonItem) {
                AddonSubscription::create([
                    'company_id'     => $data['company_id'],
                    'subscription_id' => $subscription->id,
                    'addon_id'       => $addonItem['addon_id'],
                    'addon_price_id' => $addonItem['addon_price_id'],
                    'starts_at'      => $now,
                    'ends_at'        => $endsAt,
                    'status'         => 'active',
                    'total_paid'     => $addonItem['price'],
                ]);
            }
        }

        ReferralRewardService::handleConversion($subscription);

        $slug = Filament::getTenant()?->slug;

        //add voucherUsage
        if ($data['voucher_id']) {
            VoucherUsage::create([
                'voucher_id' => $data['voucher_id'],
                'company_id' => $data['company_id'],
                'used_at'    => now(),
            ]);
        }

        $subscriptionData = [
            'name'         => auth()->user()->name,
            'email'        => auth()->user()->email,
            'slug'         => $slug,
            'dashboardUrl' => url('/app'),
            'planName'     => $data['plan_name'],
            'carLimit'     => $plan->car_limit,
            'total'        => round($data['total']),
            'billingCycle' => ucwords(str_replace('_', ' ', $data['billing_cycle'])),
        ];

        Mail::to(auth()->user()->email)->queue(
            new SubscriptionPaymentSuccess($subscriptionData)
        );

        return view('subscription.success', [
            'slug'         => $slug,
            'planName'     => $data['plan_name'],
            'carLimit'     => $plan->car_limit,
            'billingCycle' => ucwords(str_replace('_', ' ', $data['billing_cycle'])),
        ]);
    }

}
