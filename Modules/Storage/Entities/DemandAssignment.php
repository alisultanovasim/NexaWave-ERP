<?php

namespace Modules\Storage\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Hr\Entities\Employee\Employee;

class DemandAssignment extends Model
{
    const STATUS_WAIT = 2;
    const STATUS_ACCEPTED = 1;
    const STATUS_REJECTED = 0;

    protected $guarded = ['id'];

    public function demand(){
        return $this->belongsTo(Demand::class , 'demand_id');
    }

    public function employee(){
        return $this->belongsTo(Employee::class , 'employee_id');
    }
    public function items(){
        return $this->hasMany(DemandItem::class , 'demand_assignment_id' , 'id');
    }

    public function scopeCompany($q){
        return $q->whereHas('demand', function ($q){
            $q->company();
        });
    }
}
