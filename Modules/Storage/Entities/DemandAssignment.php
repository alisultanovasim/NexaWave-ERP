<?php

namespace Modules\Storage\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Hr\Entities\Employee\Employee;

class DemandAssignment extends Model
{
    const STATUS_ACCEPTED = 2;
    const STATUS_REJECTED = 3;

    protected $guarded = ['id'];

    protected $fillable=[
        'description',
        'employee_id',
        'demand_id',
        'expiry_time'
    ];
    protected $table='demand_assignments';

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
