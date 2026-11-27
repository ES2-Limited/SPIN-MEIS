<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Scheme;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class SchemeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Scheme::class);

        $schemes = Scheme::query()->orderBy('id')->get();

        return response()->json([
            'data' => $schemes,
            'correlation_id' => $request->attributes->get('correlation_id'),
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $scheme = Scheme::withoutGlobalScopes()->findOrFail($id);

        Gate::authorize('viewRecord', $scheme);

        return response()->json([
            'data' => $scheme,
            'correlation_id' => $request->attributes->get('correlation_id'),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        Gate::authorize('createRecord', Scheme::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:32', 'unique:schemes,code'],
            'state_id' => ['required', 'integer', 'exists:states,id'],
            'rbda_id' => ['required', 'integer', 'exists:rbdas,id'],
            'delivery_model' => ['required', 'in:model_1,model_2'],
        ]);

        $scheme = Scheme::create($validated);

        return response()->json([
            'data' => $scheme,
            'correlation_id' => $request->attributes->get('correlation_id'),
        ], 201);
    }
}
