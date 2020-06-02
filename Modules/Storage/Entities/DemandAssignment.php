<?php

namespace Modules\Storage\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Hr\Entities\Employee\Employee;

class DemandAssignment extends Model
{
    const STATUS_WAIT = 0;
    const STATUS_ACCEPTED = 1;
    const STATUS_REJECTED = 2;

    protected $guarded = ['id'];

    public function demand(){
        return $this->belongsTo(Demand::class , 'demand_id');
    }

    public function employee(){
        return $this->belongsTo(Employee::class , '');
    }
    public function items(){
        return $this->hasMany(DemandItem::class , '');
    }

    public function scopeCompany($q){
        return $q->whereHas('demand', function ($q){
            $q->company();
        });
    }
}
