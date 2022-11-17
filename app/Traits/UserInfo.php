<?php
namespace App\Traits;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Modules\Hr\Entities\Employee\Employee;

trait UserInfo
{
    public function getUserRoles()
    {
        return User::query()
            ->where('id',Auth::id())
            ->with('roles')
            ->first();
    }

    function getEmployeeId($companyId){
        return Employee::query()
            ->where([
                'user_id'=>Auth::id(),
                'company_id'=>$companyId
            ])
            ->first()['id'];
    }
}
