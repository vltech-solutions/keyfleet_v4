<?php

use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\CheckoutController;
use App\Livewire\Booking\ViewCarDetails;
use App\Livewire\BookingWizard;
use App\Livewire\ClientLandingPage;
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
use App\Http\Controllers\InspectionReportController;

$centralDomain = config('app.domain');
/*
|--------------------------------------------------------------------------
| 1. SUBDOMAIN / TENANT WEBSITE ROUTES
|--------------------------------------------------------------------------
*/

Route::get('/pwa-login', function () {
    if (Auth::check()) {
      	$user = Auth::user();
        
        $company = $user->companies()->first();

        if ($company) {
            return redirect('/app/' . $company->slug);
        }

        return redirect('/app');
    }
    return redirect('/app/login');
});

Route::post('/push-subscription', [App\Http\Controllers\PWA\PushController::class, 'store']);

Route::domain('{tenant}.' . $centralDomain)->group(function () {
    // Ito na ang magiging main landing page ng tenant (Booking-v2)
    Route::get('/', ClientLandingPage::class)->name('client.page');
    Route::get('/{car}/details', ViewCarDetails::class)->name('car.details');

    Route::get('/book', BookingWizard::class)->name('booking.wizard.v2');
    Route::get('/reservation/success/{reservationNumber}', function ($tenant, $reservationNumber) {
        $company = Company::where('slug', $tenant)->firstOrFail();
        return view('reservation.success', ['reservationNumber' => $reservationNumber, 'tenant' => $tenant, 'company' => $company]);
    })->name('reservation.success');

});

/*
|--------------------------------------------------------------------------
| 2. CENTRAL DOMAIN ROUTES (Main Landing Page & System)
|--------------------------------------------------------------------------
*/

Route::domain($centralDomain)->group(function () {
    Route::middleware([\App\Http\Middleware\PreventRequestsDuringMaintenance::class])->group(function () {
        
        // Main Home Page
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
            $plans = Plan::with('prices')->where('is_active', 1)->where('name','!=','FREE TRIAL')->get();
            return view('pricing', compact('plans'));
        });
        Route::get('/terms-of-service', fn() => view('terms-of-service'));
        Route::get('/privacy-policy', fn() => view('privacy-policy'));
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
    });
});

/*
|--------------------------------------------------------------------------
| 3. TRANSACTIONS & BOOKING (Subdirectory Style)
|--------------------------------------------------------------------------
*/

Route::get('/{tenant}/book', BookingWizard::class)->name('booking.wizard');
Route::get('/{tenant}/reservation/success/{reservationNumber}', function ($tenant, $reservationNumber) {
    $company = Company::where('slug', $tenant)->firstOrFail();
    return view('reservation.success', ['reservationNumber' => $reservationNumber, 'tenant' => $tenant, 'company' => $company]);
})->name('reservation.success');

/*
|--------------------------------------------------------------------------
| 4. PAYMENTS & GOOGLE CALENDAR
|--------------------------------------------------------------------------
*/

Route::controller(CheckoutController::class)->group(function () {
    Route::get('/subscription/success', 'create')->name('subscription.success');
    Route::get('/checkout/failure', fn() => 'Payment Failed.')->name('subscription.cancel');
});

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

/*
|--------------------------------------------------------------------------
| 5. PDF & DOCUMENT GENERATION
|--------------------------------------------------------------------------
*/

Route::get('/preview-contract/{booking}', function (Booking $booking) {
    $company = auth()->user()->companies()->first();
    if (!$company) abort(403, 'Company not found.');

    $booking->load('car');
    $cachedTemplate = Cache::get("contract_template_{$company->id}");
    $body = $cachedTemplate ? (new Contract(['body' => $cachedTemplate]))->render($booking) : $company->contract?->render($booking);

    $renderedHtml = str_replace('', '<div class="print-pagebreak"></div>', $body);

    return view('contracts.rendered', ['body' => $renderedHtml, 'booking' => $booking]);
})->middleware(['web', 'auth'])->name('contract.preview');

Route::get('/invoices/{id}/download', function ($id) {
    $booking = Booking::with('car')->findOrFail($id);
    $company = Company::findOrFail($booking->company_id);
    $invoiceBlade = explode('.', $company->invoice_template)[0];

    return Pdf::loadView('invoice.' . $invoiceBlade, ['invoiceData' => ['company' => $company, 'booking' => $booking]])
              ->download("invoice-{$booking->id}.pdf");
})->name('invoices.download');

// Dedicated route for public tenant invoice download
Route::get('/download-invoice/{tenant}/{booking}', function ($tenantId, $bookingId) {
    $company = Company::findOrFail($tenantId);
    $booking = Booking::with('car')->findOrFail($bookingId);
    $invoiceBlade = explode('.', $company->invoice_template)[0];

    return Pdf::loadView('invoice.' . $invoiceBlade, ['invoiceData' => ['company' => $company, 'booking' => $booking]])
              ->download('invoice.pdf');
})->name('invoice.download');

Route::get('/inspection-print/{inspection}', [InspectionReportController::class, 'download'])
    ->name('inspection.report.print')
    ->middleware(['signed', 'auth']);

/*
|--------------------------------------------------------------------------
| 6. DEV / TESTING ROUTES
|--------------------------------------------------------------------------
*/

Route::prefix('test')->group(function () {
    Route::get('/t3', fn() => view('invoice.invoice3'));
    Route::get('/expiry', fn() => view('emails.car-doc-expiry'))->name('expiry');
    Route::get('/layout', fn() => view('emails.layout'));
});


Route::get('auth/google', [GoogleController::class, 'redirect'])->name('google.login');
Route::get('auth/google/callback', [GoogleController::class, 'callback']);