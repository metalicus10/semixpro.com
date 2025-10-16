<?php

use App\Http\Middleware\RoleMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Session\TokenMismatchException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\NoStoreForAuthenticated::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (TokenMismatchException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Session expired. Please reload the page.',
                ], 419); // НЕТ такой константы — ставим число
            }

            // Обычный запрос — возвращаемся назад с флеш-сообщением
            return redirect()
                ->back()
                ->withInput($request->except('_token'))
                ->with('error', 'Сессия истекла. Обновите страницу и отправьте форму снова.');
        });
    })->create();
