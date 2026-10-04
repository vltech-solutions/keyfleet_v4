<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\User;
use App\Services\CalendarDataService;
use App\Services\IcalendarService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class CalendarExportController extends Controller
{
    public function booking(Request $request, Booking $booking, IcalendarService $ical)
    {
        $user = $request->user();
        abort_unless($user?->is_active && $user->hasPermission('calendar.view'), 403);
        abort_unless((int) $booking->company_id === (int) $user->company_id, 404);

        return $this->response(
            $ical->calendar(collect([$booking]), 'KeyFleet Booking '.$booking->booking_id),
            'booking-'.($booking->booking_id ?: $booking->id).'.ics',
        );
    }

    public function feed(
        User $user,
        string $token,
        CalendarDataService $calendar,
        IcalendarService $ical,
    ) {
        abort_unless(
            $user->is_active
            && $user->company_id
            && $user->hasPermission('calendar.view')
            && $user->calendar_feed_token_hash
            && hash_equals($user->calendar_feed_token_hash, hash('sha256', $token)),
            404,
        );

        $from = Carbon::now(config('app.timezone'))->subYear()->startOfDay();
        $to = Carbon::now(config('app.timezone'))->addYears(2)->endOfDay();
        $bookings = $calendar->bookings($user->company, $from, $to);

        return $this->response(
            $ical->calendar($bookings, $user->company->name.' Bookings'),
            'keyfleet-calendar.ics',
        );
    }

    private function response(string $content, string $filename)
    {
        return response($content, 200, [
            'Content-Type' => 'text/calendar; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Cache-Control' => 'private, no-store, max-age=0',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
