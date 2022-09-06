<?php

namespace Modules\Storage\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Hr\Entities\Employee\Employee;

class Demand extends Model
{
    const STATUS_WAIT=1;
    const STATUS_CONFIRMED=2;
    const STATUS_REJECTED=3;

    use SoftDeletes;
protected $fillable=[
    'name',
    'price_approx',
    'description',
    'product_id',
    'amount',
    'employee_id',
    'forward_to',
    'company_id',
    'status',
];

public function propose()
{
   return $this->hasOne(Propose::class);
}
public function employee(){
    return $this->belongsTo(Employee::class);
}
public function assignment(){
    return $this->hasOne(DemandAssignment::class);
}


public function scopeCompany($q){
    return $q->where('company_id' , request(
        'company_id'
    ));
}

public function product()
{
        return $this->belongsTo(Product::class);
}
}
