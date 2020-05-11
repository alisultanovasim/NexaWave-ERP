<?php


namespace Modules\Esd\Entities;


use Illuminate\Database\Eloquent\Model;

class Structure extends  Model
{
    public $timestamps = false;

    protected  $guarded = ['id'];
    protected  $table = 'structure_docs';

    public function senderCompany(){
        return $this->belongsTo('Modules\Esd\Entities\senderCompany' , 'sender_company_id' , 'id');
    }

    public function senderCompanyUser(){
        return $this->belongsTo('Modules\Esd\Entities\senderCompanyUser');
    }

    public function senderCompanyRole(){
        return $this->belongsTo('Modules\Esd\Entities\senderCompanyRole');
    }
}
