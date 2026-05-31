@extends('emails.layout')

@section('title', 'Your KeyFleet Subscription Has Expired')

@section('content')
<div style="margin-bottom: 1.5rem;">
    <h2 style="font-size: 1.5rem; font-weight: 600; color: #1f2937;">Hello {{ $company->name }},</h2>
    <p style="color: #b91c1c;">
        Your <strong>{{ $plan->name }}</strong> subscription expires <strong>today</strong>.
    </p>
</div>

<div style="margin-bottom: 1.5rem;">
    <p style="color: #4b5563;">
        Access to your bookings, reports, and premium features has been disabled. Don’t worry — you can restore everything instantly by renewing now.
    </p>
    <p style="color: #4b5563; margin-top: 0.5rem;">
        Keep your fleet operations uninterrupted. Act before any data is affected.
    </p>
</div>

<div style="text-align: center; margin: 2rem 0;">
    <a href="{{ $renewUrl }}" style="background-color: #f59e0b; color: white; padding: 0.75rem 2rem; border-radius: 0.5rem; text-decoration: none;">Restore Access</a>
</div>

<div style="margin-top: 1.5rem; color: #4b5563;">
    <p>If you need assistance, reply to this email and we’ll be happy to help.</p>
</div>
@endsection
