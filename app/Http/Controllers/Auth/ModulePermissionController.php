<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\Permission;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

/**
 * Class ModulePermissionController
 * @package App\Http\Controllers
 */
class ModulePermissionController extends Controller
{
    use ApiResponse;


    /**
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function getModuleAndPermissionList()
    {
        $permissions = Permission::with([])
            ->whereNull("module_id")
            ->where("is_active", "=", true)
            ->get()->toArray();
        $modules = Module::with(['permissions:id,module_id,name,is_active'])
            ->where("is_active", '=', true)
            ->get([
                'id',
                'name',
                'parent_id',
                'is_active'
            ])->toArray();

        foreach ($modules as $key => $module) {
            $module['permissions'] = array_merge($module['permissions'], $permissions);
            $modules[$key] = $module;
        }
        return $this->successResponse($modules);
    }


}
