<?php


namespace App\Http\Middleware;


use Closure;
use Illuminate\Http\Response;

class NodeMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (trim($request->header('Authorization')) ==   env('NODE_CRON_KEY'))
            return $next($request);
        abort(Response::HTTP_FORBIDDEN , 'Forbidden');
    }
}
