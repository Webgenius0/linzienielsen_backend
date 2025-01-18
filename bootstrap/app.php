<?php

use App\Helpers\Helper;
use App\Http\Middleware\EnsureGuestJwt;
use App\Http\Middleware\HelperMiddleware;
use App\Http\Middleware\IsVerifyed;
use App\Http\Middleware\UserMiddleware;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\ControllerDoesNotReturnResponseException;
use Symfony\Component\HttpKernel\Exception\GoneHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\InvalidMetadataException;
use Symfony\Component\HttpKernel\Exception\LengthRequiredHttpException;
use Symfony\Component\HttpKernel\Exception\LockedHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NearMissValueResolverException;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\PreconditionFailedHttpException;
use Symfony\Component\HttpKernel\Exception\PreconditionRequiredHttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\HttpKernel\Exception\UnexpectedSessionUsageException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',

        then: function () {
            // api
            Route::middleware([])
                ->prefix('api/auth')
                ->name('api.auth.')
                ->group(base_path('routes/api/auth.php'));
        }
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'guest.api' => EnsureGuestJwt::class,
            'helper' => HelperMiddleware::class,
            'user' => UserMiddleware::class,
            'verified' => IsVerifyed::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->is('api/*')) {
                // Log::info('Exception Type: ' . get_class($e));
                if ($e instanceof BadRequestHttpException) {
                    return Helper::error(400, 'Bad Request', $e->getMessage());
                } else if ($e instanceof InvalidMetadataException) {
                    return Helper::error(400, 'Invalid Metadata', $e->getMessage());
                } else if ($e instanceof NearMissValueResolverException) {
                    return Helper::error(400, 'Invalid Near Miss Value', $e->getMessage());
                } else if ($e instanceof UnexpectedSessionUsageException) {
                    return Helper::error(400, 'Unexpected Session Usage', $e->getMessage());
                } else if ($e instanceof AuthenticationException) {
                    return Helper::error(401, 'Unauthorized', $e->getMessage());
                } else if ($e instanceof AuthorizationException) {
                    return Helper::error(403, 'Forbidden', $e->getMessage());
                } else if ($e instanceof AccessDeniedHttpException) {
                    return Helper::error(403, 'Forbidden', $e->getMessage());
                } else if ($e instanceof ModelNotFoundException) {
                    return Helper::error(404, 'Not Found', $e->getMessage());
                } else if ($e instanceof NotFoundHttpException) {
                    return Helper::error(404, 'Not Found', $e->getMessage());
                } else if ($e instanceof MethodNotAllowedHttpException) {
                    return Helper::error(405, 'Method Not Allowed', $e->getMessage());
                } else if ($e instanceof NotAcceptableHttpException) {
                    return Helper::error(406, 'Not Acceptable', $e->getMessage());
                } else if ($e instanceof ConflictHttpException) {
                    return Helper::error(409, 'Conflict', $e->getMessage());
                } else if ($e instanceof GoneHttpException) {
                    return Helper::error(410, 'Resource Permanently Removed', $e->getMessage());
                } else if ($e instanceof LengthRequiredHttpException) {
                    return Helper::error(411, 'Length Required', $e->getMessage());
                } else if ($e instanceof PreconditionFailedHttpException) {
                    return Helper::error(412, 'Precondition Failed', $e->getMessage());
                } else if ($e instanceof UnsupportedMediaTypeHttpException) {
                    return Helper::error(415, 'Unsupported Media Type', $e->getMessage());
                } else if ($e instanceof UnprocessableEntityHttpException) {
                    return Helper::error(422, 'Unprocessable Entity', $e->getMessage());
                } else if ($e instanceof LockedHttpException) {
                    return Helper::error(423, 'Locked', $e->getMessage());
                } else if ($e instanceof PreconditionRequiredHttpException) {
                    return Helper::error(428, 'Precondition Required', $e->getMessage());
                } else if ($e instanceof TooManyRequestsHttpException) {
                    return Helper::error(429, 'Too Many Requests', $e->getMessage());
                } else if ($e instanceof QueryException) {
                    return Helper::error(500, 'Server Error', $e->getMessage());
                } else if ($e instanceof BindingResolutionException) {
                    return Helper::error(500, 'Server Error', $e->getMessage());
                } else if ($e instanceof HttpExceptionInterface) {
                    return Helper::error(500, 'HTTP Exception Interface', $e->getMessage());
                } else if ($e instanceof ControllerDoesNotReturnResponseException) {
                    return Helper::error(500, 'Server Error', $e->getMessage());
                } else if ($e instanceof ServiceUnavailableHttpException) {
                    return Helper::error(503, 'Service Unavailable', $e->getMessage());
                } else if (!$e instanceof ValidationException) {
                    return Helper::error($e->getCode() ?? 500, 'Error', $e->getMessage());
                }  
            }
        });
    })->create();
