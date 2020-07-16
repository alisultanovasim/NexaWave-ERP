<?php

namespace Modules\Hr\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Hr\Entities\Employee\Employee;
use Rennokki\QueryCache\Traits\QueryCacheable;

class Punishment extends Model
{
    use SoftDeletes;
    use QueryCacheable;

    public $cacheFor = 604800;

    protected static $flushCacheOnUpdate = true;

    protected $guarded = ['id'];

    public function employee(){
        return $this->belongsTo(Employee::class);
    }

    public function currency(){
        return $this->belongsTo(Currency::class);
    }

    public function punishmentType(){
        return $this->belongsTo(PunishmentType::class, 'punishment_type_id');
    }

    public function scopeWhereBelongsToCompany($query, $companyId){
        return $query->whereHas('employee', function ($query) use ($companyId){
            $query->where('company_id', $companyId);
        });
    }
}
