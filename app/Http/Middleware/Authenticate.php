<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    protected function redirectTo(Request $request): ?string
{
    // Jika request API → JANGAN REDIRECT
    if ($request->expectsJson() || $request->is('api/*')) {
        return null;
    }

    // Kalau bukan API → redirect login web
    return route('login');
}

}
