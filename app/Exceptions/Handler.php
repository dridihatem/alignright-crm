<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            // Log all exceptions with context
            $this->logException($e);
        });

        // Handle different types of exceptions
        $this->renderable(function (Throwable $e, Request $request) {
            return $this->handleException($e, $request);
        });
    }

    /**
     * Handle different types of exceptions
     */
    protected function handleException(Throwable $e, Request $request)
    {
        // Handle API requests
        if ($request->expectsJson() || $request->is('api/*')) {
            return $this->handleApiException($e, $request);
        }

        // Handle web requests
        return $this->handleWebException($e, $request);
    }

    /**
     * Handle API exceptions
     */
    protected function handleApiException(Throwable $e, Request $request)
    {
        $status = 500;
        $message = 'Internal Server Error';
        $data = [];

        if ($e instanceof ValidationException) {
            $status = 422;
            $message = 'Validation failed';
            $data = $e->errors();
        } elseif ($e instanceof AuthenticationException) {
            $status = 401;
            $message = 'Unauthenticated';
        } elseif ($e instanceof AuthorizationException) {
            $status = 403;
            $message = 'Unauthorized';
        } elseif ($e instanceof ModelNotFoundException) {
            $status = 404;
            $message = 'Resource not found';
        } elseif ($e instanceof NotFoundHttpException) {
            $status = 404;
            $message = 'Endpoint not found';
        } elseif ($e instanceof MethodNotAllowedHttpException) {
            $status = 405;
            $message = 'Method not allowed';
        } elseif ($e instanceof QueryException) {
            $status = 500;
            $message = 'Database error occurred';
            
            // Don't expose database details in production
            if (config('app.debug')) {
                $data = ['sql' => $e->getSql(), 'bindings' => $e->getBindings()];
            }
        }

        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => $data,
            'error_code' => $e->getCode(),
        ], $status);
    }

    /**
     * Handle web exceptions
     */
    protected function handleWebException(Throwable $e, Request $request)
    {
        if ($e instanceof ValidationException) {
            return redirect()->back()
                           ->withInput()
                           ->withErrors($e->errors());
        }

        if ($e instanceof AuthenticationException) {
            return redirect()->route('login')
                           ->with('error', 'Please login to access this page.');
        }

        if ($e instanceof AuthorizationException) {
            return redirect()->back()
                           ->with('error', 'You are not authorized to perform this action.');
        }

        if ($e instanceof ModelNotFoundException) {
            return redirect()->back()
                           ->with('error', 'The requested resource was not found.');
        }

        if ($e instanceof NotFoundHttpException) {
            return response()->view('errors.404', [], 404);
        }

        if ($e instanceof QueryException) {
            Log::error('Database error: ' . $e->getMessage(), [
                'sql' => $e->getSql(),
                'bindings' => $e->getBindings(),
                'url' => $request->fullUrl(),
                'user_id' => auth()->id(),
            ]);

            return redirect()->back()
                           ->with('error', 'A database error occurred. Please try again.');
        }

        // For other exceptions, show generic error page
        return response()->view('errors.500', [], 500);
    }

    /**
     * Log exception with context
     */
    protected function logException(Throwable $e)
    {
        $context = [
            'exception_class' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ];

        // Add request context if available
        if (request()) {
            $context['url'] = request()->fullUrl();
            $context['method'] = request()->method();
            $context['user_id'] = auth()->id();
            $context['user_agent'] = request()->userAgent();
            $context['ip'] = request()->ip();
        }

        // Log with appropriate level
        if ($e instanceof ValidationException || $e instanceof AuthenticationException) {
            Log::warning($e->getMessage(), $context);
        } elseif ($e instanceof AuthorizationException || $e instanceof ModelNotFoundException) {
            Log::info($e->getMessage(), $context);
        } else {
            Log::error($e->getMessage(), $context);
        }
    }

    /**
     * Determine if the exception should be reported.
     */
    public function shouldReport(Throwable $e): bool
    {
        // Don't report validation exceptions
        if ($e instanceof ValidationException) {
            return false;
        }

        // Don't report authentication exceptions
        if ($e instanceof AuthenticationException) {
            return false;
        }

        return parent::shouldReport($e);
    }
}
