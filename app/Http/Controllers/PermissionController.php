<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\Permission;
use App\Models\PositionModulePermission;
use App\Models\Role;
use App\Models\RoleModulePermission;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Modules\Hr\Entities\Positions;

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
     * @return JsonResponse
     */
    public function getModules(Request $request): JsonResponse {
        $modules = Module::hasCompany($request->get('company_id'))
            ->with([
                'subModules',
                'permissions:id,name,module_id'
            ])
            ->where('parent_id', null)
            ->get([
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
        $permissions = $this->role->companyId($request->get('company_id'))
            ->where('id', $roleId)
            ->with([
                'modules'
            ])
            ->first(['id', 'name']);
        return $this->successResponse(
            $this->formatRolePermissionsResponse($permissions)
        );
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getRoles(Request $request): JsonResponse {
        return $this->successResponse(
            $this->role->companyId($request->get('company_id'))->get(['id', 'name'])
        );
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
     * @return mixed
     * @throws ValidationException
     */
    public function setRolePermissions(Request $request): JsonResponse {
        $this->validate($request, $this->getRules($request->get('company_id')));
        $permissions = $this->preparePermissionsInsertData($request->get('role_id'), $request->get('modules'));
        return DB::transaction(function () use ($request, $permissions){
            RoleModulePermission::where('role_id', $request->get('role_id'))->delete();
            RoleModulePermission::insert($permissions);
            return $this->successResponse(trans('responses.OK'));
        });
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
        $data = [];
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
            $modules[$module->id]['permissions'][] = [
                'id' => $module->pivot->permission_id,
                'name' => $module->pivot->permission_name,
            ];
        }
        $updatedPosition['modules'] = array_values($modules);
        $data[] = $updatedPosition;
        return $data;
    }

    /**
     * @param $companyId
     * @return array
     */
    private function getRules($companyId): array {
        return [
            'role_id' => 'required|exists:roles,id,company_id,'.$companyId,
            'modules' => 'required|array|min:1',
            'modules.*.permissions' => 'required|array',
            'modules.*.permissions.*.id' => 'required|exists:permissions,id',
            'modules.*.module_id' => [
                'required',
                Rule::exists('company_modules', 'module_id')
                    ->where('is_active', true)
                    ->where('company_id', $companyId)
            ]
        ];
    }

}
