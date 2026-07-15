<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerLoginController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'repeat_token' => 'required|string'
        ]);

        $customer = Customer::where('repeat_token', $request->repeat_token)
            ->where('company_id', session('tenant_id'))
            ->first();

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid QR code. Please try again.'
            ], 401);
        }

        // Store customer in session
        session(['customer_token' => $customer->repeat_token]);

        return response()->json([
            'success' => true,
            'message' => 'Login successful!',
            'redirect_url' => route('customer.bookings', ['repeat_token' => $customer->repeat_token])
        ]);
    }

    public function show($repeat_token)
    {
        $customer = Customer::where('repeat_token', $repeat_token)
            ->where('company_id', session('tenant_id'))
            ->firstOrFail();

        return view('customer.bookings', compact('customer'));
    }
}