<?php


namespace App\Http\Controllers\Auth;


use App\Http\Controllers\Controller;
use App\Http\Middleware\RolePermissionMiddleware;
use App\Models\ModuleRolePermission;
use App\Models\Role;
use App\Traits\ApiResponse;
use Doctrine\DBAL\Driver\Mysqli\MysqliException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Http\ResponseFactory;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class RoleController
 * @package App\Http\Controllers\Auth
 */
class RoleController extends Controller
{
    use ApiResponse;


    /**
     * @param Request $request
     * @return mixed
     * @throws ValidationException
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            $this->validate($request, [
                'name' => "required|string|min:2",
                "description" => "sometimes|required|string|min:1|max:255",
                "modules" => "required|array",
                "modules.*.id" => "required_with:modules|int|distinct",
                "modules.*.permissions" => "required_with:modules|array",
                "modules.*.permissions.*" => "required_with:modules|int",
            ]);
            $role = new Role();
            $role->name = $request->input("name");
            $role->description = $request->input("description");
            $role->created_by = Auth::id();
            $role->save();

            $moduleRolePermissions = [];
            foreach ($request->input("modules") as $module) {
                foreach ($module["permissions"] as $permission) {
                    $moduleRolePermissions[] = [
                        'id' => Str::uuid(),
                        "role_id" => $role->id,
                        "permission_id" => $permission,
                        "module_id" => $module['id'],
                        "created_at" => Carbon::now(),
                        "updated_at" => Carbon::now(),
                    ];
                }
            }
            ModuleRolePermission::with([])->insert($moduleRolePermissions);
            DB::commit();
            return $this->successResponse([], Response::HTTP_CREATED);

        } catch (MysqliException $exception) {
            DB::rollBack();
        }
    }


    /**
     * @param Request $request
     * @param $id
     * @return mixed
     * @throws ValidationException
     * @throws \Throwable
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => "required|string|min:2",
            "description" => "sometimes|required|string|min:1|max:255",
            "modules" => "required|array",
            "modules.*.id" => "required_with:modules|int|distinct",
            "modules.*.permissions" => "required_with:modules|array",
            "modules.*.permissions.*" => "required_with:modules|int",
        ]);

        DB::transaction(function () use ($request, $id) {
            $role = Role::with([])->findOrFail($id);
            $role->fill($request->only([
                'name',
                'description'
            ]));
            $role->save();

            $moduleRolePermissions = [];
            ModuleRolePermission::with([])->where("role_id", "=", $role->id)->delete();

            foreach ($request->input("modules") as $module) {
                foreach ($module["permissions"] as $permission) {
                    $moduleRolePermissions[] = [
                        'id' => Str::uuid(),
                        "role_id" => $role->id,
                        "permission_id" => $permission,
                        "module_id" => $module['id'],
                        "created_at" => Carbon::now(),
                        "updated_at" => Carbon::now(),
                    ];
                }
            }
            ModuleRolePermission::with([])->insert($moduleRolePermissions);

        });
        return $this->successResponse([], Response::HTTP_OK);
    }


    /**
     * @param $id
     * @return mixed
     * @throws \Exception
     */
    public function destroy($id)
    {
        $role = Role::with(['users'])->findOrFail($id);
        if (count($role->users) > 0)
            return $this->errorResponse(trans('responses.role_assigned'), Response::HTTP_BAD_REQUEST);

        ModuleRolePermission::with([])->where("role_id", "=", $role->id)->delete();
        $role->delete();
        return $this->successResponse([]);

    }


    /**
     * @param $name
     * @param $companyId
     * @param $positionId
     * @param $createdBy
     * @param null $roleId
     * @return mixed
     */
    public function companyCreateOrUpdateRole($name, $companyId , $positionId , $createdBy, $roleId = null){
        if(!$roleId)
            $role = new Role();
        else
        {
            $role = Role::where([
                'id' => $roleId,
                'company_id' => $companyId
            ])->first(['id']);
            if (!$role)
                throw new ModelNotFoundException();
        }

        $role->fill([
            'name' => $name,
            'company_id' => $companyId,
            'created_by' => $createdBy,
            'position_id' => $positionId
        ]);
        $role->save();
        return $role->getKey();
    }
}
