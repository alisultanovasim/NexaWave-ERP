<?php

namespace Modules\Esd\Entities;

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
        return $this->belongsTo("Modules\Esd\Entities\Section");
    }

    public function from()
    {
        return $this->hasOne("Modules\Esd\Entities\User", "id", "from");
    }

    public function assignment()
    {
        return $this->hasOne("Modules\Esd\Entities\Assignment");
    }

    public function docs()
    {
        return $this->hasMany("Modules\Esd\Entities\Doc");
    }

    public function sendType()
    {
        return $this->belongsTo("Modules\Esd\Entities\sendType", 'send_type');
    }

    public function sendForm()
    {
        return $this->belongsTo("Modules\Esd\Entities\sendForm", 'send_form');
    }

    public function parent()
    {
        return $this->belongsTo("Modules\Esd\Entities\Document", 'parent_id');/*->where('status' , '!=' , config("esd.document.status.draft"));*/
    }

    public function region()
    {
        return $this->belongsTo('Modules\Esd\Entities\Region');
    }

    public function senderCompany()
    {
        return $this->belongsTo('Modules\Esd\Entities\senderCompany', 'sender_company_id', 'id');
    }

    public function senderCompanyUser()
    {
        return $this->belongsTo('Modules\Esd\Entities\senderCompanyUser');
    }

    public function senderCompanyRole()
    {
        return $this->belongsTo('Modules\Esd\Entities\senderCompanyRole');
    }

    public function companyUser()
    {
        return $this->belongsTo('Modules\Hr\Entities\Employee\Employee', 'company_user', 'id');
    }

    public function toInOurCompany()
    {
        return $this->belongsTo('Modules\Hr\Entities\Employee\Employee', 'to_in_our_company', 'id');
    }

    public function fromInOurCompany()
    {
        return $this->belongsTo('Modules\Hr\Entities\Employee\Employee', 'from_in_our_company', 'id');
    }

    public function scopeWithAllRelations($q, $all = false)
    {
        $relations = [
            'companyUser', 'companyUser.user:id,name,surname', 'toInOurCompany', 'toInOurCompany.user:id,name,surname', 'fromInOurCompany', 'fromInOurCompany.user:id,name,surname', 'senderCompany', 'senderCompanyUser', 'senderCompanyRole', 'region'
        ];
        if ($all) {
            $func = function ($q) {
                $q->with('position:id,name')->active()->select(['id', 'position_id']);
            };
            $relations['companyUser.contracts'] = $func;
            $relations['toInOurCompany.contracts'] = $func;
            $relations['fromInOurCompany.contracts'] = $func;
        }

        return $q->with($relations);
    }


}
