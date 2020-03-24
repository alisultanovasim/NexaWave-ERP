<?php

namespace Modules\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{

    const WAIT = 0;
    const ACTIVE = 1;
    const DRAFT = 2;  //qaralama
    const DONE = 3;
    const ARCHIVE = 4;
    const WAIT_FOR_ACCEPTANCE = 5;

    use SoftDeletes;
    protected $guarded = ["id"];

    public function section()
    {
        return $this->belongsTo("Modules\Entities\Section");
    }

    public function from()
    {
        return $this->hasOne("Modules\Entities\User", "id", "from");
    }

    public function assignment()
    {
        return $this->hasOne("Modules\Entities\Assignment");
    }

    public function docs()
    {
        return $this->hasMany("Modules\Entities\Doc");
    }

    public function sendType()
    {
        return $this->belongsTo("Modules\Entities\sendType", 'send_type');
    }

    public function sendForm()
    {
        return $this->belongsTo("Modules\Entities\sendForm", 'send_form');
    }

    public function parent()
    {
        return $this->belongsTo("Modules\Entities\Document", 'parent_id');/*->where('status' , '!=' , config("modules.document.status.draft"));*/
    }

    public function region()
    {
        return $this->belongsTo('Modules\Entities\Region');
    }

    public function senderCompany()
    {
        return $this->belongsTo('Modules\Entities\senderCompany', 'sender_company_id', 'id');
    }

    public function senderCompanyUser()
    {
        return $this->belongsTo('Modules\Entities\senderCompanyUser');
    }

    public function senderCompanyRole()
    {
        return $this->belongsTo('Modules\Entities\senderCompanyRole');
    }
    public function companyUser(){
        return $this->belongsTo('App\Models\User' , 'company_user' , 'id');
    }
    public function toInOurCompany(){
        return $this->belongsTo('App\Models\User' , 'to_in_our_company' , 'id');
    }
    public function fromInOurCompany(){
        return $this->belongsTo('App\Models\User' , 'from_in_our_company' , 'id');
    }



}
