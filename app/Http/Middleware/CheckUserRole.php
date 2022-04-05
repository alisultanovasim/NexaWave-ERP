<?php

namespace App\Http\Middleware;


use App\Models\Role;
use App\Models\UserRole;
use App\Providers\PermissionProvider;
use App\Traits\ApiResponse;
use Closure;
use Illuminate\Auth\Access\AuthorizationException;
//use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Request;

class CheckUserRole
{
    use ApiResponse;

    private $userRoles;
    private $permissionProvider;
    private $companyId;
    private $request;
    private $roleModel;

    private function apache_request_headers()
    {
        $arh = array();
        $rx_http = '/\AHTTP_/';

        foreach ($_SERVER as $key => $val) {
            if (preg_match($rx_http, $key)) {
                $arh_key = preg_replace($rx_http, '', $key);
                $rx_matches = array();
                // do some nasty string manipulations to restore the original letter case
                // this should work in most cases
                $rx_matches = explode('_', $arh_key);

                if (count($rx_matches) > 0 and strlen($arh_key) > 2) {
                    foreach ($rx_matches as $ak_key => $ak_val) {
                        $rx_matches[$ak_key] = ucfirst($ak_val);
                    }

                    $arh_key = implode('-', $rx_matches);
                }

                $arh[$arh_key] = $val;
            }
        }

        return ($arh);
    }

    /**
     * CheckUserAccess constructor.
     * @param Request $request
     * @param Role $role
     */
    public function __construct(Request $request, Role $role)
    {
        echo "<pre>";
        print_r($request->headers());
        echo "</pre>";
        exit;
        $this->request = $request;
        if ($request->hasHeader('cid')) {
            $this->companyId = $request->header('cid');
        } else {
            $headers = apache_request_headers();
            $this->companyId = array_key_exists('cid', (array)$headers) ? $request->header('cid') : $request->get('cid');
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
    public function handle(Request $request, Closure $next)
    {
        /*
         * When user requests as company employee (sends company_id)
         */
        if ($this->companyId) {
            $this->validateUserCompanyAndMergeToRequest($this->companyId);
        } /*
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
    private function validateUserCompanyAndMergeToRequest($companyId)
    {
        $userRolesForThisRequest = [];
        foreach ($this->userRoles as $role) {
            if ($role->company_id == $companyId) {

                /*
                 * if user if office user set office id instance variable
                 */
                if (Auth::user()->getAttribute('is_office_user')) {
                    Auth::user()->setUserWorkingOfficeId($role['office_id']);
                }

                $userRolesForThisRequest[] = $role;
            }
        }
        $this->request->merge(['company_id' => $companyId]);
        $this->userRoles = $userRolesForThisRequest;
    }


    private function removeCompanyRolesFromUserRoleListForThisRequest(): void
    {
        /*
         * Remove company roles
         */
        foreach ($this->userRoles as $key => $role) {
            if ($role->company_id !== null) {
                unset($this->userRoles[$key]);
            }
        }
    }

}
