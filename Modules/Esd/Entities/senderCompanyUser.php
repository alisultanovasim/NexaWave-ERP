<?php


namespace Modules\Entities;


use Illuminate\Database\Eloquent\Model;

class senderCompanyUser extends  Model
{
    protected $table = 'sender_company_users';

    public $timestamps = false;


    protected $guarded = ['id'];


    public function roles(){
        return $this->belongsTo('Modules\Entities\senderCompanyRole' , 'sender_company_role_id');
    }

}
