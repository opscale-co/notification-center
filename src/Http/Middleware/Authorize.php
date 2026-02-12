<?php

namespace Opscale\NotificationCenter\Http\Middleware;

use Illuminate\Http\Request;
use Laravel\Nova\Nova;
use Opscale\NotificationCenter\Tool;

class Authorize
{
    public function handle(Request $request, $next)
    {
        $tool = collect(Nova::registeredTools())->first([$this, 'matchesTool']);

        return optional($tool)->authorize($request) ? $next($request) : abort(403);
    }

    public function matchesTool($tool)
    {
        return $tool instanceof Tool;
    }
}
