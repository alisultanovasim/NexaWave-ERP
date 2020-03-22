<?php


namespace Modules\Entities;


use Illuminate\Database\Eloquent\Model;

class senderCompany extends  Model
{
    protected $table = 'sender_companies';

    public $timestamps = false;


    protected $guarded = ['id'];

    public function users(){
        return $this->hasMany('Modules\Entities\senderCompanyUser');
    }
    public function roles(){
        return $this->hasMany('Modules\Entities\senderCompanyRole');
    }
}
