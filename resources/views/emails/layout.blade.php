
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>
</head>
<body style="background-color: #f3f4f6; padding: 2rem 0; margin: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;">
    <div style="max-width: 1200px; margin: 0 auto; padding: 0 1rem;">
        <div style="text-align: center; margin-bottom: 2rem;">
            <h1 style="font-size: 1.875rem; font-weight: bold; color: #1f2937; margin-bottom: 0.5rem; margin-top: 0;">@yield('title')</h1>
            {{-- <p style="color: #4b5563; margin: 0;">A clean, responsive email template built with inline styles</p> --}}
        </div>
        
        <div style="max-width: 42rem; margin: 0 auto; background-color: white; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;">
            <!-- Email Container -->
            <div style="background-color: white; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05); border-radius: 0.5rem; overflow: hidden;">
                
                <!-- Header -->
                <div style="background: linear-gradient(to right, #2563eb, #1d4ed8); padding: 1.5rem 2rem;">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div style="color: white;">
                            <h1 style="font-size: 1.5rem; font-weight: bold; margin: 0 0 0.25rem 0;">{{ config('app.name') }}</h1>
                            <p style="color: #dbeafe; font-size: 0.875rem; margin: 0;">Smarter Car Rental Management System</p>
                        </div>
                       
                    </div>
                </div>

                <!-- Main Content -->
                <div style="padding: 2rem;">
                    @yield('content')
                </div>

                <!-- Footer -->
                <div style="background-color: #f9fafb; padding: 1.5rem 2rem; border-top: 1px solid #e5e7eb;">
                    <div style=" margin-bottom: 1rem;">
                        <p style="color: #4b5563; margin-bottom: 0.5rem; margin-top: 0;">Best regards,</p>
                        <p style="font-weight: 600; color: #1f2937; margin: 0;">— The KeyFleet Team</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
