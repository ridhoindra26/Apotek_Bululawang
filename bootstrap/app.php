<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

use App\Http\Middleware\EnforceSingleSession;
use App\Http\Middleware\CheckRole;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'single.session' => EnforceSingleSession::class,
            'role' => CheckRole::class,
        ]);

        // $middleware->append(EnforceSingleSession::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (TokenMismatchException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Sesi sudah berakhir. Silakan refresh halaman dan coba lagi.',
                ], 419);
            }

            return redirect()
                ->route('login')
                ->with('error', 'Sesi sudah berakhir. Silakan login kembali.');
        });

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Silakan login terlebih dahulu.',
                ], 401);
            }

            return redirect()
                ->route('login')
                ->with('error', 'Silakan login terlebih dahulu.');
        });

        $exceptions->render(function (AuthorizationException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Anda tidak memiliki akses untuk melakukan tindakan ini.',
                ], 403);
            }

            return response()
                ->view('errors.403', [
                    'message' => 'Anda tidak memiliki akses untuk membuka halaman ini.',
                ], 403);
        });

        $exceptions->render(function (ModelNotFoundException|NotFoundHttpException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Data atau halaman tidak ditemukan.',
                ], 404);
            }

            return response()
                ->view('errors.404', [
                    'message' => 'Data atau halaman yang Anda cari tidak ditemukan.',
                ], 404);
        });

        $exceptions->render(function (HttpException $e, Request $request) {
            $status = $e->getStatusCode();

            if ($status === 403 || $status === 404 || $status === 419) {
                return null;
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Terjadi kesalahan pada permintaan.',
                ], $status);
            }

            return response()
                ->view('errors.generic', [
                    'status' => $status,
                    'message' => 'Terjadi kesalahan pada permintaan.',
                ], $status);
        });

        $exceptions->render(function (Throwable $e, Request $request) {
            if (app()->environment('local')) {
                return null;
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Terjadi kesalahan pada server. Silakan coba lagi.',
                ], 500);
            }

            return response()
                ->view('errors.500', [
                    'message' => 'Terjadi kesalahan pada server. Silakan coba lagi.',
                ], 500);
        });
    })->create();
