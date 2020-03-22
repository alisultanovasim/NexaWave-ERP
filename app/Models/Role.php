<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property integer $id
 * @property string $name
 * @property string $description
 * @property integer $company_id
 * @property integer $position_id
 * @property integer $created_by
 * @property string $created_at
 * @property string $updated_at
 * @property ModuleRolePermission[] $moduleRolePermissions
 * @property User[] $users
 */
class Role extends Model
{
    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'integer';

    /**
     * @var array
     */
    protected $fillable = ['name', 'company_id', 'description', 'created_by', 'created_at', 'updated_at' , 'position_id'];

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
    public function users()
    {
        return $this->hasMany('App\Models\User');
    }
}
