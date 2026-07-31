<?php

use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\I18nController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\ThemeController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Home route: redirect based on authentication state to prevent infinite loops
Route::get('/', function () {
    if (auth('admin')->check()) {
        return redirect()->route('admin.dashboard');
    }
    if (auth()->check()) {
        return redirect()->route('client.dashboard');
    }

    return redirect()->route('login');
})->name('home');

// Auth routes
require __DIR__.'/auth.php';

// Locale / currency / theme
Route::put('/locale', [LocaleController::class, 'update'])->name('locale.update');
Route::get('/i18n/{locale}', [I18nController::class, 'show'])->name('i18n.show');
Route::put('/currency', [CurrencyController::class, 'update'])->name('currency.update');
Route::post('/theme/update', [ThemeController::class, 'update'])->name('theme.update');

// Public marketing pages redirect to login
Route::get('/contact', fn () => redirect()->route('login'))->name('contact');
Route::post('/contact', fn () => abort(404))->name('contact.store');

// Public marketing landing pages redirect to login
Route::get('/pricing', fn () => redirect()->route('login'))->name('pricing');
Route::get('/faq', fn () => redirect()->route('login'))->name('faq');
Route::get('/use-cases', fn () => redirect()->route('login'))->name('use-cases');
Route::get('/about', fn () => redirect()->route('login'))->name('about');
Route::get('/integrations', fn () => redirect()->route('login'))->name('integrations');

// CMS pages (e.g. /p/privacy, /p/terms)
Route::get('/p/{slug}', fn () => abort(404))->name('cms-page.show');

// Sitemap & robots.txt
Route::get('/sitemap.xml', function () {
    $urls = [route('login')];
    $xml = '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    foreach ($urls as $url) {
        $xml .= '<url><loc>'.htmlspecialchars($url).'</loc></url>';
    }
    $xml .= '</urlset>';

    return response($xml, 200)->header('Content-Type', 'application/xml');
})->name('sitemap');

Route::get('/robots.txt', function () {
    $sitemap = route('sitemap');

    return response(
        "User-agent: *\nDisallow: /admin/\nDisallow: /app/\nSitemap: {$sitemap}",
        200
    )->header('Content-Type', 'text/plain');
})->name('robots');

// Webhooks 404 stubs for unused payment gateways
Route::middleware('throttle:webhooks')->group(function () {
    Route::post('/webhooks/stripe', fn () => abort(404))->name('webhooks.stripe');
    Route::post('/webhooks/paypal', fn () => abort(404))->name('webhooks.paypal');
    Route::post('/webhooks/paddle', fn () => abort(404))->name('webhooks.paddle');
    Route::post('/webhooks/razorpay', fn () => abort(404))->name('webhooks.razorpay');
    Route::post('/webhooks/cashfree', fn () => abort(404))->name('webhooks.cashfree');
    Route::post('/webhooks/tap', fn () => abort(404))->name('webhooks.tap');
    Route::post('/webhooks/paystack', fn () => abort(404))->name('webhooks.paystack');
    Route::post('/webhooks/xendit', fn () => abort(404))->name('webhooks.xendit');
    Route::post('/webhooks/paymob', fn () => abort(404))->name('webhooks.paymob');
    Route::post('/webhooks/myfatoorah', fn () => abort(404))->name('webhooks.myfatoorah');
    Route::post('/webhooks/mollie', fn () => abort(404))->name('webhooks.mollie');
    Route::post('/webhooks/square', fn () => abort(404))->name('webhooks.square');
    Route::post('/webhooks/mercadopago', fn () => abort(404))->name('webhooks.mercadopago');
});

// ─── Health / readiness probes ───────────────────────────────────────────────
// Protected by a shared secret token (HEALTHZ_TOKEN env var). Set to a random
// string in production and pass via Authorization: Bearer <token> header.
Route::middleware('throttle:30,1')->group(function () {
    $guardHealthz = function (Illuminate\Http\Request $request): bool {
        $token = config('app.healthz_token');

        return ! filled($token) || hash_equals($token, $request->bearerToken() ?? '');
    };

    Route::get('/healthz/db', function () use ($guardHealthz) {
        if (! $guardHealthz(request())) {
            return response()->json(['error' => 'Unauthorized.'], 401);
        }
        try {
            DB::selectOne('SELECT 1');

            return response()->json(['status' => 'ok', 'db' => 'connected']);
        } catch (Throwable $e) {
            return response()->json(['status' => 'error', 'db' => 'database error'], 503);
        }
    })->name('healthz.db');

    Route::get('/healthz/redis', function () use ($guardHealthz) {
        if (! $guardHealthz(request())) {
            return response()->json(['error' => 'Unauthorized.'], 401);
        }
        try {
            Redis::ping();

            return response()->json(['status' => 'ok', 'redis' => 'connected']);
        } catch (Throwable $e) {
            return response()->json(['status' => 'error', 'redis' => 'redis error'], 503);
        }
    })->name('healthz.redis');

    Route::get('/healthz/queue', function () use ($guardHealthz) {
        if (! $guardHealthz(request())) {
            return response()->json(['error' => 'Unauthorized.'], 401);
        }
        try {
            $size = Queue::size('default');

            return response()->json(['status' => 'ok', 'queue_driver' => config('queue.default'), 'default_size' => $size]);
        } catch (Throwable $e) {
            return response()->json(['status' => 'error', 'queue' => 'queue error'], 503);
        }
    })->name('healthz.queue');
});
