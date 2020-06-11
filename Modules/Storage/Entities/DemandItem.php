<?php

namespace Modules\Storage\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Hr\Entities\Employee\Employee;

class DemandItem extends Model
{
    const REJECTED = 0;
    const ACCEPTED = 1;
    const WAIT = 2;

    protected $guarded = ["id"];


    public function assignment(){
        return $this->belongsTo(DemandAssignment::class , 'demand_assignment_id');
    }

    public function employee(){
        return $this->belongsTo(Employee::class);
    }
    public function scopeCompany($q){
        return $q->whereHas('assignment' , function ($q){
            $q->company();
        });
    }
}
