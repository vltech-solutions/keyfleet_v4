@extends('emails.layout')

@section('title', 'Last Day to Renew Your KeyFleet Subscription')

@section('content')
<div style="margin-bottom: 1.5rem;">
    <h2 style="font-size: 1.5rem; font-weight: 600; color: #1f2937;">Hi {{ $company->name }},</h2>
    <p style="color: #b91c1c;">
        Just a heads-up: your <strong>{{ $plan->name }}</strong> subscription will expire <strong>tomorrow</strong>.
    </p>
</div>

<div style="margin-bottom: 1.5rem;">
    <p style="color: #4b5563;">
        If not renewed, access to bookings, reports, and fleet tools will be disabled — potentially disrupting your operations.
    </p>
    <p style="color: #4b5563; margin-top: 0.5rem;">
        It only takes a minute to renew and stay connected with your team and clients.
    </p>
</div>

<div style="text-align: center; margin: 2rem 0;">
    <a href="{{ $renewUrl }}" style="background-color: #dc2626; color: white; padding: 0.75rem 2rem; border-radius: 0.5rem; text-decoration: none;">Renew Now</a>
</div>

<div style="margin-top: 1.5rem; color: #4b5563;">
    <p>Need help or have questions? Just reply to this email — we’re here for you.</p>
</div>
@endsection
