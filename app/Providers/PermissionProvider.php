<?php

namespace App\Providers;

use App\Models\Module;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Modules\Hr\Entities\Employee\Employee;


class PermissionProvider
{

    private $userCompanyId;

    /**
     * Boot Provider after validation company id
     * Example usage $this->authorize('permissionName-moduleName')   ( $this->authorize('edit-users') )
     * @param Role $role
     */
    public function boot(Role $role)
    {
        $this->setUserCompanyId(request()->get('company_id'));

        Gate::before(function (User $user, $ability) use ($role) {
            if ($user->getAttribute('role_id') == $role->getSuperAdminId()) {
                return true;
            }
        });

        foreach ($this->companyGetRoleModulePermissions() as $permission){
            $gateName = $permission->permission_name . '-' . $permission->module_name;
            Gate::define($gateName, function (User $user) use ($permission, $role){
                return $this->companyPositionHasPermission($permission->position_id, $user, $role);
            });
        }
    }


    /**
     * @param $positionId
     * @param User $user
     * @param Role $role
     * @return bool
     */
    private function companyPositionHasPermission($positionId, User $user, Role $role): bool {
        //company admin has all permissions for subscribed modules
        if ($user->getAttribute('role_id') == $role->getCompanyAdminId())
            return true;
        //logic for other users
        return Employee::join('employee_contracts', 'employee_contracts.employee_id', 'employees.id')
        ->where([
            'employees.company_id' => $this->userCompanyId,
            'employees.user_id' => $user->getKey(),
            'employee_contracts.position_id' => $positionId
        ])
        ->exists();
    }

    /**
     * @return Collection
     */
    private function companyGetRoleModulePermissions(): Collection {
        return Module::where('modules.is_active', 1)
            ->hasCompany($this->userCompanyId)
            ->join('position_module_permissions', 'position_module_permissions.module_id', 'modules.id')
            ->join('permissions', 'permissions.id', 'position_module_permissions.permission_id')
            ->select([
                'modules.name as module_name',
                'permissions.name as permission_name',
                'position_module_permissions.position_id as position_id'
            ])
            ->get();
    }

    /**
     * @param mixed $userCompanyId
     * @return PermissionProvider
     */
    public function setUserCompanyId($userCompanyId): PermissionProvider {
        $this->userCompanyId = $userCompanyId;
        return $this;
    }
}
