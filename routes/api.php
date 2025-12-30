<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Users\UserController;

use App\Http\Controllers\Subscriptions\PurchaseController;
use App\Http\Controllers\Companies\CompanyController;

// use App\Http\Controllers\Client\ClientController;

use App\Http\Controllers\Auth\OTPController;
use App\Http\Controllers\Client\ClientController;
use App\Http\Controllers\Subscriptions\SubscriptionController;
use App\Models\ClientFinancialApproval;
use App\Http\Controllers\Employee\EmployeeController;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

// Route::get('/storage-link', function () {

// if (File::exists(public_path('storage'))) {
// return response()->json([
// 'status' => true,
// 'message' => 'Storage link already exists'
// ]);
// }

// Artisan::call('storage:link');

// return response()->json([
// 'status' => true,
// 'message' => 'Storage link created successfully'
// ]);
// });


Route::get('/ping', function () {
    return ["status" => "API Working"];
});
Route::post('/user/create', [OTPController::class, 'sendUserOtp']);
Route::POST('/verifyotpregister',[OTPController::class,'registerOtpVerify']);
Route::POST('/setpassword',[OTPController::class,'setpassword']);
Route::post('/login', [UserController::class, 'login']);




Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });


    // Route::get('/company/view',[CompanyController::class,'viewcompany']);
    
    Route::post('/create/client/services',[ClientController::class,'ClientServicestore']);
    Route::post('/create/client',[ClientController::class,'createclient']);

    Route::post('/create/client/financial',[ClientController::class,'clientfinancial']);
    
    Route::post('/create/client/consignee',[ClientController::class,'addClientConsignees']);

    Route::get('/client/view',[ClientController::class,'viewclient']);
    Route::get('/is-subscribes',[SubscriptionController::Class,'issubscribed']);


    Route::get('/select/consignee/',[ClientController::class,'consignee']);

    Route::get('/select/designation',[ClientController::class,'designation']);

    Route::Post('/purchase-subscription',[PurchaseController::class,'purchasesubscription']);
    Route::get('/user-profile', [UserController::class, 'profile']);

    Route::get('/is-subscriptions',[SubscriptionController::class,'issubscribedwithlogin']);
    Route::post('/update-profile', [UserController::class, 'updateProfile']);
    Route::post('/logout', [UserController::class, 'logout']);


Route::post('/employee/create',[UserController::class,'employeecreate']);

    




Route::prefix('employee')->group(function () {

            Route::post('/store', [EmployeeController::class, 'store']);
            Route::get('/list', [EmployeeController::class, 'index']);
            Route::get('/view/{id}', [EmployeeController::class, 'show']);
    
            
});



    
    Route::prefix('company')
    ->controller(CompanyController::class)
    ->group(function () {
    
        Route::get('/view', 'viewcompany')->name('company.view');
        Route::post('/create', 'create')->name('company.create');
        Route::patch('/update', 'update')->name('company.update');
        Route::delete('/delete', 'delete')->name('company.delete');
    
    });
    
});




Route::post('/login',[UserController::class,'login']);

Route::post('/signup',[UserController::class,'signup']);

Route::Post('/phone-number-verify',[OTPController::class,'verifyphone']);
Route::Post('/emailverifyotp',[OTPController::class,'verifyEmailOtp']);





Route::post('/verifyforgetotp',[UserController::class,'verifyForgetOtp']);

Route::post('/forgetpassword', [UserController::class, 'forgetpassword']);


Route::Post('/phone-verify-otp',[OTPController::class,'verifyphoneotp']);

Route::Post('/emailverify',[OTPController::class,'emailverify']);

Route::Post('/resetpassword',[UserController::class,'resetpassword']);

Route::post('/signinwithgoogle', [UserController::class, 'googleLogin']);

Route::Post('/signup',[UserController::class,'signup']);
Route::prefix('subscriptions')->group(function () {
    Route::get('/', [SubscriptionController::class, 'index']);
    Route::get('/billing/{type}', [SubscriptionController::class, 'byBillingType']);
    Route::get('/{id}', [SubscriptionController::class, 'show']);
});

// Harsh


