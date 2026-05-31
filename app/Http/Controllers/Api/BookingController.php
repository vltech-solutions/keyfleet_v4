<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Car;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $companyId = $user->companies()->first()?->id;

        if (! $companyId) {
            return response()->json([
                'status' => 'error',
                'message' => 'No company linked to this account.'
            ], 403);
        }

        $status = $request->query('status'); // upcoming, ongoing, finished, cancelled
        $now = now();

        $bookings = Booking::with(['car', 'payments'])
            ->where('company_id', $companyId)
            ->when($status, function ($query) use ($status, $now) {
                return match ($status) {
                    'upcoming' => $query->where('start_datetime', '>', $now)
                                        ->where('status', 'approved'),
                    'ongoing' => $query->where('start_datetime', '<=', $now)
                                    ->where('end_datetime', '>=', $now)
                                    ->where('status', 'approved'),
                    'finished' => $query->where('end_datetime', '<', $now)
                                        ->where('status', 'approved'),
                    'cancelled' => $query->where('status', 'cancelled'),
                    default => $query,
                };
            })
            ->orderBy('start_datetime', 'asc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $bookings,
        ]);
    }

    public function show($id){
        $booking = Booking::with(['car', 'payments'])->find($id);
        return response()->json([
            'status' => 'success',
            'data' => $booking,
        ]);
    }

}
