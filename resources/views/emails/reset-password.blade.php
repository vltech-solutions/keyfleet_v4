@extends('emails.layout')

@section('title', 'Reset Password')

@section('content')
    <!-- Greeting -->
    <div style="margin-bottom: 1.5rem;">
        <h2 style="font-size: 1.5rem; font-weight: 600; color: #1f2937; margin-bottom: 0.5rem; margin-top: 0;">
            Hello {{ $user->name }},
        </h2>
        <p style="color: #4b5563; line-height: 1.625; margin: 0;">
            You are receiving this email because we received a password reset request for your account.
        </p>
    </div>

    <!-- Call to Action -->
    <div style="text-align: center; margin: 2rem 0;">
        <a href="{{ $resetUrl }}" style="
            display: inline-block;
            background-color: #2563eb;
            color: white;
            font-weight: 600;
            padding: 0.75rem 2rem;
            border-radius: 0.5rem;
            text-decoration: none;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1),
                        0 4px 6px -2px rgba(0, 0, 0, 0.05);
            transition: background-color 0.2s ease-in-out;
        ">
            Reset Password
        </a>
    </div>

    <!-- Info Notes -->
    <div style="margin-top: 1.5rem; color: #4b5563; line-height: 1.625;">
        <p style="margin-bottom: 1rem;">
            {{ __('This password reset link will expire in :count minutes.', ['count' => config('auth.passwords.'.config('auth.defaults.passwords').'.expire')]) }}
        </p>
        <p style="margin: 0;">
            {{ __('If you did not request a password reset, no further action is required.') }}
        </p>
    </div>


@endsection