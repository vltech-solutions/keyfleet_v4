<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Success</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f8fafc; line-height: 1.6;">
    <!-- Email Container -->
    <table role="presentation" style="width: 100%; border-collapse: collapse; background-color: #f8fafc;">
        <tr>
            <td style="padding: 40px 20px;">
                <!-- Main Content Card -->
                <table role="presentation" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1); overflow: hidden;">
                    <!-- Header with Logo -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #0455da 0%, #0344b8 100%); padding: 40px 30px; text-align: center;">
                            <!-- Company Logo Circle -->
                            {{-- <div style="width: 80px; height: 80px; margin: 0 auto 20px;  border-radius: 50%; display: flex; align-items: center; justify-content: center; ">
                                <div style="width: 40px; height: 40px; background-color: white; border-radius: 50%; position: relative;">
                                    <!-- Checkmark SVG -->
                                    <svg style="width: 24px; height: 24px; position: absolute; top: 8px; left: 8px; color: #0455da;" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                                    </svg>
                                </div>
                            </div> --}}
                            <h1 style="color: white; margin: 0; font-size: 28px; font-weight: 700; text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">Payment Successful!</h1>
                        </td>
                    </tr>
                    
                    <!-- Main Content -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <!-- Greeting -->
                            <h2 style="color: #1f2937; margin: 0 0 20px; font-size: 24px; font-weight: 600;">Hi {{ $subscriptionData['name'] }},</h2>
                            
                            <!-- Thank you message -->
                            <p style="color: #4b5563; margin: 0 0 25px; font-size: 16px;">Thank you for your payment. Your subscription to <strong style="color: #0455da;">{{ $subscriptionData['planName'] }}</strong> has been successfully processed.</p>
                            
                            <!-- Payment Details Card -->
                            <div style="background: linear-gradient(135deg, #f8fafc 0%, #e6f2ff 100%); border: 2px solid #e6f2ff; border-radius: 12px; padding: 25px; margin: 25px 0;">
                                <h3 style="color: #0455da; margin: 0 0 20px; font-size: 18px; font-weight: 600; border-bottom: 2px solid #0455da; padding-bottom: 8px; display: inline-block;">Payment Details</h3>
                                
                                <table role="presentation" style="width: 100%; border-collapse: collapse;">
                                    <tr>
                                        <td style="padding: 8px 0; border-bottom: 1px solid #e5e7eb;">
                                            <strong style="color: #374151; font-weight: 600;">Billing Cycle:</strong>
                                        </td>
                                        <td style="padding: 8px 0; border-bottom: 1px solid #e5e7eb; text-align: right;">
                                            <span style="color: #6b7280; background-color: #f3f4f6; padding: 4px 12px; border-radius: 20px; font-size: 14px; font-weight: 500;">{{ ucfirst($subscriptionData['billingCycle']) }}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 8px 0; border-bottom: 1px solid #e5e7eb;">
                                            <strong style="color: #374151; font-weight: 600;">Total Paid:</strong>
                                        </td>
                                        <td style="padding: 8px 0; border-bottom: 1px solid #e5e7eb; text-align: right;">
                                            <span style="color: #059669; font-weight: 700; font-size: 18px;">₱{{ number_format($subscriptionData['total'], 2) }}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 8px 0;">
                                            <strong style="color: #374151; font-weight: 600;">Date:</strong>
                                        </td>
                                        <td style="padding: 8px 0; text-align: right;">
                                            <span style="color: #6b7280;">{{ now()->format('F d, Y') }}</span>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            
                            <!-- What's Next Section -->
                            <div style="background-color: #f9fafb; border-left: 4px solid #0455da; padding: 20px; margin: 25px 0; border-radius: 0 8px 8px 0;">
                                <h4 style="color: #0455da; margin: 0 0 10px; font-size: 16px; font-weight: 600;">What's Next?</h4>
                                <p style="color: #4b5563; margin: 0; font-size: 14px; line-height: 1.5;">Your subscription is now active and you have access to all keyfleet features. You can manage your subscription anytime from your dashboard.</p>
                            </div>
                            
                            <!-- Dashboard Button -->
                            <div style="text-align: center; margin: 30px 0;">
                                <a href="{{ $dashboardUrl ?? '#' }}" style="display: inline-block; background: linear-gradient(135deg, #0455da 0%, #0344b8 100%); color: white; text-decoration: none; padding: 14px 30px; border-radius: 8px; font-weight: 600; font-size: 16px; box-shadow: 0 4px 12px rgba(4, 85, 218, 0.3); transition: all 0.3s ease;">
                                    Access Dashboard →
                                </a>
                            </div>
                            
                            <!-- Support Message -->
                            <p style="color: #6b7280; margin: 25px 0 0; font-size: 14px; text-align: center; border-top: 1px solid #e5e7eb; padding-top: 20px;">
                                If you have any questions, feel free to <a href="mailto:support@yourcompany.com" style="color: #0455da; text-decoration: none; font-weight: 600;">contact us</a>.
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f9fafb; padding: 30px; text-align: center; border-top: 1px solid #e5e7eb;">
                            <p style="color: #6b7280; margin: 0 0 10px; font-size: 16px; font-weight: 600;">— VL Tech IT Solutions</p>
                            <p style="color: #9ca3af; margin: 0; font-size: 12px;">
                                © {{ date('Y') }} VL Tech IT Solutions. All rights reserved.<br>
                                You received this email because you made a purchase with us.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>