<?php

namespace Modules\Hr\Entities;

use App\Models\PositionModulePermission;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Positions extends Model
{
    use SoftDeletes;

    const DIRECTOR = 1;

    protected $guarded = [];

    public function modules(){
        return $this->belongsToMany(
            'App\Models\Module',
            'position_module_permissions',
            'position_id',
            'module_id'
        )
        ->withPivot('permission_id')
        ->join('permissions','permission_id','=','permissions.id')
        ->select([
            'permissions.name as pivot_permission_name',
            'modules.id as id',
            'modules.name as module_name',
        ]);
    }

    public function permissions(){
        return $this->hasMany(PositionModulePermission::class , 'position_id');
    }


}
