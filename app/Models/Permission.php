<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property integer $id
 * @property integer $module_id
 * @property string $name
 * @property boolean $is_active
 * @property string $created_at
 * @property string $updated_at
 * @property Module $module
 * @property ModuleRolePermission[] $moduleRolePermissions
 */
class Permission extends Model
{

    const CREATE = 1;
    const READ = 2;
    const UPDATE = 3;
    const DELETE = 4;
    protected $hidden = ['pivot'];

    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'integer';

    /**
     * @var array
     */
    protected $fillable = ['module_id', 'name', 'is_active', 'created_at', 'updated_at'];




    /**
     * @return BelongsTo
     */
    public function module()
    {
        return $this->belongsTo('App\Models\Module');
    }

    /**
     * @return HasMany
     */
    public function moduleRolePermissions()
    {
        return $this->hasMany('App\Models\ModuleRolePermission');
    }
}
