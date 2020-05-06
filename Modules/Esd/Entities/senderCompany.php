<?php


namespace Modules\Esd\Entities;


use Illuminate\Database\Eloquent\Model;

class senderCompany extends  Model
{
    protected $table = 'sender_companies';

    public $timestamps = false;


    protected $guarded = ['id'];

    public function users(){
        return $this->hasMany('Modules\Esd\Entities\senderCompanyUser');
    }
    public function roles(){
        return $this->hasMany('Modules\Esd\Entities\senderCompanyRole');
    }

    public function scopeCheckSender($q , $request){
        if ($request->has('sender_company_role_id'))
            $q->whereHas('roles', function ($q) use ($request) {
                $q->where('id', $request->get('sender_company_role_id'));
                if ($request->has('sender_company_user_id')) {
                    $q->whereHas('users', function ($query) use ($request) {
                        $query->where('id', $request->get('sender_company_user_id'));
                    });
                }
            });
        return $q;
    }
}
