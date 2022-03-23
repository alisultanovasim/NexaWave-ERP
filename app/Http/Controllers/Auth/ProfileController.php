<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Module;
use App\Models\Role;
use App\Models\User;
use App\Models\UserRole;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Modules\Hr\Entities\Employee\Employee;
use Modules\Hr\Entities\Employee\UserDetail;
use Modules\Plaza\Entities\Office;

class ProfileController extends Controller
{
    use  ApiResponse;

    public function getCurrentLogin()
    {
        $user = User::query()
            ->with(['details'])
            ->where('id', Auth::id())
            ->first();
        $employee = Employee::query()->where('user_id', Auth::id())->first();
        $company = Company::query()->where('owner_id', $employee->id)->get();
        return $this->dataResponse([
            'user' => $user,
            'companies' => $company
        ]);
    }

    public function profile(Request $request, Role $role)
    {
        $user = Auth::user();
        $office = null;
        $companies = null;
        $modules = null;

        if ($user->getAttribute('is_office_user')) {
            $userRole = UserRole::where('user_id', Auth::id())
                ->where('company_id', $request->get('company_id'))
                ->whereNotNull('office_id')
                ->first(['office_id']);
            if (!$userRole)
                return $this->errorResponse(trans('response.logicError'), 400);
            $office = Office::where('id', $userRole->office_id)
                ->first();
        }

        if ($request->get('company_id')) {
            $companies = Employee::where('user_id', Auth::id())
                ->active()
                ->with([
                    'company',
                    'contracts' => function ($q) {
                        $q->where('is_active', true);
                        $q->select(['id', 'position_id', 'employee_id']);
                    },
                    'contracts.position'
                ])
                ->get();
        }

        $modules = (new Module())
            ->where('parent_id', null)
            ->with([
                'permissionList' => function ($query) {
                    $query->whereIn('role_module_permissions.role_id', \auth()->user()->getUserRolesForRequest());
                    $query->select([
                        'permissions.id',
                        'permissions.name',
                    ]);
                }
            ]);


        if ($request->get('company_id')) {
            $modules = $modules->hasCompany($request->get('company_id'));
            if (!Auth::user()->getAttribute('is_office_user'))
                $modules = $modules->where('is_office_module', 0);
            else
                $modules = $modules->where('is_office_module', 1);
        }

        $modules = $modules
            ->orderBy('position_id')
            ->get([
                'id',
                'name',
                'icon',
                'route'
            ]);

        if (
            (in_array($role->getCompanyAdminRoleId(), \auth()->user()->getUserRolesForRequest())) or
            (in_array($role->getSuperAdminRoleId(), \auth()->user()->getUserRolesForRequest())) or
            (in_array($role->getOfficeAdminRoleId(), \auth()->user()->getUserRolesForRequest()))
        ) {
            foreach ($modules as $key => $module) {
                $modules[$key]['permission_list'] = ['*'];
                unset($module['permissionList']);
            }
        }

        return $this->dataResponse([
            'user' => $user,
            'companies' => $companies,
            'modules' => $modules,
            'office' => $office
        ]);

    }

    public function index(Request $request)
    {
        return Auth::user()->load([
            'details',
            'details.nationality',
            'details.citizen',
            'details.birthdayCity',
            'details.birthdayCountry',
            'details.birthdayRegion'
        ]);
    }

    public function history()
    {
        return Auth::user()->load([
            'employment',
            'employment.company',
            'employment.contracts',
            'employment.contracts.position'
        ]);
    }

    public function update(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:255',
            'surname' => 'required|max:255',
            'avatar' => ['nullable', 'mimes:png,jpg,jpeg'],
        ]);
        $userUpdateData = $request->only('name', 'surname');
        $userDetailUpdateData = [];
        if ($request->hasFile('avatar')) {
            $id = Auth::id();
            $name = md5($id . time()) . '.' . $request->file('avatar')->getClientOriginalExtension();
            Storage::disk('public')->putFileAs('users', $request->file('avatar'), $name);
            $userDetailUpdateData['avatar'] = $name;
        }
        User::where('id', Auth::id())->update($userUpdateData);
        if (count($userDetailUpdateData)) {
            UserDetail::where('user_id', Auth::id())->update($userDetailUpdateData);
        }
        return $this->successResponse('ok');
    }
}

