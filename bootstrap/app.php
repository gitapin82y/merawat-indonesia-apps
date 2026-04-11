<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\CheckRole;
use App\Http\Middleware\CheckAuth;
use App\Http\Middleware\TripayIpMiddleware;
use App\Http\Middleware\TrustProxies;
use App\Exceptions\InvalidOrderException;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append(TrustProxies::class);
        $middleware->alias([
            'checkRole' => \App\Http\Middleware\CheckRole::class,
            'checkAuth' => \App\Http\Middleware\CheckAuth::class,
            'checkAuth' => \App\Http\Middleware\CheckAuth::class,
            'tripay.ip' => \App\Http\Middleware\TripayIpMiddleware::class,
        ]);
         $middleware->validateCsrfTokens(except: [
        'api/espay/*',
        'api/moota/*',
        'api/v1.0/transfer-va/inquiry',
        'api/v1.0/transfer-va/payment',
    ]);

    })
    ->withExceptions(function (Exceptions $exceptions) {
        // 404 - Akses ditolak
        $exceptions->render(function (NotFoundHttpException $e) {
            return response()->view('errors.404', ['exception' => $e], 404);
        });
        
        // 403 - Akses ditolak
        $exceptions->render(function (HttpException $e) {
            if ($e->getStatusCode() === 403) {
                return response()->view('errors.403', ['exception' => $e], 403);
            }
        });
        
        // 419 - Sesi kadaluarsa (CSRF token mismatch)
        $exceptions->render(function (TokenMismatchException $e) {
            return response()->view('errors.419', ['exception' => $e], 419);
        });
        
        // 429 - Terlalu banyak permintaan
        $exceptions->render(function (TooManyRequestsHttpException $e) {
            return response()->view('errors.429', ['exception' => $e], 429);
        });
        
        // 500 - Error server
        $exceptions->render(function (\Throwable $e) {
            if (!app()->environment('local') && 
                !($e instanceof NotFoundHttpException) && 
                !($e instanceof HttpException && $e->getStatusCode() < 500)) {
                return response()->view('errors.500', ['exception' => $e], 500);
            }
        });
    })->create();
