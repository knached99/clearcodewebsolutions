<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetClientTimezone
{
    public function handle(Request $request, Closure $next)
    {
        $timezone = $request->cookie('client_timezone');

        if (
            $timezone &&
            in_array($timezone, timezone_identifiers_list(), true)
        ) {
            config([
                'app.timezone' => $timezone,
            ]);
        }

        return $next($request);
    }
}