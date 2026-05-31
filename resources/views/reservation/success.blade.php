<x-layouts.booking  :company="$company">
    <div class="flex items-center justify-center min-h-screen bg-gray-100">
        <div class="w-full max-w-md p-8 text-center bg-white shadow-lg rounded-2xl">
            <!-- Big green check icon -->
            <svg xmlns="http://www.w3.org/2000/svg" class="w-20 h-20 mx-auto text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>

            <h1 class="mt-4 text-2xl font-bold text-gray-800">Reservation Submitted!</h1>

            <p class="mt-3 text-lg font-semibold">Your reservation number:</p>
            <p class="mt-1 text-3xl font-extrabold text-gray-900">{{ $reservationNumber }}</p>

            <p class="mt-3 text-sm text-gray-600">
                Please keep this number for tracking and future reference.
            </p>

            <a href="{{ route('booking.wizard',['tenant' => $tenant]) }}" class="inline-block px-6 py-2 mt-6 text-white transition bg-blue-600 rounded-lg hover:bg-blue-700">
                Back to Booking Form
            </a>
        </div>
    </div>
</x-layouts.booking>