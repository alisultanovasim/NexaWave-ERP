<?php


namespace App\Models;



use Illuminate\Database\Eloquent\Model;

class CompanyRoleUser extends Model
{
    public $timestamps= false;

    protected $guarded = [];

    protected $hidden = ['role_id'  , 'company_id'];

    public function company()
    {
        return $this->belongsTo('App\Models\Company');
    }

    public function role()
    {
        return $this->belongsTo('App\Models\Role');
    }

    public function modules()
    {
        return $this->hasMany('App\Models\CompanyModule' , 'company_id' , 'company_id');
    }

}
