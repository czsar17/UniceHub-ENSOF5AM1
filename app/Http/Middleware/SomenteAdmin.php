<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// barra quem nao e admin (antes isso ficava repetido em cada metodo)
class SomenteAdmin
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::user()?->is_admin) {
            abort(403);
        }

        return $next($request);
    }
}
