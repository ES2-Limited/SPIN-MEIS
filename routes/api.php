<?php

use App\Http\Controllers\Api\V1\HealthController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/health', HealthController::class)->name('api.v1.health');
    Route::get('/openapi.json', function () {
        $spec = json_decode(file_get_contents(public_path('openapi.json')), true);

        return response()->json($spec);
    })->name('api.v1.openapi');
});
