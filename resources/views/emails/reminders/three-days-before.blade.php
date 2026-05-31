@extends('emails.layout')

@section('title', '3 Days Left: Renew Your KeyFleet Access')

@section('content')
<div style="margin-bottom: 1.5rem;">
    <h2 style="font-size: 1.5rem; font-weight: 600; color: #1f2937;">Hey {{ $company->name }},</h2>
    <p style="color: #4b5563;">Just a quick reminder — your <strong>KeyFleet {{ $plan->name }}</strong> subscription will expire in <strong>3 days</strong>.</p>
</div>

<div style="margin-bottom: 1.5rem;">
    <p style="color: #4b5563;">
        Renew now to avoid any disruption to your operations. Staying subscribed means uninterrupted access to:
    </p>
    <ul style="color: #4b5563; padding-left: 1.25rem; margin-top: 0.75rem;">
        <li>Booking and fleet management tools</li>
        <li>Real-time reports and dashboards</li>
        <li>Full admin access and support</li>
    </ul>
</div>

<div style="margin-bottom: 1.5rem;">
    <p style="color: #4b5563;">
        Your team and clients rely on a smooth and active platform — don’t miss a beat.
    </p>
</div>

<div style="text-align: center; margin: 2rem 0;">
    <a href="{{ $renewUrl }}" style="background-color: #2563eb; color: white; padding: 0.75rem 2rem; border-radius: 0.5rem; text-decoration: none;">Renew Now</a>
</div>

<div style="margin-top: 1.5rem; color: #4b5563;">
    <p>Already renewed? Awesome — no action needed!</p>
    <p>Need assistance? We’re here to help. Just reply to this email.</p>
</div>

@endsection
