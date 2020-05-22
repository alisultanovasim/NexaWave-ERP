<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{

    private $superAdminId = 1;
    /**
     * @var array
     */
    protected $guarded = ['name'];


    /**
     * @return HasMany
     */
    public function users()
    {
        return $this->hasMany('App\Models\User');
    }

    /**
     * @return int
     */
    public function getSuperAdminId(): int
    {
        return $this->superAdminId;
    }



}
