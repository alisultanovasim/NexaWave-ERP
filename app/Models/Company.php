<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
    protected $fillable = ['name', 'created_at' , 'owner_id'];

    /**
     * @return HasMany
     */
    public function companyModules()
    {
        return $this->hasMany('App\Models\CompanyModule');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany('App\Models\User' , 'company_role_users' );
    }

    public function role(){
        return $this->belongsToMany('App\Models\Role' , 'company_role_users' );

    }
}
