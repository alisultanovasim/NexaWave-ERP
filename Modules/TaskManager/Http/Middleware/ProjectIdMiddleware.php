<?php

namespace Modules\TaskManager\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ProjectIdMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $this->checkHeaders();
        return $next($request);
    }

    private function checkHeaders()
    {
        if (!\request()->header("projectId"))
            abort(400, "Project Id not provided!");
    }


}
