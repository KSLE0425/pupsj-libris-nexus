<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'kiosk.auth' => \App\Http\Middleware\KioskAuth::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        $exceptions->render(function (AuthenticationException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Unauthenticated.',
                ], 401);
            }

            if ($request->is('admin') || $request->is('admin/*')) {
                return redirect()->guest(route('admin.login'));
            }

            if ($request->is('faculty') || $request->is('faculty/*')) {
                return redirect()->guest(route('faculty.login'));
            }

            if ($request->is('student') || $request->is('student/*')) {
                return redirect()->guest(route('student.login'));
            }

            return redirect()->guest(route('login'));
        });

        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, $request) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'Your session has expired. Please refresh the page and try again.',
                    'errors'  => [
                        'session' => ['Your session has expired. Please refresh the page and try again.'],
                    ],
                ], 419);
            }

            return redirect()->back()
                ->withInput($request->except(['password', 'password_confirmation', '_token']))
                ->withErrors(['session' => 'Your session has expired. Please try submitting the form again.']);
        });

    })->create();
