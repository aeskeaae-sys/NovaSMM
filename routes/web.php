<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\NodeApiController;

Route::post('/schedule/sync-orders', [NodeApiController::class,'cronSyncOrders']);
Route::get('/schedule/{any?}', fn() => response()->json(['status'=>501,'message'=>'Use /api/cron/sync-orders with X-Cron-Secret'],501))->where('any','.*');
Route::any('/arts/{any?}', fn() => response()->json(['status'=>410,'message'=>'Disabled for security'],410))->where('any','.*');

Route::get('/{any?}', function () {
    $index = public_path('app/index.html');
    if (file_exists($index)) {
        return response()->file($index);
    }
    return response('NovaSMM React build missing. Run npm run build:react', 503);
})->where('any', '^(?!api|sanctum|storage|build|assets|vendor|_debugbar|app).*$');
