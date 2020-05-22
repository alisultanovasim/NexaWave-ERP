<?php

namespace Modules\Storage\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Hr\Entities\Employee\Employee;

class Demand extends Model
{

    use SoftDeletes;

    const STATUS_WAIT = 0;
    const STATUS_ASSIGNED = 1;
    const STATUS_REJECTED = 2;
    const STATUS_ACCEPTED = 3;

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

}
