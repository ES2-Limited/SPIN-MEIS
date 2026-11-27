<?php

use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\SchemeController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/health', HealthController::class)->name('api.v1.health');
    Route::get('/openapi.json', function () {
        $spec = json_decode(file_get_contents(public_path('openapi.json')), true);

        return response()->json($spec);
    })->name('api.v1.openapi');

    Route::middleware('auth:web')->group(function () {
        Route::get('/schemes', [SchemeController::class, 'index'])->name('api.v1.schemes.index');
        Route::get('/schemes/{id}', [SchemeController::class, 'show'])->name('api.v1.schemes.show');
        Route::post('/schemes', [SchemeController::class, 'store'])->name('api.v1.schemes.store');
    });
});
