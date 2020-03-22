<?php

namespace App\Http\Middleware;

use App\Models\ModuleRolePermission;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RolePermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param \Closure $next
     * @param $module_id
     * @param $permission_id
     * @return mixed
     */
    public function handle($request, Closure $next, $module_id, $permission_id)
    {
        if ($this->hasPermission($module_id, $permission_id))
            return $next($request);
        else
            abort(403, "Forbidden");
    }

    public function hasPermission($module_id, $permission_id)
    {
        $hasPermission = ModuleRolePermission::with([])
            ->whereHas("role.users", function ($query) {
                $query->where("id", "=", Auth::id());
            })->where("module_id", "=", $module_id)
            ->where("permission_id", "=", $permission_id)
            ->exists();

        /**
         * todo
         * return $hasPermission :D
         */
        if ($hasPermission)
            return true;
        return false;


    }
}
