<?php

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Middleware\Cors;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CronController;
use App\Http\Controllers\InstallController;
use App\Http\Controllers\DataReturnController;
use App\Http\Controllers\Order\OrderItemController;
use App\Http\Controllers\MiscController;
use App\Http\Controllers\Order\OrderUpdateController;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

// ToDo: Comment on install
/*
Route::get('/install', [InstallController::class, 'install'])->name('install.install');

Route::get('/sftp-test', function () {
    try {
        return Storage::disk('nomane_ftp_out_folders')->files('/');
    } catch (\Exception $e) {

        LOG::debug($e->getMessage());

        return response()->json([
            'error' => $e->getMessage(),
        ]);
    }
});
*/

/*Route::post('/sanctum/get-token', function (Request $request) {

    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    if ($request->email == 'api.user@callmedicall.com') {
        return [ 'wrongLogin' => ['The provided credentials are incorrect.'] ];
    }

    $user = User::where('email', $request->email)->first();
    if ( ! $user || ! Hash::check($request->password, $user->password)) {
        return [ 'wrongLogin' => ['The provided credentials are incorrect.'] ];
    }
    //Log::info($user);
    $user->tokens()->delete();
    return [ 'token' => [ $user->createToken('user-token', ['*'], now()->addMinutes( (int) env('SANCTUM_TOKEN_EXPIRATION')))->plainTextToken],
            'user_type' =>[ $user->user_type != '' ? $user->user_type : 'Call' ]
];
})->middleware(['throttle:6,1']);*/


Route::post('/sanctum/get-token', function (Request $request) {
    $attempts = session()->get('login_attempts', 0);

    // 1. Validation de base : si >= 3 échecs → captcha obligatoire
    $rules = [
        'email' => 'required|email',
        'password' => 'required',
    ];

    if ($attempts >= 3) {
        $rules['captcha_token'] = 'required|string';
    }

    $validated = $request->validate($rules);

    // 2. Blocage manuel d'un utilisateur si email spécifique
    if ($request->email === 'api.user@callmedicall.com') {
        return response()->json([
            'wrongLogin' => ['The provided credentials are incorrect.']
        ]);
    }

    // 3. Recherche utilisateur
    $user = User::where('email', $request->email)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        session()->put('login_attempts', $attempts + 1);

        $showCaptcha = session()->get('login_attempts') >= 3;

        return response()->json([
            'wrongLogin' => ['The provided credentials are incorrect.'],
            'show_captcha' => $showCaptcha
        ]);
    }

    // 4. Vérification CAPTCHA si nécessaire
    if ($attempts >= 3) {
        $captchaToken = $request->input('captcha_token');

        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => config('services.recaptcha.secret'),
            'response' => $captchaToken,
            'remoteip' => $request->ip(), // recommandé
        ]);

        $result = $response->json();

        if (!($result['success'] ?? false)) {
            return response()->json([
                'validate_captcha' => ['Le CAPTCHA est invalide !']
            ]);
        }
    }

    // 5. Authentification réussie : reset du compteur, création du token
    session()->forget('login_attempts');
    $user->tokens()->delete(); // Révocation d'anciens tokens

    $token = $user->createToken('user-token', ['*'], now()->addMinutes(
        (int) env('SANCTUM_TOKEN_EXPIRATION', 60))
    )->plainTextToken;

    return response()->json([
        'token' => [$token],
        'user_type' => [$user->user_type ?: 'Call']
    ]);
})->middleware(['throttle:6,1']);


Route::post('/sanctum/verify-token', [AuthController::class, 'verifyToken'])->name('verifyToken')
    ->middleware(['throttle:60,1']);

Route::post('/login', [AuthController::class, 'login'])->name('login');

