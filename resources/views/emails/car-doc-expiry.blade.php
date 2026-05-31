<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject ?? 'Notification' }}</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f8fafc; font-family: Arial, sans-serif; color: #111827;">

    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background-color: #f8fafc; padding: 30px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" role="presentation" style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); overflow: hidden;">
                    <tr>
                        <td style="padding: 24px; background: linear-gradient(to right, #0047AB, #0a66c2); 
                                        color: white;  text-align: center;">
                            <h1 style="margin: 0; font-size: 24px;">{{ config('app.name') }}</h1>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 24px;">
                            <h2 style="margin-top: 0;">{{ $title ?? 'Document Expiration Notice' }}</h2>

                            <p style="margin-bottom: 16px;">
                                This is a reminder that the following car documents are nearing their expiration. Please ensure they are renewed before the expiry date to avoid any penalties or disruptions.
                            </p>

                            <table width="100%" cellpadding="8" cellspacing="0" style="border-collapse: collapse; margin-bottom: 24px;">
                                <thead>
                                    <tr style="background-color: #f3f4f6; text-align: left;">
                                        <th style="border-bottom: 1px solid #e5e7eb;">Car</th>
                                        <th style="border-bottom: 1px solid #e5e7eb;">Document</th>
                                        <th style="border-bottom: 1px solid #e5e7eb;">Expiry Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($docs as $doc)
                                        <tr>
                                            <td style="border-bottom: 1px solid #e5e7eb;">{{ $doc['car']['name'] }}</td>
                                            <td style="border-bottom: 1px solid #e5e7eb;">{{ $doc['document_type'] }}</td>
                                            <td style="border-bottom: 1px solid #e5e7eb; color: #b91c1c;">
                                                {{ \Carbon\Carbon::parse($doc['expiration_date'])->format('F d, Y') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                    {{-- <tr>
                                        <td style="border-bottom: 1px solid #e5e7eb;">Toyota Vios</td>
                                        <td style="border-bottom: 1px solid #e5e7eb;">Registration</td>
                                        <td style="border-bottom: 1px solid #e5e7eb; color: #b91c1c;">July 10, 2025</td>
                                    </tr>
                                    <tr>
                                        <td style="border-bottom: 1px solid #e5e7eb;">Honda Civic</td>
                                        <td style="border-bottom: 1px solid #e5e7eb;">Insurance</td>
                                        <td style="border-bottom: 1px solid #e5e7eb; color: #b91c1c;">July 15, 2025</td>
                                    </tr>
                                    <tr>
                                        <td style="border-bottom: 1px solid #e5e7eb;">Ford Ranger</td>
                                        <td style="border-bottom: 1px solid #e5e7eb;">Emission Test</td>
                                        <td style="border-bottom: 1px solid #e5e7eb; color: #b91c1c;">July 20, 2025</td>
                                    </tr> --}}
                                </tbody>
                            </table>

                            <p style="font-size: 14px; color: #6b7280; margin-bottom: 20px;">
                                For more information, please visit your account.
                            </p>
                            <div style="text-align: center;">
                                <a href="{{ $dashboardUrl }}app/login"
                                style="background: linear-gradient(to right, #0047AB, #0a66c2); 
                                        color: white; 
                                        padding: 12px 24px; 
                                        border-radius: 6px; 
                                        text-decoration: none; 
                                        display: inline-block;">
                                    Login to Your Account
                                </a>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 16px; background-color: #f1f5f9; text-align: center; font-size: 12px; color: #6b7280;">
                            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

</body>
</html>
