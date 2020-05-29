<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\Permission;
use App\Models\PositionModulePermission;
use App\Models\Role;
use App\Models\RoleModulePermission;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Modules\Hr\Entities\Positions;
use PhpParser\Node\Expr\AssignOp\Mod;
use function Deployer\add;

class PermissionController extends Controller
{
    use ApiResponse;

    private $role;

    /**
     * PermissionController constructor.
     * @param Role $role
     */
    public function __construct(Role $role)
    {
        $this->role = $role;
    }

    /**
     * @param Request $request
     * @param $moduleId
     * @return JsonResponse
     */
    public function userGetPermissionsByModuleId(Request $request, $moduleId): JsonResponse {
        $modules = Module::where('parent_id', $moduleId)->with('subModuleIds:id,parent_id');
        if ($request->get('company_id'))
            $modules = $modules->hasCompany($request->get('company_id'));
        $modules = $modules->get(['id']);
        $moduleIds = $this->convertNestedModulesToModelsArray($modules);
        $modules = Module::whereIn('id', $moduleIds)
        ->with([
            'permissionList' => function ($query) use ($moduleIds){
                $query->whereIn('role_module_permissions.role_id', \auth()->user()->getUserRolesForRequest());
                $query->whereIn('role_module_permissions.module_id', $moduleIds);
                $query->select([
                    'permissions.id',
                    'permissions.name',
                ]);
            }
        ])
        ->get([
            'id',
            'name'
        ]);
        if (
            (in_array($this->role->getCompanyAdminRoleId(), \auth()->user()->getUserRolesForRequest())) or
            (in_array($this->role->getSuperAdminRoleId(), \auth()->user()->getUserRolesForRequest()))
        ){
            foreach ($modules as $key => $module){
                $modules[$key]['permission_list'] = ['*'];
                unset($module['permissionList']);
            }
        }
        return $this->successResponse($modules);
    }

    /**
     * @param $modules
     * @param array $moduleIds
     * @return array
     */
    private function convertNestedModulesToModelsArray($modules, $moduleIds = []){
        foreach ($modules as $module){
            $moduleIds[] = $module['id'];
            if (isset($module['subModuleIds']) and count($module['subModuleIds'])){
                $moduleIds = $this->convertNestedModulesToModelsArray($module['subModuleIds'], $moduleIds);
            }
        }
        return $moduleIds;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getModules(Request $request): JsonResponse {
        $modules = Module::with([
                'subModules',
                'permissions:id,name,module_id'
            ])
            ->where('parent_id', null);
        if ($request->get('company_id'))
            $modules = $modules->hasCompany($request->get('company_id'));
        $modules = $modules->get([
            'id',
            'name',
            'parent_id'
        ]);
        return $this->successResponse($modules);
    }

    /**
     * @param Request $request
     * @param $roleId
     * @return JsonResponse
     */
    public function getRolePermissions(Request $request, $roleId): JsonResponse {
        $permissions = $this->role::where('id', $roleId)
            ->with([
                'modules'
            ]);
        if ($request->get('company_id'))
            $permissions = $permissions->companyId($request->get('company_id'));
        $permissions = $permissions->firstOrFail(['id', 'name']);
        return $this->successResponse(
            $this->formatRolePermissionsResponse($permissions)
        );
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getRoles(Request $request): JsonResponse {
        $roles = $this->role;
        if ($request->get('company_id'))
            $roles = $roles->companyId($request->get('company_id'));
        $roles = $roles->get(['id', 'name']);
        return $this->successResponse($roles);
    }

    /**
     * @return JsonResponse
     */
    public function getPermissions(): JsonResponse{
        return $this->successResponse(
            Permission::all(['id', 'name'])
        );
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function setRolePermissions(Request $request): JsonResponse {
        $this->validate($request, $this->getRules($request->get('company_id')));
        if ($request->get('role_id') and $request->get('role_name')){
            $role = $this->saveRole($request, $this->role->where([
                'id' => $request->get('role_id'),
                'company_id' => $request->get('company_id')
            ])->firstOrFail(['id']));
        }
        if (!$request->get('role_id') and $request->get('role_name')){
            $role = $this->saveRole($request, $this->role);
        }
        $permissions = $this->preparePermissionsInsertData($role->getKey(), $request->get('modules'));
        return DB::transaction(function () use ($request, $permissions, $role){
            RoleModulePermission::where('role_id', $role->getKey())->delete();
            RoleModulePermission::insert($permissions);
            return $this->successResponse(trans('responses.OK'));
        });
    }

    private function saveRole(Request $request, Role $role) {
        $role->fill([
            'name' => $request->get('role_name'),
            'company_id' => $request->get('company_id'),
            'created_by' => Auth::id()
        ])->save();
        return $role;
    }

    /**
     * @param int $roleId
     * @param array $modules
     * @return array
     */
    private function preparePermissionsInsertData(int $roleId, array $modules): array {
        $data = [];
        foreach ($modules as $module){
            foreach ($module['permissions'] as $permission){
                $data[] = [
                    'id' => Str::uuid(),
                    'role_id' => $roleId,
                    'module_id' => $module['module_id'],
                    'permission_id' => $permission['id'],
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ];
            }
        }
        return $data;
    }


    /**
     * @param $roleWithPermissions
     * @return array
     */
    private function formatRolePermissionsResponse($roleWithPermissions) {
        $updatedPosition = [
            'id' => $roleWithPermissions->id,
            'name' => $roleWithPermissions->name,
        ];
        $modules = [];
        foreach ($roleWithPermissions->modules as $module){
            if (!isset($modules[$module->id]))
                $modules[$module->id] = [
                    'id' => $module->id,
                    'name' => $module->module_name,
                    'permissions' => []
                ];
            if (
                (in_array($this->role->getCompanyAdminRoleId(), \auth()->user()->getUserRolesForRequest())) or
                (in_array($this->role->getSuperAdminRoleId(), \auth()->user()->getUserRolesForRequest()))
            ){
                $modules[$module->id]['permissions'] = ['*'];
            }
            else {
                $modules[$module->id]['permissions'][] = [
                    'id' => $module->pivot->permission_id,
                    'name' => $module->pivot->permission_name,
                ];
            }
        }
        $updatedPosition['modules'] = array_values($modules);
        return $updatedPosition;
    }

    /**
     * @param $companyId
     * @return array
     */
    private function getRules($companyId): array {
        $rules = [
            'modules' => 'required|array|min:1',
            'modules.*.permissions' => 'required|array',
            'modules.*.permissions.*.id' => 'required|exists:permissions,id',
        ];
        if ($companyId){
            $rules['role_name'] = [
                'nullable',
                'max:255',
                Rule::unique('roles', 'name')->where('company_id', $companyId)
            ];
            $rules['role_id'] = 'nullable|exists:roles,id,company_id,'.$companyId;
            $rules['modules.*.module_id'] = [
                'required',
                Rule::exists('company_modules', 'module_id')
                    ->where('is_active', true)
                    ->where('company_id', $companyId)
            ];
        }
        else {
            $rules['role_name'] = [
                'nullable',
                'max:255',
                Rule::unique('roles', 'name')->where('company_id',  $companyId)
            ];
            $rules['role_id'] = 'nullable|exists:roles,id';
            $rules['modules.*.module_id'] = [
                'required',
                Rule::exists('modules', 'id')
                    ->where('is_active', true)
            ];
        }
        return  $rules;
    }

}
