<?php

use App\Http\Middleware\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\NodeApiController;

/*
|--------------------------------------------------------------------------
| NovaSMM Node-compat API (primary)
|--------------------------------------------------------------------------
*/
Route::get('/health', [NodeApiController::class,'health']);
Route::get('/bootstrap', [NodeApiController::class,'bootstrap']);

Route::post('/auth/setup-admin', [NodeApiController::class,'setupAdmin']);
Route::post('/auth/register', [NodeApiController::class,'register']);
Route::post('/auth/login', [NodeApiController::class,'login']);
Route::post('/auth/logout', [NodeApiController::class,'logout']);
Route::post('/auth/forgot-password', [NodeApiController::class,'forgotPassword']);
Route::post('/auth/reset-password', [NodeApiController::class,'resetPassword']);
Route::get('/auth/me', [NodeApiController::class,'me']);

Route::middleware([\App\Http\Middleware\ApiSpaSessionAuth::class, 'web'])->group(function () {
  Route::get('/user', [NodeApiController::class,'meUser']);
  Route::post('/profile/update', [NodeApiController::class,'profileUpdate']);
  Route::post('/profile/password', [NodeApiController::class,'profilePassword']);
  Route::post('/profile/token', [NodeApiController::class,'profileToken']);
  Route::post('/profile/token/revoke', [NodeApiController::class,'profileTokenRevoke']);

  Route::get('/orders', [NodeApiController::class,'listOrders']);
  Route::post('/orders', [NodeApiController::class,'createOrder']);
  Route::post('/orders/store', [NodeApiController::class,'createOrder']); // legacy
  Route::get('/orders/get-by-id', [NodeApiController::class,'getOrderById']);
  Route::get('/orders/get-by-ids', [NodeApiController::class,'getOrdersByIds']);
  Route::post('/orders/{id}/sync', [NodeApiController::class,'syncOrder']);
  Route::post('/orders/{id}/refill', [NodeApiController::class,'refillOrder']);
  Route::post('/orders/{id}/cancel', [NodeApiController::class,'cancelOrder']);
  Route::post('/orders/{id}/{action}', [NodeApiController::class,'orderAction']);

  Route::get('/deposits', [NodeApiController::class,'deposits']);
  Route::post('/deposits/invoice', [NodeApiController::class,'createInvoice']);
  Route::post('/deposits/voucher', [NodeApiController::class,'redeemVoucher']);

  Route::get('/transactions', [NodeApiController::class,'transactions']);
  Route::get('/affiliates', [NodeApiController::class,'affiliates']);
  Route::post('/affiliates/withdraw', [NodeApiController::class,'affiliateWithdraw']);
  Route::get('/notifications', [NodeApiController::class,'notifications']);
  Route::post('/notifications/read', [NodeApiController::class,'notificationsRead']);
  Route::get('/search', [NodeApiController::class,'search']);

  // legacy users routes
  Route::get('/users/histories', [NodeApiController::class,'userHistories']);
  Route::get('/users/transactions', [NodeApiController::class,'transactions']);
  Route::get('/users/invoices', [NodeApiController::class,'userInvoices']);
  Route::post('/users/invoices', [NodeApiController::class,'createInvoice']);
  Route::get('/users/invoices/{id}', [NodeApiController::class,'userInvoiceShow']);
  Route::get('/users/banks', [NodeApiController::class,'banks']);
  Route::post('/users/affiliates/withdraw', [NodeApiController::class,'affiliateWithdraw']);
  Route::post('/users/vouchers/redeem', [NodeApiController::class,'redeemVoucher']);
  Route::get('/users/card-list', fn() => response()->json(['data'=>[],'status'=>200,'message'=>'Card gateway not configured']));
  Route::post('/users/send-card', fn() => response()->json(['status'=>501,'message'=>'Card deposit not configured'],501));
  Route::post('/deposit/paypal-confirm', fn() => response()->json(['status'=>501,'message'=>'PayPal not configured'],501));
});

Route::post('/deposits/webhook', [NodeApiController::class,'depositWebhook']);

// Preset / tools / v2 compat
Route::prefix('/preset-data')->group(function () {
  Route::get('/platforms', [NodeApiController::class,'presetPlatforms']);
  Route::get('/categories', [NodeApiController::class,'presetCategories']);
  Route::get('/services', [NodeApiController::class,'listServices']);
  Route::get('/service-info', [NodeApiController::class,'presetServiceInfo']);
});
Route::prefix('/tools')->group(function () {
  Route::post('/calculate', [NodeApiController::class,'calculate']);
  Route::get('/facebook/get-uid', fn() => response()->json(['status'=>501,'message'=>'Not configured'],501));
  Route::get('/get-form/{form_type}', fn($form_type) => response()->json(['status'=>200,'data'=>['form_type'=>$form_type,'html'=>'']]));
});
Route::match(['get','post'],'/v2', [NodeApiController::class,'v2']);

// Admin
Route::middleware(['auth:sanctum', Admin::class])->prefix('/admin')->group(function () {
  Route::get('/overview', [NodeApiController::class,'adminOverview']);
  Route::post('/orders/{id}/status', [NodeApiController::class,'adminOrderStatus']);
  Route::post('/invoices/{id}/approve', [NodeApiController::class,'adminInvoiceApprove']);
  Route::post('/invoices/{id}/reject', [NodeApiController::class,'adminInvoiceReject']);
  Route::post('/services', [NodeApiController::class,'adminCreateService']);
  Route::put('/services/{id}', [NodeApiController::class,'adminUpdateService']);
  Route::delete('/services/{id}', [NodeApiController::class,'adminDeleteService']);
  Route::post('/platforms', [NodeApiController::class,'adminCreatePlatform']);
  Route::post('/categories', [NodeApiController::class,'adminCreateCategory']);
  Route::post('/users/{id}/balance', [NodeApiController::class,'adminAdjustUser']);
  Route::post('/users/{id}/reset-token', [NodeApiController::class,'adminResetToken']);
  Route::post('/banks', [NodeApiController::class,'adminCreateBank']);
  Route::put('/banks/{id}', [NodeApiController::class,'adminUpdateBank']);
  Route::delete('/banks/{id}', [NodeApiController::class,'adminDeleteBank']);
  Route::post('/vouchers', [NodeApiController::class,'adminCreateVoucher']);
  Route::delete('/vouchers/{id}', [NodeApiController::class,'adminDeleteVoucher']);
  Route::post('/providers', [NodeApiController::class,'adminCreateProvider']);
  Route::post('/providers/{id}/toggle', [NodeApiController::class,'adminToggleProvider']);
  Route::post('/providers/{id}/balance', [NodeApiController::class,'adminProviderBalance']);
  Route::post('/providers/{id}/sync', [NodeApiController::class,'adminProviderSync']);
  Route::post('/announcements', [NodeApiController::class,'adminCreateAnnouncement']);
  Route::delete('/announcements/{id}', [NodeApiController::class,'adminDeleteAnnouncement']);
  Route::post('/settings', [NodeApiController::class,'adminSettings']);
});

Route::post('/cron/sync-orders', [NodeApiController::class,'cronSyncOrders']);


