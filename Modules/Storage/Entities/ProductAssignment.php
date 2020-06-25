<?php

namespace Modules\Storage\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Hr\Entities\Department;
use Modules\Hr\Entities\Employee\Employee;

class ProductAssignment extends Model
{
    protected $guarded = [];


    const ASSIGN_TO_USER = 1 ;
    const ASSIGN_TO_PLACE = 2 ;

    public function scopeCompany($q){
        return $q->where('company_id', request('company_id'));
    }

    public function employee(){
        return $this->belongsTo(Employee::class);
    }
    public function department(){
        return $this->belongsTo(Department::class);
    }
    public function section(){
        return $this->belongsTo(Employee::class);
    }
    public function sector(){
        return $this->belongsTo(Employee::class);
    }

}
