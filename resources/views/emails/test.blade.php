@extends('emails.layout')

@section('title', 'Payment Issue Notification')

@section('content')
    <!-- Greeting -->
    <div style="margin-bottom: 1.5rem;">
        <h2 style="font-size: 1.5rem; font-weight: 600; color: #1f2937; margin-bottom: 0.5rem; margin-top: 0;">Hello John,</h2>
        <p style="color: #4b5563; line-height: 1.625; margin: 0;">
            We hope this email finds you well. We're excited to share some important updates with you.
        </p>
    </div>

    <!-- Main Message -->
    <div style="margin-bottom: 2rem;">
        <h3 style="font-size: 1.25rem; font-weight: 600; color: #1f2937; margin-bottom: 1rem; margin-top: 0;">Important Update</h3>
        <p style="color: #4b5563; line-height: 1.625; margin-bottom: 1rem;">
            Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris.
        </p>
        <p style="color: #4b5563; line-height: 1.625; margin-bottom: 1.5rem;">
            Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
        </p>

        <!-- Highlight Box -->
        <div style="background-color: #eff6ff; border-left: 4px solid #3b82f6; padding: 1rem; margin-bottom: 1.5rem;">
            <div style="display: flex; align-items: flex-start;">
                <div style="flex-shrink: 0;">
                    <svg style="width: 1.25rem; height: 1.25rem; color: #3b82f6; margin-top: 0.125rem;" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div style="margin-left: 0.75rem;">
                    <p style="font-size: 0.875rem; color: #1d4ed8; font-weight: 500; margin: 0;">
                        <strong>Important:</strong> Please review the attached documents by Friday, December 15th.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Call to Action -->
    <div style="text-align: center; margin-bottom: 2rem;">
        <a href="#" style="display: inline-block; background-color: #2563eb; color: white; font-weight: 600; padding: 0.75rem 2rem; border-radius: 0.5rem; text-decoration: none; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05); transition: all 0.2s;">
            Take Action Now
        </a>
        <p style="font-size: 0.875rem; color: #6b7280; margin-top: 0.75rem; margin-bottom: 0;">
            Or visit our website at <a href="#" style="color: #2563eb; text-decoration: none;">www.yourcompany.com</a>
        </p>
    </div>

    <!-- Additional Info -->
    <div style="border-top: 1px solid #e5e7eb; padding-top: 1.5rem;">
        <h4 style="font-size: 1.125rem; font-weight: 600; color: #1f2937; margin-bottom: 0.75rem; margin-top: 0;">What's Next?</h4>
        <ul style="margin: 0; padding: 0; list-style: none;">
            <li style="display: flex; align-items: flex-start; margin-bottom: 0.5rem;">
                <span style="flex-shrink: 0; width: 0.5rem; height: 0.5rem; background-color: #3b82f6; border-radius: 50%; margin-top: 0.5rem; margin-right: 0.75rem;"></span>
                <span style="color: #4b5563;">Review the attached documentation</span>
            </li>
            <li style="display: flex; align-items: flex-start; margin-bottom: 0.5rem;">
                <span style="flex-shrink: 0; width: 0.5rem; height: 0.5rem; background-color: #3b82f6; border-radius: 50%; margin-top: 0.5rem; margin-right: 0.75rem;"></span>
                <span style="color: #4b5563;">Schedule a follow-up meeting if needed</span>
            </li>
            <li style="display: flex; align-items: flex-start;">
                <span style="flex-shrink: 0; width: 0.5rem; height: 0.5rem; background-color: #3b82f6; border-radius: 50%; margin-top: 0.5rem; margin-right: 0.75rem;"></span>
                <span style="color: #4b5563;">Contact us with any questions</span>
            </li>
        </ul>
    </div>
@endsection