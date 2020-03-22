<?php


namespace Modules\Entities;


use Illuminate\Database\Eloquent\Model;

class senderCompanyRole extends Model
{
    protected $table = 'sender_company_roles';

    public $timestamps = false;

    protected $guarded = ['id'];
    public function roles(){
        return $this->belongsTo('Modules\Entities\senderCompanyUser' , 'sender_company_role_id');
    }
}
