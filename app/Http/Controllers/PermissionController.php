<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\Permission;
use App\Models\PositionModulePermission;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Modules\Hr\Entities\Positions;

class PermissionController extends Controller
{
    use ApiResponse;

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getModules(Request $request): JsonResponse {
        $modules = Module::hasCompany($request->get('company_id'))
            ->with([
                'subModules'
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
     * @return JsonResponse
     */
    public function getPositions(Request $request): JsonResponse{
        $permissions = Positions::where('company_id', $request->get('company_id'))
        ->with([
            'modules'
        ])
        ->get();
//        return  $this->successResponse($permissions);
        return $this->successResponse(
            $this->beautifyPositionPermissionsResponse($permissions)
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
    public function setPositionPermissions(Request $request): JsonResponse{
        $this->validate($request, $this->getRules($request->get('company_id')));
        $permissions = $this->prepareStoreData($request->get('position_id'), $request->get('modules'));
        return DB::transaction(function () use ($request, $permissions){
            PositionModulePermission::where('position_id', $request->get('position_id'))->delete();
            PositionModulePermission::insert($permissions);
            return $this->successResponse(trans('responses.OK'));
        });
    }

    /**
     * @param int $positionId
     * @param array $modules
     * @return array
     */
    private function prepareStoreData(int $positionId, array $modules): array {
        $data = [];
        foreach ($modules as $module){
            foreach ($module['position_permissions'] as $permission){
                $data[] = [
                    'id' => Str::uuid(),
                    'position_id' => $positionId,
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
     * @param $positionModulePermissions
     * @return array
     */
    private function beautifyPositionPermissionsResponse($positionModulePermissions): array {
        $data = [];
        foreach ($positionModulePermissions as $position){
            $updatedPosition = [
                'id' => $position->id,
                'name' => $position->name,
            ];
            $modules = [];
            foreach ($position->modules as $module){
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
        }
        return $data;
    }

    /**
     * @param $companyId
     * @return array
     */
    private function getRules($companyId): array {
        return [
            'position_id' => 'required|exists:positions,id,company_id,'.$companyId,
            'modules' => 'required|array|min:1',
            'modules.*.position_permissions' => 'required|array',
            'modules.*.position_permissions.*.id' => 'required|exists:permissions,id',
            'modules.*.module_id' => [
                'required',
                Rule::exists('company_modules', 'module_id')
                    ->where('is_active', true)
                    ->where('company_id', $companyId)
            ]
        ];
    }

}
