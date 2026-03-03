<?php

namespace App\Exceptions;

use Throwable;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Response;

// custom business exceptions
use App\Exceptions\BusinessException;
use App\Exceptions\NotFoundException as AppNotFoundException;
use App\Exceptions\ValidationException as AppValidationException;
use App\Traits\HttpResponses;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    use HttpResponses;

    /**
     * Inputs that are never flashed for validation exceptions.
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register exception reporting.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            try {
                Log::error('Uncaught exception: ' . get_class($e) . ' - ' . $e->getMessage(), [
                    'exception' => $e,
                ]);
            } catch (Throwable $ex) {
                // Prevent logging failure from crashing app
            }
        });
    }

    /**
     * Render exception into JSON response for API.
     */
    public function render($request, Throwable $e)
    {
        // Force JSON for API routes
        if ($request->expectsJson() || $request->is('api/*')) {

            // 422 - Validation
            if ($e instanceof ValidationException) {
                return $this->error(
                    'Validation error',
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                    $e->errors()
                );
            }

            // 401 - Unauthenticated
            if ($e instanceof AuthenticationException) {
                return $this->error(
                    'Unauthenticated',
                    Response::HTTP_UNAUTHORIZED
                );
            }

            // 403 - Forbidden
            if ($e instanceof AuthorizationException) {
                return $this->error(
                    'Forbidden',
                    Response::HTTP_FORBIDDEN
                );
            }

            // 404 - Model not found (Eloquent)
            if ($e instanceof ModelNotFoundException) {
                return $this->error(
                    'Resource not found',
                    Response::HTTP_NOT_FOUND
                );
            }

            // 404 - Business not found exception
            if ($e instanceof AppNotFoundException) {
                return $this->error(
                    $e->getMessage() ?: 'Resource not found',
                    Response::HTTP_NOT_FOUND
                );
            }

            // 404 - Route not found
            if ($e instanceof NotFoundHttpException) {
                return $this->error(
                    'Route not found',
                    Response::HTTP_NOT_FOUND
                );
            }

            // 500 - Database error
            if ($e instanceof QueryException) {

                Log::error('Database error', [
                    'sql' => $e->getSql(),
                    'bindings' => $e->getBindings(),
                    'message' => $e->getMessage(),
                ]);

                return $this->error(
                    'Database error',
                    Response::HTTP_INTERNAL_SERVER_ERROR
                );
            }

            // 400 - Business rule violation
            if ($e instanceof BusinessException) {
                return $this->error(
                    $e->getMessage(),
                    Response::HTTP_BAD_REQUEST
                );
            }

            // 422 - custom validation exception (from services)
            if ($e instanceof AppValidationException) {
                return $this->error(
                    $e->getMessage() ?: 'Validation error',
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                    $e->getErrors()
                );
            }

            // Other HTTP exceptions (405, 429...)
            if ($e instanceof HttpExceptionInterface) {
                return $this->error(
                    $e->getMessage() ?: Response::$statusTexts[$e->getStatusCode()],
                    $e->getStatusCode()
                );
            }

            // Fallback - 500
            return $this->error(
                config('app.debug') ? $e->getMessage() : 'Server error',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return parent::render($request, $e);
    }
}
