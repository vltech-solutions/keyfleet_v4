<?php

use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CustomerLoginController;
use App\Http\Controllers\InspectionReportController;
use App\Livewire\Booking\ViewCarDetails;
use App\Livewire\BookingWizard;
use App\Livewire\ClientLandingPage;
use App\Livewire\CustomerPortal\Bookings;
use App\Livewire\CustomerPortal\Dashboard;
use App\Livewire\CustomerPortal\Reservations;
use App\Livewire\PartnerReport;
use App\Livewire\TenantRegister;
use App\Models\Booking;
use App\Models\Company;
use App\Models\Contract;
use App\Models\Plan;
use App\Models\Testimonial;
use Barryvdh\DomPDF\Facade\Pdf;
use Google\Client as GoogleClient;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Stephenjude\FilamentBlog\Models\Post;

$centralDomain = config('app.domain');

/*
|--------------------------------------------------------------------------
| 1. PUBLIC ROUTES (No Authentication Required)
|--------------------------------------------------------------------------
*/

// PWA Login
Route::get('/pwa-login', function () {
    if (Auth::check()) {
        $user = Auth::user();
        $company = $user->companies()->first();
        return $company ? redirect('/app/' . $company->slug) : redirect('/app');
    }
    return redirect('/app/login');
});

// Push Notifications
Route::post('/push-subscription', [App\Http\Controllers\PWA\PushController::class, 'store']);

/*
|--------------------------------------------------------------------------
| 2. MAIN WEBSITE ROUTES (Landing Pages, Content, Blog)
|--------------------------------------------------------------------------
*/

// Home Page
Route::get('/', function () {
    $companies = Company::whereHas('subscription', function ($q) {
        $q->whereDate('ends_at', '>=', now())
          ->whereHas('planPrice', function ($subQ) {
              $subQ->where('price', '>', 0);
          });
    })->get();

    return view('welcome', compact('companies'));
});

// Authentication & Registration
Route::get('/register-company', TenantRegister::class)->name('tenant.register');
Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

// Content Pages
Route::get('/pricing', function () {
    $plans = Plan::with('prices')
        ->where('is_active', 1)
        ->where('name', '!=', 'FREE TRIAL')
        ->get();
    return view('pricing', compact('plans'));
})->name('pricing');

Route::get('/terms-of-service', fn() => view('terms-of-service'))->name('terms-of-service');
Route::get('/privacy-policy', fn() => view('privacy-policy'))->name('privacy-policy');
Route::get('/cookies-policy', fn() => view('cookies-policy'))->name('cookies-policy');
Route::get('/contact-us', fn() => view('contact-us'))->name('contact');
Route::get('/testimonials', function () {
    $testimonials = Testimonial::latest()->get();
    return view('testimonials', compact('testimonials'));
})->name('testimonials');

// Blog System
Route::prefix('blog')->name('blog.')->group(function () {
    Route::get('/', function () {
        $posts = Post::published()->latest()->paginate(9);
        return view('blog-index', compact('posts'));
    })->name('index');

    Route::get('/{slug}', function ($slug) {
        $post = Post::with(['author', 'category'])->where('slug', $slug)->firstOrFail();
        return view('blog-view', compact('post'));
    })->name('show');
});

/*
|--------------------------------------------------------------------------
| 3. CUSTOMER PORTAL ROUTES (QR Code Based Login)
|--------------------------------------------------------------------------
*/

Route::prefix('customer')->name('customer.')->group(function () {
    // Login
    Route::post('/login', [CustomerLoginController::class, 'login'])
        ->name('login')
        ->middleware('throttle:5,60');

    // Dashboard
    Route::get('/{repeatToken}/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/{repeatToken}/reservations', Reservations::class)->name('reservations');
    Route::get('/{repeatToken}/bookings', Bookings::class)->name('bookings');

    // Redirect to dashboard
    Route::get('/{repeatToken}', function ($repeatToken) {
        return redirect()->route('customer.dashboard', ['repeatToken' => $repeatToken]);
    })->name('portal');

    // Logout
    Route::post('/logout', function () {
        session()->forget(['customer_token', 'customer_id']);
        $tenantSlug = session('tenant_slug', '');
        return redirect()->to('/' . $tenantSlug);
    })->name('logout');
});

/*
|--------------------------------------------------------------------------
| 4. TENANT WEBSITE ROUTES (Multi-Tenant Booking System)
|--------------------------------------------------------------------------
*/

