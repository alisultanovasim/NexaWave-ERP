<?php


namespace App\Http\Controllers;


use App\Models\CompanyRoleUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function info(Request $request)
    {
        $user = Auth::user();

//        $companyAndRoleData = CompanyRoleUser::with([
//            'role:id,name', 'company:id,name', 'modules:id,is_active,module_id,company_id', 'modules.module', 'modules.module.subModules', 'modules.module.permissionsByModule'
//        ])
//            ->where('user_id', Auth::id())
//            ->get(['role_id', 'company_id']);
//        $user->companies = $companyAndRoleData;
        return $user;
    }
}
