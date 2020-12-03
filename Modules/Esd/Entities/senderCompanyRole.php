<?php


namespace Modules\Esd\Entities;


use Illuminate\Database\Eloquent\Model;

class senderCompanyRole extends Model
{
    protected $table = 'sender_company_roles';

    public $timestamps = false;

    protected $guarded = ['id'];

    public function users(){
        return $this->hasMany('Modules\Esd\Entities\senderCompanyUser' , 'sender_company_role_id');
    }
}
