<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\WeatherController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\EconomyController;
use App\Http\Controllers\PortController;
use App\Http\Controllers\RiskScoreController;
use App\Http\Controllers\CompareController;
use App\Http\Controllers\WatchlistController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\ShipmentController;

use App\Http\Controllers\Api\DashboardController as ApiDashboardController;
use App\Http\Controllers\Api\CountryController as ApiCountryController;
use App\Http\Controllers\Api\WeatherController as ApiWeatherController;
use App\Http\Controllers\Api\CurrencyController as ApiCurrencyController;
use App\Http\Controllers\Api\EconomyController as ApiEconomyController;
use App\Http\Controllers\Api\PortController as ApiPortController;
use App\Http\Controllers\Api\NewsController as ApiNewsController;
use App\Http\Controllers\Api\ShipmentController as ApiShipmentController;
use App\Http\Controllers\Api\MapApiController as ApiMapApiController;
use App\Http\Controllers\Api\ProfileApiController as ApiProfileApiController;

/*
|--------------------------------------------------------------------------
| AUTHENTICATION (PUBLIC)
|--------------------------------------------------------------------------
*/
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::any('/logout', [LoginController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| AUTHENTICATED WEB & API ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role'])->group(function () {

    // Web Pages
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Country Importers / Exporters (WAJIB di atas resource)
    Route::get('/countries/import-api', [CountryController::class, 'importFromApi'])->name('countries.importApi');
    Route::post('/countries/import-csv', [CountryController::class, 'importCsv'])->name('countries.importCsv');
    Route::get('/countries/export-excel', [CountryController::class, 'exportExcel'])->name('countries.exportExcel');
    Route::get('/countries/export-pdf', [CountryController::class, 'exportPdf'])->name('countries.exportPdf');

    // Resources
    Route::resource('countries', CountryController::class);
    Route::resource('weather', WeatherController::class);
    Route::resource('currency', CurrencyController::class);
    Route::resource('economy', EconomyController::class);
    Route::resource('ports', PortController::class);
    Route::resource('risk-score', RiskScoreController::class);
    Route::resource('watchlist', WatchlistController::class);
    Route::resource('news', NewsController::class);
    Route::get('/news/{id}', [NewsController::class, 'show'])->name('news.show');

    // Weather API update route
    Route::post('/weather/update-data', [WeatherController::class, 'updateWeather'])->name('weather.updateWeather');

    // Currency sync route
    Route::post('/currency/sync', [CurrencyController::class, 'sync'])->name('currency.sync');

    // Economy sync route
    Route::post('/economy/sync', [EconomyController::class, 'sync'])->name('economy.sync');

    // Ports sync route
    Route::post('/ports/sync', [PortController::class, 'sync'])->name('ports.sync');

    // News sync route
    Route::post('/news/sync', [NewsController::class, 'sync'])->name('news.sync');

    // Risk score calculate route
    Route::post('/risk-score/calculate', [RiskScoreController::class, 'calculate'])->name('risk-score.calculate');

    // Compare
    Route::get('/compare-countries', [CompareController::class, 'index'])->name('compare.index');
    Route::post('/compare-countries', [CompareController::class, 'compare'])->name('compare.result');

    // Upcoming / New Pages views
    Route::get('/global-map', function () {
        return view('map.index');
    })->name('map.index');

    // Shipment routes
    Route::get('/shipment-planner', [ShipmentController::class, 'planner'])->name('shipments.planner');
    Route::post('/shipments', [ShipmentController::class, 'store'])->name('shipments.store');
    Route::get('/shipment-history', [ShipmentController::class, 'history'])->name('shipments.history');
    Route::get('/shipments/{id}', [ShipmentController::class, 'show'])->name('shipments.show');
    Route::post('/shipments/{id}/status', [ShipmentController::class, 'updateStatus'])->name('shipments.updateStatus');

    Route::get('/admin-panel', function () {
        return view('admin.panel');
    })->name('admin.panel');

    /*
    |--------------------------------------------------------------------------
    | API ENDPOINTS (AUTHENTICATED VIA WEB SESSION)
    |--------------------------------------------------------------------------
    */
    Route::prefix('api')->group(function () {
        Route::get('/dashboard', [ApiDashboardController::class, 'index']);

        Route::get('/countries', [ApiCountryController::class, 'index']);
        Route::post('/countries', [ApiCountryController::class, 'store']);
        Route::get('/countries/{id}', [ApiCountryController::class, 'show']);
        Route::put('/countries/{id}', [ApiCountryController::class, 'update']);
        Route::delete('/countries/{id}', [ApiCountryController::class, 'destroy']);
        Route::post('/countries/import', [ApiCountryController::class, 'import']);

        Route::get('/weather', [ApiWeatherController::class, 'index']);
        Route::post('/weather', [ApiWeatherController::class, 'store']);
        Route::get('/weather/{id}', [ApiWeatherController::class, 'show']);
        Route::put('/weather/{id}', [ApiWeatherController::class, 'update']);
        Route::delete('/weather/{id}', [ApiWeatherController::class, 'destroy']);
        Route::post('/weather/sync', [ApiWeatherController::class, 'sync']);

        Route::get('/currency', [ApiCurrencyController::class, 'index']);
        Route::post('/currency', [ApiCurrencyController::class, 'store']);
        Route::get('/currency/{id}', [ApiCurrencyController::class, 'show']);
        Route::put('/currency/{id}', [ApiCurrencyController::class, 'update']);
        Route::delete('/currency/{id}', [ApiCurrencyController::class, 'destroy']);
        Route::post('/currency/sync', [ApiCurrencyController::class, 'sync']);

        Route::get('/economy', [ApiEconomyController::class, 'index']);
        Route::post('/economy', [ApiEconomyController::class, 'store']);
        Route::get('/economy/{country}', [ApiEconomyController::class, 'show']);
        Route::put('/economy/{id}', [ApiEconomyController::class, 'update']);
        Route::delete('/economy/{id}', [ApiEconomyController::class, 'destroy']);
        Route::post('/economy/sync', [ApiEconomyController::class, 'sync']);

        Route::get('/ports', [ApiPortController::class, 'index']);
        Route::post('/ports', [ApiPortController::class, 'store']);
        Route::get('/ports/{id}', [ApiPortController::class, 'show']);
        Route::put('/ports/{id}', [ApiPortController::class, 'update']);
        Route::delete('/ports/{id}', [ApiPortController::class, 'destroy']);
        Route::post('/ports/sync', [ApiPortController::class, 'sync']);

        Route::get('/news', [ApiNewsController::class, 'index']);
        Route::post('/news', [ApiNewsController::class, 'store']);
        Route::get('/news/{id}', [ApiNewsController::class, 'show']);
        Route::put('/news/{id}', [ApiNewsController::class, 'update']);
        Route::delete('/news/{id}', [ApiNewsController::class, 'destroy']);
        Route::post('/news/sync', [ApiNewsController::class, 'sync']);

        Route::get('/map', [ApiMapApiController::class, 'index']);

        Route::get('/shipments', [ApiShipmentController::class, 'index']);
        Route::get('/shipments/planners', [ApiShipmentController::class, 'planners']);
        Route::post('/shipments', [ApiShipmentController::class, 'store']);
        Route::get('/shipments/history/{tracking_number}', [ApiShipmentController::class, 'history']);
        Route::post('/shipments/history/{id}', [ApiShipmentController::class, 'addHistory']);
        Route::get('/shipments/stats', [ApiShipmentController::class, 'stats']);

        Route::get('/profile', [ApiProfileApiController::class, 'show']);
        Route::post('/profile', [ApiProfileApiController::class, 'update']);
        Route::post('/profile/photo', [ApiProfileApiController::class, 'uploadPhoto']);
        Route::post('/profile/password', [ApiProfileApiController::class, 'changePassword']);
        Route::get('/profile/activities', [ApiProfileApiController::class, 'activityLog']);
    });
});