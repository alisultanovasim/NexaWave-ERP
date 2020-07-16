<?php

namespace Modules\Hr\Entities;

use App\Models\PositionModulePermission;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Rennokki\QueryCache\Traits\QueryCacheable;

class Positions extends Model
{
    use SoftDeletes;
    use QueryCacheable;

    public $cacheFor = 604800;

    protected static $flushCacheOnUpdate = true;

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

    public function structures(){
        return $this->hasMany(StructurePosition::class, 'position_id', 'id');
    }

    public function scopeExistsInStructure($query){
        return $query->whereHas('structures');
    }

}
