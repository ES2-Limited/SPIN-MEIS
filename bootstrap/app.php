<?php

use App\Http\Middleware\CorrelationIdMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append(CorrelationIdMiddleware::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (Throwable $e, Request $request) {
            if (! $request->is('api/*') && ! $request->expectsJson()) {
                return null;
            }

            $correlationId = $request->attributes->get('correlation_id');

            if ($e instanceof ValidationException) {
                return response()->json([
                    'code' => 'VALIDATION_ERROR',
                    'message' => $e->getMessage(),
                    'field_errors' => $e->errors(),
                    'correlation_id' => $correlationId,
                ], 422);
            }

            $status = $e instanceof HttpExceptionInterface ? $e->getStatusCode() : 500;
            $code = match ($status) {
                401 => 'UNAUTHORIZED',
                403 => 'FORBIDDEN',
                404 => 'NOT_FOUND',
                429 => 'RATE_LIMITED',
                default => $status >= 500 ? 'INTERNAL_ERROR' : 'HTTP_ERROR',
            };

            return response()->json([
                'code' => $code,
                'message' => $status >= 500 && ! config('app.debug') ? 'An unexpected error occurred.' : $e->getMessage(),
                'field_errors' => (object) [],
                'correlation_id' => $correlationId,
            ], $status);
        });
    })->create();
