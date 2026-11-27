<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HealthController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $dbStatus = 'ok';
        try {
            DB::connection()->getPdo();
        } catch (\Throwable) {
            $dbStatus = 'error';
        }

        return response()->json([
            'status' => $dbStatus === 'ok' ? 'ok' : 'degraded',
            'service' => 'SPIN MEIS 2.0',
            'version' => '1.0.0',
            'database' => $dbStatus,
            'timestamp' => now()->toIso8601String(),
            'correlation_id' => $request->attributes->get('correlation_id'),
        ]);
    }
}
