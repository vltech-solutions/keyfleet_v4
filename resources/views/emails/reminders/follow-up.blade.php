@extends('emails.layout')

@section('title', 'We’ve Missed You at KeyFleet')

@section('content')
<div style="margin-bottom: 1.5rem;">
    <h2 style="font-size: 1.5rem; font-weight: 600; color: #1f2937;">Hi {{ $company->name }},</h2>
    <p style="color: #4b5563;">It’s been 3 days since your KeyFleet subscription expired, and we noticed you haven’t renewed yet.</p>
</div>

<div style="margin-bottom: 1.5rem;">
    <p style="color: #4b5563;">
        We'd really love to have you back. You can still renew your <strong>{{ $plan->name }}</strong> plan and regain full access to all your tools, bookings, and reports — no data loss, no extra fees.
    </p>
    <p style="color: #4b5563; margin-top: 0.5rem;">
        Don’t let your operations stay paused. Reactivate in just a few clicks!
    </p>
</div>

<div style="text-align: center; margin: 2rem 0;">
    <a href="{{ $renewUrl }}" style="background-color: #10b981; color: white; padding: 0.75rem 2rem; border-radius: 0.5rem; text-decoration: none;">Renew & Reactivate</a>
</div>

<div style="margin-top: 1.5rem; color: #4b5563;">
    <p>Need help or have questions? Just reply to this email — we're happy to assist.</p>
</div>
@endsection
