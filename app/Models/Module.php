<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Rennokki\QueryCache\Traits\QueryCacheable;

/**
 * @property integer $id
 * @property string $name
 * @property integer $parent_id
 * @property boolean $is_active
 * @property string $created_at
 * @property string $updated_at
 * @property CompanyModule[] $companyModules
 * @property ModuleRolePermission[] $moduleRolePermissions
 * @property Permission[] $permissions
 */
class   Module extends Model
{
//    use QueryCacheable;

//    public $cacheFor = 24 * 60 * 60; // cache time, in seconds

    //uncomment above line after changing cache driver to redis
//    public $cacheFor = 0;
    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'integer';

    /**
     * @var array
     */
    protected $fillable = ['name', 'parent_id', 'is_active', 'created_at', 'updated_at'];

    /**
     * @return HasMany
     */
    public function companyModules()
    {
        return $this->hasMany('App\Models\CompanyModule');
    }

    /**
     * @return HasMany
     */
    public function moduleRolePermissions()
    {
        return $this->hasMany('App\Models\ModuleRolePermission');
    }

    /**
     * @return HasMany
     */
    public function permissions()
    {
        return $this->hasMany('App\Models\Permission');
    }

    /**
     * @return BelongsToMany
     */
    public function positionPermissions()
    {
        return $this->belongsToMany(
            'App\Models\Permission',
            'position_module_permissions',
            'module_id',
            'permission_id'
        );
    }

    /**
     * Invalidate the cache automatically
     * upon update in the database.
     *
     * @var bool
     */
    protected static $flushCacheOnUpdate = true;


    /**
     * @return HasMany
     */
    public function subModules()
    {
        return $this->hasMany('App\Models\Module', 'parent_id', 'id')
            ->with(['subModules:id,name,parent_id']);
    }

    public function scopeHasCompany($query, $companyId){
        return $query->whereHas('companyModules', function ($query) use ($companyId){
            $query->where('company_id', $companyId);
            $query->where('is_active', true);
        });
    }
}
