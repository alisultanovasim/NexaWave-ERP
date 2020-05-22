<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\Permission;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Hr\Entities\Employee\Employee;
use Modules\Plaza\Entities\OfficeUser;

class ProfileController extends Controller
{
    use  ApiResponse;

    public function profile(Request $request)
    {
        $user = Auth::user()->load('role:id,name');
        $office = null;
        $companies = null;
        $modules = null;

        switch ($user->role_id) {

            case User::OFFICE:
                $office = OfficeUser::with(['office:id,name,image,company_id'])->where('user_id', $user->id)->first()->office;
                $companies = [
                    [
                        "company_id" => $office->company_id
                    ]
                ];
                break;

            case User::EMPLOYEE:
                $companies = Employee::with([
                    'company',
                    'contract' => function ($q) {
                        $q->where('is_active', true);
                    },
                    'contract.position',
                    'contract.position.permissions' => function ($q) {
                        $q->with(['module:id,name'])
                            ->whereHas('module', function ($q) {
                                $q->whereNull('parent_id');
                            })
                            ->whereHas('permission' , function ($q) {
                                $q->where('id', Permission::READ);
                            })
                            ->select([
                                'position_id',
                                'module_id',
                                'permission_id',
                            ]);
                    },
                ])
                    ->active()
                    ->where('user_id', Auth::id())
                    ->get();
                break;

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
            'details', 'details.nationality', 'details.citizen', 'details.birthdayCity', 'details.birthdayCountry', 'details.birthdayRegion'
        ]);
    }

    public function history()
    {
        return Auth::user()->load([
            'employment', 'employment.company', 'employment.contracts', 'employment.contracts.position'
        ]);
    }

    public function update(Request $request)
    {
        UserController::updateUser($request, Auth::id());
        return $this->successResponse('ok');
    }

}
