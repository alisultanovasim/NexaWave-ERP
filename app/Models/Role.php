<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{

    private $superAdminRoleId = 1;
    private $companyAdminRoleId = 5;

    protected $hidden = ['pivot'];

    /**
     * @var array
     */
    protected $guarded = ['id'];


    /**
     * @return HasMany
     */
    public function users()
    {
        return $this->hasMany('App\Models\User');
    }

    public function modules(){
        return $this->belongsToMany(
            'App\Models\Module',
            'role_module_permissions',
            'role_id',
            'module_id'
        )
        ->withPivot('role_id')
        ->join('permissions','permission_id','=','permissions.id')
        ->select([
            'permissions.name as pivot_permission_name',
            'permissions.id as pivot_permission_id',
            'modules.id as id',
            'modules.name as module_name',
        ]);
    }

    public function permissions(){
        return $this->hasMany(PositionModulePermission::class , 'position_id');
    }

    public function scopeCompanyId($query, $companyId) {
        return $query->where('company_id', $companyId);
    }

    /**
     * @return int
     */
    public function getSuperAdminRoleId(): int
    {
        return $this->superAdminRoleId;
    }

    /**
     * @return int
     */
    public function getCompanyAdminRoleId(): int
    {
        return $this->companyAdminRoleId;
    }

    /**
     * @return int
     */
    public function getOfficeAdminRoleId(): int
    {
        return $this->officeAdminRoleId;
    }

}
