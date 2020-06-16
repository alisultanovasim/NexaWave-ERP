<?php

namespace Modules\Storage\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Hr\Entities\Employee\Employee;

class Demand extends Model
{

    use SoftDeletes;

    const STATUS_WAIT = 2;
    const STATUS_REJECTED = 0;
    const STATUS_ACCEPTED = 1;

    protected $guarded = ['id'];

    public function employee(){
        return $this->belongsTo(Employee::class);
    }
    public function product(){
        return $this->belongsTo(Product::class);
    }
    public function assignment(){
        return $this->hasOne(DemandAssignment::class);
    }


    public function scopeCompany($q){
        return $q->where('company_id' , request(
            'company_id'
        ));
    }

}
