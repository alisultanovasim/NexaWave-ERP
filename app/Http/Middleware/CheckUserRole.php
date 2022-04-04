<?php

namespace App\Http\Middleware;


use App\Models\Role;
use App\Models\User;
use App\Models\UserRole;
use App\Providers\PermissionProvider;
use App\Traits\ApiResponse;
use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Modules\Hr\Entities\Employee\Employee;
use Modules\Plaza\Entities\OfficeUser;


class CheckUserRole
{
    use ApiResponse;

    private $userRoles;
    private $permissionProvider;
    private $companyId;
    private $request;
    private $roleModel;

    /**
     * CheckUserAccess constructor.
     * @param Request $request
     * @param Role $role
     */
    public function __construct(Request $request, Role $role)
    {
        $this->request = $request;
        if ($request->hasHeader('company_id')){
            $this->companyId = $request->header('company_id');
        } else {
            $headers = apache_request_headers();
            $this->companyId =  array_key_exists('company_id' , (array)$headers) ? $headers['company_id'] : $request->get('company_id');
        }
        $this->userRoles = UserRole::where('user_id', Auth::id())->get(['role_id', 'company_id', 'office_id']);
        $this->permissionProvider = new PermissionProvider($role, $this->companyId);
        $this->roleModel = $role;
    }

    /**
     * @param Request $request
     * @param Closure $next
     * @return mixed
     * @throws AuthorizationException
     */
    public function handle(Request $request, Closure $next){
        /*
         * When user requests as company employee (sends company_id)
         */
        if ($this->companyId){
            $this->validateUserCompanyAndMergeToRequest($this->companyId);
        }

        /*
         * When user requests as platform user
         */
        else {
//            $this->removeCompanyRolesFromUserRoleListForThisRequest();
        }

        /*
         * If user has no remain roles after filter throw exception
         */
        if (!count($this->userRoles)) {
            throw new AuthorizationException();
        }

        /*
         * Boot permissions and set user roles
         */
        $userRoles = collect($this->userRoles)->pluck('role_id')->toArray();
        \auth()->user()->setUserRolesForRequest($userRoles);
        $this->permissionProvider->boot();

        return $next($this->request);
    }


    /**
     * @param $companyId
     */
    private function validateUserCompanyAndMergeToRequest($companyId) {
        $userRolesForThisRequest = [];
        foreach ($this->userRoles as $role){
            if ($role->company_id == $companyId){

                /*
                 * if user if office user set office id instance variable
                 */
                if (Auth::user()->getAttribute('is_office_user')){
                    Auth::user()->setUserWorkingOfficeId($role['office_id']);
                }

                $userRolesForThisRequest[] = $role;
            }
        }
        $this->request->merge(['company_id' => $companyId]);
        $this->userRoles = $userRolesForThisRequest;
    }


    private function removeCompanyRolesFromUserRoleListForThisRequest(): void {
        /*
         * Remove company roles
         */
        foreach ($this->userRoles as $key => $role){
            if ($role->company_id !== null){
                unset($this->userRoles[$key]);
            }
        }
    }

}
