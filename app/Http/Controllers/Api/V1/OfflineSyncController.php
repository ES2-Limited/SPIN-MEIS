<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\ApprovalWorkflowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OfflineSyncController extends Controller
{
    public function __invoke(Request $request, ApprovalWorkflowService $workflow): JsonResponse
    {
        $validated = $request->validate([
            'indicator_id' => ['required', 'integer', 'exists:indicators,id'],
            'scheme_id' => ['nullable', 'integer', 'exists:schemes,id'],
            'owner_type' => ['required', 'in:national,rbda,state'],
            'owner_id' => ['nullable', 'integer'],
            'year' => ['required', 'integer', 'min:2025', 'max:2035'],
            'quarter' => ['required', 'integer', 'min:1', 'max:4'],
            'numerator' => ['nullable', 'numeric'],
            'denominator' => ['nullable', 'numeric'],
            'value' => ['nullable', 'numeric'],
            'comment' => ['nullable', 'string'],
            'client_generated_id' => ['required', 'string', 'max:64'],
        ]);

        $result = $workflow->syncOffline($validated, $request->user());

        $statusCode = $result['status'] === 'rejected_locked' ? 409 : 200;

        return response()->json([
            'sync_status' => $result['status'],
            'data' => $result['record'],
            'correlation_id' => $request->attributes->get('correlation_id'),
        ], $statusCode);
    }
}
