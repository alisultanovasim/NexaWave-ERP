<?php

namespace Modules\Hr\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Rennokki\QueryCache\Traits\QueryCacheable;

class WorkCalendar extends Model
{
    use SoftDeletes;
    use QueryCacheable;

    public $cacheFor = 604800;

    protected static $flushCacheOnUpdate = true;

    protected $guarded = ['id'];

    public function details(){
        return $this->hasMany(WorkCalendarDetail::class, 'work_calendar_id');
    }

    public function scopeCompanyId($query, $companyId){
        return $query->where('company_id', $companyId);
    }
}
