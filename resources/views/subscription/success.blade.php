<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Successful</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'primary': '#0455da',
                        'primary-dark': '#0344b8',
                        'primary-light': '#e6f2ff'
                    }
                }
            }
        }
    </script>
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-50 to-primary-light">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="w-full max-w-md p-8 text-center bg-white shadow-xl rounded-2xl">
            {{-- <!-- Company Logo -->
            <div class="mb-8">
                <div class="flex items-center justify-center w-20 h-20 mx-auto rounded-full shadow-lg bg-gradient-to-br from-primary to-primary-dark">
                    <svg class="w-10 h-10 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                </div>
            </div> --}}

            <!-- Success Icon -->
            <div class="mb-6">
                <div class="flex items-center justify-center w-16 h-16 mx-auto bg-blue-100 rounded-full">
                    <svg class="w-8 h-8 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
            </div>

            <!-- Success Message -->
            <h1 class="mb-4 text-3xl font-bold text-gray-900">
                Subscription Successful!
            </h1>
            
            <p class="mb-2 text-gray-600">
                You are now subscribed to <strong>{{ $planName }}</strong> ({{ $billingCycle }} plan)!
            </p>
            
            {{-- <p class="mb-8 text-sm text-gray-500">
                Your subscription has been activated and you now have access to all keyfleet features.
            </p> --}}

            <!-- Features List -->
            <div class="p-4 mb-8 rounded-lg bg-gray-50">
                <h3 class="mb-3 font-semibold text-gray-900">What's included:</h3>
                <ul class="space-y-2 text-sm text-gray-600">
                    <li class="flex items-center">
                        <svg class="w-4 h-4 mr-2 text-primary" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        Manage {{ $carLimit }} cars
                    </li>
                    <li class="flex items-center">
                        <svg class="w-4 h-4 mr-2 text-primary" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        Advanced reports
                    </li>
                    <li class="flex items-center">
                        <svg class="w-4 h-4 mr-2 text-primary" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        All KEYFLEET Features
                    </li>
                </ul>
            </div>

            <!-- Dashboard Button -->
            <a href="{{ url("app/{$slug}") }}" class="inline-flex items-center justify-center w-full px-6 py-3 font-semibold text-white transition-all duration-200 transform rounded-lg shadow-lg bg-gradient-to-r from-primary to-primary-dark hover:from-primary-dark hover:to-blue-800 hover:scale-105 focus:outline-none focus:ring-4 focus:ring-blue-200">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                </svg>
                Go to Dashboard
            </a>

            <!-- Footer -->
            <div class="pt-6 mt-8 border-t border-gray-200">
                <p class="text-xs text-gray-500">
                    Powered by <a href="/support" class="underline text-primary hover:text-primary-dark">VL TECH IT SOLUTIONS</a>
                </p>
            </div>
        </div>
    </div>

    <!-- Background Decoration -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute w-24 h-24 bg-blue-200 rounded-full -top-4 -right-4 opacity-20"></div>
        <div class="absolute w-32 h-32 bg-blue-300 rounded-full top-1/4 -left-8 opacity-15"></div>
        <div class="absolute w-20 h-20 bg-blue-400 rounded-full bottom-1/4 right-1/4 opacity-10"></div>
    </div>
</body>
</html>
