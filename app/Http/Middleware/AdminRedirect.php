<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminRedirect
{
    public function handle(Request $request, Closure $next)
    {
        if (
            auth()->check() &&
            auth()->user()->admin_status
        ) {
            return redirect()
                ->route('admin.attendance.index');
        }

        return $next($request);
    }
}
