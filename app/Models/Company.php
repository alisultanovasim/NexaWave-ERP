<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Hr\Entities\Department;
use Modules\Hr\Entities\Section;
use Modules\Hr\Entities\Sector;

/**
 * @property integer $id
 * @property string $name
 * @property string $created_at
 * @property CompanyModule[] $companyModules
 * @property User[] $users
 */
class Company extends Model
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
    protected $fillable = ['name', 'created_at', 'owner_id'];

    /**
     * @return HasMany
     */
    public function companyModules()
    {
        return $this->hasMany('App\Models\CompanyModule');
    }

    public function modules()
    {
        return $this->belongsToMany(
            "App\Models\Module",
            'company_modules',
            'company_id',
            'module_id');
    }

    /**
     * @return BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany('App\Models\User', 'company_role_users');
    }

    public function role()
    {
        return $this->belongsToMany('App\Models\Role', 'company_role_users');

    }

    public function structuredDepartments(){
        return $this->hasMany(Department::class, 'structable_id', 'id')
            ->where('structable_type', 'company')
            ->with([
                'structuredSections:id,name,structable_id,structable_type',
                'structuredSectors:id,name,structable_id,structable_type'
            ]);
    }

    public function structuredSections(){
        return $this->hasMany(Section::class, 'structable_id', 'id')
            ->where('structable_type', 'company')
            ->with([
                'structuredSectors:id,name,structable_id,structable_type',
            ]);
    }

    public function structuredSectors(){
        return $this->hasMany(Sector::class, 'structable_id', 'id')
            ->where('structable_type', 'company');
    }

}
