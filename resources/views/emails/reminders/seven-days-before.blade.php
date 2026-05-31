@extends('emails.layout')

@section('title', 'Your KeyFleet Subscription Ends Soon')

@section('content')
<div style="margin-bottom: 1.5rem;">
    <h2 style="font-size: 1.5rem; font-weight: 600; color: #1f2937;">Hello {{ $company->name }},</h2>
    <p style="color: #4b5563;">We’re reaching out to remind you that your <strong>KeyFleet {{ $plan->name }}</strong> subscription is ending in <strong>7 days</strong>.</p>
</div>

<div style="margin-bottom: 1.5rem;">
    <p style="color: #4b5563;">
        Don’t let your operations pause unexpectedly. Without renewal, you’ll lose access to essential features like:
    </p>
    <ul style="color: #4b5563; padding-left: 1.25rem; margin-top: 0.75rem;">
        <li>Real-time vehicle tracking</li>
        <li>Smart booking and scheduling</li>
        <li>Admin dashboard and analytics</li>
    </ul>
</div>

<div style="margin-bottom: 1.5rem;">
    <p style="color: #4b5563;">
        Renewing your subscription ensures uninterrupted access and keeps your fleet running smoothly. Our goal is to support your business every step of the way — and it all starts with staying active on KeyFleet.
    </p>
</div>

<div style="text-align: center; margin: 2rem 0;">
    <a href="{{ $renewUrl }}" style="background-color: #2563eb; color: white; padding: 0.75rem 2rem; border-radius: 0.5rem; text-decoration: none;">Renew Now</a>
</div>

<div style="margin-top: 1.5rem; color: #4b5563;">
    <p>If you’ve already renewed, you can ignore this message.</p>
    <p>Need help or have questions? Just reply to this email or reach us anytime.</p>
</div>
@endsection
