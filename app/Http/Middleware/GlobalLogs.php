<?php
namespace App\Http\Middleware;

use App\Models\GlobalLog;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
//if error check 82 line
class GlobalLogs
{
    private $log = [];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {



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
//        if ($request->method() != 'OPTIONS' and $response->isOk() and isset($request->segments()[1]) and  $request->segments()[1] != 'logs')
//            return $this->setLog($response , $request);

        return $next($request);
    }

    private function setLog($res, Request $req)
    {
        $this->log = [
            'fields' => \GuzzleHttp\json_encode($this->hideSecretFields($req)),
            'method' => $req->method(),
            'url' => $req->path() ,
            'description' => trans("globalLog.{$req->method()}.{$req->path()}"),
            'service' => $this->getService($req->segments()[1]),
        ];


        if (Auth::check()) {
            $this->log['user_id'] = Auth::id();
            $this->log['company_id'] =1;
        }
        else
            $this->addMissingFields($req);

//        GlobalLog::create($this->log);

        return $res;
    }

    private function getService($name)
    {
        return in_array($name , ['plaza' , 'esd' , 'static']) ? $name : 'gateway';
    }

    private function hideSecretFields(Request $req)
    {
        return $req->except(['password' ,'confirm_password' ,  'client_secret', 'secret' , 'new_password' , 'old_password' ]);
    }

    private function addMissingFields(Request $req)
    {
        if ($req->path() == "v1/oauth/token" and $req->has('username')){
            $user = User::where('email' , $req->username)->first(['id' , 'company_id']);
            $this->log['user_id'] = $user->id;
            $this->log['company_id'] = $user->company_id;
        }
        else{
            dd("SEE GLOBAL LOGS middleware");
        }
    }

    private function NotLoggingUrls(Request $req)
    {
        /**
         * todo
         * make static file protection
         * static routes have not protection or authMiddleware
         */
        return (isset($req->segments()[1]) and $req->segments()[1] == 'static') or (isset($req->segments()[2]) and $req->segments()[2] == 'attendance' and $req->method() == 'POST');
    }


}
