<?php

namespace App\Http\Middleware;

use App\Traits\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CompanyIdValidator
{

    use ApiResponse;


    /**
     */
    public function handle(Request $request, Closure $next)
    {

        $company_id = $request->hasHeader('company_id')?$request->header('company_id'):$request->get('company_id');
        Validator::validate(['company_id' => $company_id] , [
            'company_id' => ['required' , 'integer']
        ]);

        //todo check if user in this company
        $request->request->set('company_id' , $company_id);
//        $inThisCompany = CompanyRoleUser::where('company_id' ,$company_id)
//            ->where('user_id' , Auth::id())
//            ->exists();
//        if ($inThisCompany) return $next($request);
        return $next($request);
//        return $this->errorMessage(['error' => trans('response.notYouCompany')] , 400);

    }
}
