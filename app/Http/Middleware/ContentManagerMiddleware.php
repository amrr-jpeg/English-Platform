<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ContentManagerMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$user->isContentManager()) {
            abort(403, 'Доступ только для контент-менеджеров.');
        }

        return $next($request);
    }
}
