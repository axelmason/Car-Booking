<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Admin
{
    public function handle(Request $request, Closure $next)
    {
        if(auth()->check()) {
            if(auth()->user()->login == 'admin') {
                return $next($request);
            }
        }
        return to_route('index');
    }
}
