<?php


namespace Modules\Esd\Http\Middleware;


use Closure;
use Illuminate\Http\Response;

class accessToken
{
    /**
     * Handle an incoming request.
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
//        $validSecrets = explode(',', env('ACCEPTED_SECRETS'));
//        if (in_array($request->header('Authorization'), $validSecrets)) {
            return $next($request);
//        }
//        return abort(Response::HTTP_UNAUTHORIZED);
    }
}


