<?php


namespace Modules\Entities;


use Illuminate\Database\Eloquent\Model;

class Structure extends  Model
{
    public $timestamps = false;

    protected  $guarded = ['id'];
    protected  $table = 'structure_docs';

    public function senderCompany(){
        return $this->belongsTo('Modules\Entities\senderCompany' , 'sender_company_id' , 'id');
    }

    public function senderCompanyUser(){
        return $this->belongsTo('Modules\Entities\senderCompanyUser');
    }

    public function senderCompanyRole(){
        return $this->belongsTo('Modules\Entities\senderCompanyRole');
    }
}
