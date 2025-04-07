<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!$request->user()) {
            return redirect('login');
        }

        $teamId = $request->route('team');

        foreach ($roles as $role) {
            if ($request->user()->hasRole($role, $teamId)) {
                return $next($request);
            }
        }

        return abort(403, 'Unauthorized action.');
    }
}
