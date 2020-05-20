<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Traits\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Modules\Hr\Entities\Employee\Employee;
use Modules\Plaza\Entities\OfficeUser;

class CompanyIdValidator
{

    use ApiResponse;

    private $company;

    public function handle(Request $request, Closure $next)
    {
        switch (Auth::user()->role_id){
            case User::OFFICE:
                    $err =  $this->office();
                break;

            case User::EMPLOYEE:
                    $err = $this->employee($request);
                break;
            default:
                $err = $this->errorResponse(trans('response.unAvailable') , 503);
                break;

        }

        if ($err) return $err;

        $request->request->set('company_id' , $this->company);

        return $next($request);

    }

    private function office()
    {
        $user = OfficeUser::with(['office:id,company_id'])->where('user_id' , Auth::id())->first(['id' , 'office_id']);

        if (!$user) return $this->errorResponse(trans('notFound') , 404);

        $this->company = $user->office->company_id;

        return null;
    }

    private function employee(Request $request)
    {
        if ($request->hasHeader('company_id')){
            $company_id = $request->header('company_id');
        }else{
            $headers = apache_request_headers();
            $company_id =  array_key_exists('company_id' , (array)$headers) ? $headers['company_id'] : $request->get('company_id');
        }

        Validator::validate(['company_id' => $company_id] , [
            'company_id' => ['required' , 'integer']
        ]);

        $inThisCompany = Employee::where('company_id' , $company_id)
            ->where('user_id' , Auth::id())
            ->first(['id']);

        if (!$inThisCompany) return $this->errorMessage(['error' => trans('response.notYouCompany')] , 400);

        $this->company = $company_id;
        $this->auth_employee_id = $inThisCompany->id;


        return null;

    }
}
