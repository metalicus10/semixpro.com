<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @param  string  $role
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // Проверка авторизации
        if (!Auth::check()) {
            return redirect('login');
        }

        // Проверка роли пользователя
        if (!Auth::user()->inRole($role)) {
            return abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}
