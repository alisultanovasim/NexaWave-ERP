<?php

namespace Modules\Hr\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkCalendar extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    public function details(){
        return $this->hasMany(WorkCalendarDetail::class, 'work_calendar_id');
    }

    public function scopeCompanyId($query, $companyId){
        return $query->where('company_id', $companyId);
    }
}