Route::prefix('api')->middleware('auth:sanctum')->withoutMiddleware([VerifyCsrfToken::class])->group(function () {

    # Misc routes
    Route::post('/error-notification', [MiscController::class, 'errorNotification'])->name('errorNotification');

    Route::post('/product-update', [MiscController::class, 'productUpdate'])->name('productUpdate');
    Route::post('/product-export', [MiscController::class, 'productImport'])->name('productImport');
    Route::post('/product-import-stock', [MiscController::class, 'productImportStock'])->name('productImportStock');
    Route::post('/pharmacy-update', [MiscController::class, 'pharmacyUpdate'])->name('pharmacyUpdate');

    Route::get('/ldap-sync', [MiscController::class, 'ldapSync'])->name('ldapSync');

    # Search routes
    Route::post('/search/pharmacy-search', [DataReturnController::class, 'pharmacySearch'])->name('pharmacySearch');
    Route::post('/search/product-search', [DataReturnController::class, 'productSearch'])->name('productSearch');

    # Get routes
    Route::post('/get/pharmacy', [DataReturnController::class, 'getPharmacy'])->name('getPharmacy');
    Route::post('/get/pharmacy-id', [DataReturnController::class, 'getPharmacyId'])->name('getPharmacyId');
    Route::post('/get/create-pharmacy', [DataReturnController::class, 'getCreatePharmacy'])->name('getCreatePharmacy');
    Route::post('/get/all-products', [DataReturnController::class, 'getAllProducts'])->name('getAllProducts');
    Route::post('/get/product', [DataReturnController::class, 'getProduct'])->name('getProduct');
    //test
    Route::get('/get/products', [DataReturnController::class, 'getProducts'])->name('getProducts');

    Route::post('/get/product-id', [DataReturnController::class, 'getProductId'])->name('getProductId');
    Route::post('/get/orders', [DataReturnController::class, 'getOrders'])->name('getOrders');
    Route::post('/get/orders-farm', [DataReturnController::class, 'getOrdersFarm'])->name('getOrdersFarm');
    Route::post('/get/pharmacies', [DataReturnController::class, 'getPharmacies'])->name('getPharmacies');
    Route::post('/get/products', [DataReturnController::class, 'getProducts'])->name('getProducts');
    Route::post('/get/products-all', [DataReturnController::class, 'getProductsAll'])->name('getProductsAll');
    Route::post('/get/categories', [DataReturnController::class, 'getCategories'])->name('getCategories');

    # Order routes
    Route::post('/order/item-add', [OrderItemController::class, 'orderItemAdd'])->name('orderItemAdd');
    Route::post('/order/item-remove', [OrderItemController::class, 'orderItemRemove'])->name('orderItemRemove');
    Route::post('/order/get-order', [OrderUpdateController::class, 'getOrder'])->name('getOrder');
    Route::post('/order/save-order', [OrderUpdateController::class, 'saveOrder'])->name('saveOrder');

    # Data inputs
    Route::get('/in/pharmacies', [CronController::class, 'pharmacies'])->name('cron.pharmacies');
    Route::get('/in/products', [CronController::class, 'products'])->name('cron.products');
    Route::get('/in/trade-policy', [CronController::class, 'tradePolicy'])->name('cron.tradePolicy');
    Route::get('/in/product-control-file', [CronController::class, 'productControlFile'])->name('cron.productControlFile');
    Route::get('/in/price-control-file', [CronController::class, 'priceControlFile'])->name('cron.priceControlFile');
    Route::get('/in/process-unavailable-products', [CronController::class, 'procesUnavailableProducts'])->name('cron.procesUnaAvailableProducts');
    //Route::get('/in/short-term-products', [CronController::class, 'shortTermProducts'])->name('cron.shortTermProducts');
    //Route::get('/in/product-quotes', [CronController::class, 'productQuotes'])->name('cron.productQuotes');
    Route::get('/in/blocked-orders-in', [CronController::class, 'blockedOrdersIn'])->name('cron.blockedOrdersIn');
    Route::get('/in/cagedim-orders', [CronController::class, 'cagedimOrders'])->name('cron.cagedimOrders');
    Route::get('/in/customer-sanitation', [CronController::class, 'customerSanitation'])->name('cron.customerSanitation');
    Route::get('/in/cancelled-orders', [CronController::class, 'cancelledOrders'])->name('cron.cancelledOrders');
    //Route::get('/in/products-under-allocation', [CronController::class, 'productsUnderAllocation'])->name('cron.productsUnderAllocation');

    # Data outputs
    //Route::get('/out/product-integration-check', [CronController::class, 'productIntegrationCheck'])->name('cron.productIntegrationCheck');
    Route::get('/out/orders-sent-to-noName', [CronController::class, 'ordersSentToNomane'])->name('cron.ordersSentToNomane');
    Route::get('/out/new-customer-or-change', [CronController::class, 'newCustomerOrChange'])->name('cron.newCustomerOrChange');
    //Route::get('/out/orders-exported-to-noName', [CronController::class, 'ordersExportedToNomane'])->name('cron.ordersExportedToNomane');
    Route::get('/out/rolling-order-history', [CronController::class, 'rollingOrderHistory'])->name('cron.rollingOrderHistory');
    Route::get('/out/weekly-order-confirmations', [CronController::class, 'weeklyOrderConfirmations'])->name('cron.weeklyOrderConfirmations');
    Route::get('/out/monthly-activity-reporting', [CronController::class, 'monthlyActivityReporting'])->name('cron.monthlyActivityReporting');
    Route::get('/out/quarterly-activity-reporting', [CronController::class, 'quarterlyActivityReporting'])->name('cron.quarterlyActivityReporting');
    Route::get('/out/blocked-orders-out', [CronController::class, 'blockedOrdersOut'])->name('cron.blockedOrdersOut');
    Route::get('/out/product-audit', [CronController::class, 'productAudit'])->name('cron.productAudit');
});