Route::prefix('{tenant}')->group(function () {
    // Landing Page
    Route::get('/', ClientLandingPage::class)->name('client.page');
    
    // Car Details
    Route::get('/{car}/details', ViewCarDetails::class)->name('car.details');
    
    // Booking Wizard
    Route::get('/book', BookingWizard::class)->name('booking.wizard');
    
    // Reservation Success
    Route::get('/reservation/success/{reservationNumber}', function ($tenant, $reservationNumber) {
        $company = Company::where('slug', $tenant)->firstOrFail();
        return view('reservation.success', [
            'reservationNumber' => $reservationNumber,
            'tenant' => $tenant,
            'company' => $company
        ]);
    })->name('reservation.success');
});

/*
|--------------------------------------------------------------------------
| 5. PARTNER PORTAL ROUTES
|--------------------------------------------------------------------------
*/

Route::get('/partner/report/{token}', PartnerReport::class)
    ->name('partner.report')
    ->middleware('web');

/*
|--------------------------------------------------------------------------
| 6. PAYMENT & SUBSCRIPTION ROUTES
|--------------------------------------------------------------------------
*/

Route::controller(CheckoutController::class)->group(function () {
    Route::get('/subscription/success', 'create')->name('subscription.success');
    Route::get('/checkout/failure', fn() => 'Payment Failed.')->name('subscription.cancel');
});

/*
|--------------------------------------------------------------------------
| 7. GOOGLE CALENDAR INTEGRATION
|--------------------------------------------------------------------------
*/

Route::get('/google/calendar/callback', function () {
    $client = new GoogleClient();
    $client->setAuthConfig(config('services.google'));
    $client->addScope(\Google\Service\Calendar::CALENDAR);
    $client->setRedirectUri(route('google.calendar.callback'));
    $client->setAccessType('offline');
    $client->setPrompt('consent');

    $token = $client->fetchAccessTokenWithAuthCode(request('code'));
    $user = Auth::user();
    $oldToken = $user->google_token ? json_decode($user->google_token, true) : [];

    if (!isset($token['refresh_token']) && isset($oldToken['refresh_token'])) {
        $token['refresh_token'] = $oldToken['refresh_token'];
    }

    $user->update(['google_token' => json_encode($token)]);
    return response('<script>window.close();</script>');
})->name('google.calendar.callback');

// Google Authentication
Route::get('auth/google', [GoogleController::class, 'redirect'])->name('google.login');
Route::get('auth/google/callback', [GoogleController::class, 'callback']);

/*
|--------------------------------------------------------------------------
| 8. PDF & DOCUMENT GENERATION
|--------------------------------------------------------------------------
*/

// Contract Preview
Route::get('/preview-contract/{booking}', function (Booking $booking) {
    $company = auth()->user()->companies()->first();
    if (!$company) abort(403, 'Company not found.');

    $booking->load('car');
    $cachedTemplate = Cache::get("contract_template_{$company->id}");
    $body = $cachedTemplate 
        ? (new Contract(['body' => $cachedTemplate]))->render($booking) 
        : $company->contract?->render($booking);

    $renderedHtml = str_replace('', '<div class="print-pagebreak"></div>', $body);

    return view('contracts.rendered', [
        'body' => $renderedHtml,
        'booking' => $booking
    ]);
})->middleware(['web', 'auth'])->name('contract.preview');

// Invoice Download (Authenticated)
Route::get('/invoices/{id}/download', function ($id) {
    $booking = Booking::with('car')->findOrFail($id);
    $company = Company::findOrFail($booking->company_id);
    $invoiceBlade = explode('.', $company->invoice_template)[0];

    return Pdf::loadView('invoice.' . $invoiceBlade, [
        'invoiceData' => ['company' => $company, 'booking' => $booking]
    ])->download("invoice-{$booking->id}.pdf");
})->name('invoices.download');

// Invoice Download (Public - for tenants)
Route::get('/download-invoice/{tenant}/{booking}', function ($tenantId, $bookingId) {
    $company = Company::findOrFail($tenantId);
    $booking = Booking::with('car')->findOrFail($bookingId);
    $invoiceBlade = explode('.', $company->invoice_template)[0];

    return Pdf::loadView('invoice.' . $invoiceBlade, [
        'invoiceData' => ['company' => $company, 'booking' => $booking]
    ])->download('invoice.pdf');
})->name('invoice.download');

// Inspection Report Print
Route::get('/inspection-print/{inspection}', [InspectionReportController::class, 'download'])
    ->name('inspection.report.print')
    ->middleware(['signed', 'auth']);

/*
|--------------------------------------------------------------------------
| 9. DEVELOPMENT & TESTING ROUTES
|--------------------------------------------------------------------------
*/

Route::prefix('test')->group(function () {
    Route::get('/t3', fn() => view('invoice.invoice3'));
    Route::get('/expiry', fn() => view('emails.car-doc-expiry'))->name('expiry');
    Route::get('/layout', fn() => view('emails.layout'));
});