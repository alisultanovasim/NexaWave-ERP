<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property integer $role_id
 * @property integer $permission_id
 * @property integer $module_id
 * @property string $created_at
 * @property string $updated_at
 * @property Module $module
 * @property Permission $permission
 * @property Role $role
 */
class ModuleRolePermission extends Model
{

    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var array
     */
    protected $fillable = ['role_id', 'permission_id', 'module_id', 'created_at', 'updated_at'];

    /**
     * @return BelongsTo
     */
    public function module()
    {
        return $this->belongsTo('App\Models\Module');
    }

    /**
     * @return BelongsTo
     */
    public function permission()
    {
        return $this->belongsTo('App\Models\Permission');
    }

    /**
     * @return BelongsTo
     */
    public function role()
    {
        return $this->belongsTo('App\Models\Role');
    }
}
