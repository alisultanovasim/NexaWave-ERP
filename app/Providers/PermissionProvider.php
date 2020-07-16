<?php

namespace App\Providers;

use App\Models\Module;
use App\Models\Role;
use App\Models\RoleModulePermission;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Modules\Hr\Entities\Employee\Employee;


class PermissionProvider
{

    private $userCompanyId;
    private $roleModel;


    /**
     * PermissionProvider constructor.
     * @param Role $role
     * @param $userCompanyId
     */
    public function __construct(Role $role, $userCompanyId)
    {
        $this->roleModel = $role;
        $this->userCompanyId = $userCompanyId;
    }

    /**
     * Boot Provider after validation company id
     * Example usage $this->authorize('permissionName-moduleName')   ( $this->authorize('edit-users') )
     * @param array $userRoles
     */
    public function boot(array $userRoles)
    {
        /*
         * Give all access to super admin
         */
        Gate::before(function (User $user, $ability) use ($userRoles) {
            if (in_array($this->roleModel->getSuperAdminRoleId(), $userRoles)) {
                return true;
            }
        });


        /*
         * Define gates for remain roles
         */
        foreach ($this->getRoleModulePermissions() as $permission) {
//            $abilityName = $permission->permission_name . '-' . $permission->module_name;
            $abilityName = $permission->permission_slug . '-' . $permission->module_name;
            Gate::define($abilityName, function (User $user) use ($userRoles, $permission){
                return $this->userHasAccess(
                    $userRoles,
                    $permission->module_id,
                    $permission->is_office_module,
                    $permission->permission_id
                );
            });
        }

    }


    /**
     * @param array $userRoles
     * @param $moduleId
     * @param $isOfficeModule
     * @param $permissionId
     * @return bool
     */
    private function userHasAccess(array $userRoles, $moduleId, $isOfficeModule, $permissionId): bool {

        /*
         * Company admin has permission to all subscribed modules
         */
        if (in_array($this->roleModel->getCompanyAdminRoleId(), $userRoles) and !$isOfficeModule)
            return true;

        /*
         * Office admin has permission to all subscribed modules
         */
        if (in_array($this->roleModel->getOfficeAdminRoleId(), $userRoles) and $isOfficeModule)
            return true;

        /*
         * Check non user access
         */
        return RoleModulePermission::where([
            'permission_id' => $permissionId,
            'module_id' => $moduleId,
        ])
        ->whereIn('role_id', $userRoles)
        ->exists();
    }

    /**
     * @return Collection
     */
    private function getRoleModulePermissions(): Collection {

        $permissions =  Module::where('modules.is_active', 1)
        ->join('permissions', function ($join){
            $join->where('permissions.is_active', 1);
        });

        /*
         * if user requests as company user get modules where company subscribed
         */
        if ($this->userCompanyId) {
            $permissions = $permissions->join('company_modules', function ($join){
                $join->on('company_modules.module_id', 'modules.id');
                $join->where('company_modules.is_active', 1);
            });
        }

        $permissions = $permissions->get([
            'modules.name as module_name',
            'modules.is_office_module as is_office_module',
            'permissions.name as permission_name',
            'permissions.slug as permission_slug',
            'modules.id as module_id',
            'permissions.id as permission_id'
        ]);

        return $permissions;
    }
}
