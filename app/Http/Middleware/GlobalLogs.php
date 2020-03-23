<?php

namespace App\Http\Middleware;

use App\Models\GlobalLog;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class GlobalLogs
{
    private $log = [];

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        //todo
        //except login routes
//        $response = $next($request);

        /**
         * sometimes we as a programmers test routes
         * but we dont want to log this action
         * send no_log params as 1
         */
        if ($request->no_log == 1)
            return $next($request);


        if ($this->NotLoggingUrls($request))
            return $next($request);

        /**
         * OPTIONS , failed requests and logs requests dont need logs
         */
//        if ($request->method() != 'OPTIONS' and $response->isSuccessful() and isset($request->segments()[1]) and $request->segments()[1] != 'logs')
//            return $this->setLog($response, $request);

        return $next($request);
    }

    private function setLog($res, Request $req)
    {
        if (!Auth::check()) return $res;

        else
            $this->log = [
                'user_id' => Auth::id(),
                'company_id' => $req->get('company_id')
            ];

        $this->log = [
            'fields' => \GuzzleHttp\json_encode($this->hideSecretFields($req)),
            'method' => $req->method(),
            'url' => $req->path(),
            'description' => trans("globalLog.{$req->method()}.{$req->path()}"),
            'service' => $this->getService($req->segments()[1]),
        ];

        GlobalLog::create($this->log);

        return $res;
    }

    private function getService($name)
    {
        return in_array($name, ['plaza', 'esd', 'static']) ? $name : 'gateway';
    }

    private function hideSecretFields(Request $req)
    {
        return $req->except(['password', 'confirm_password', 'client_secret', 'secret', 'new_password', 'old_password']);
    }

    private function addMissingFields(Request $req)
    {
        //
    }

    private function NotLoggingUrls(Request $req)
    {
        /**
         * todo
         * make static file protection
         * static routes have not protection or authMiddleware
         */
        return (isset($req->segments()[2]) and $req->segments()[2] == 'attendance' and $req->method() == 'POST');
    }


}
