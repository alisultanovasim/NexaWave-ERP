<?php

namespace Modules\Storage\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Hr\Entities\Employee\Employee;

class   ProductUpdate extends Model
{
    public function setUpdatesAttribute($value)
    {
        $this->attributes['updates'] = \GuzzleHttp\json_encode($value);

    }
    public function getUpdatesAttribute($value)
    {
        return \GuzzleHttp\json_decode($value);
    }

    public function employee(){
        return $this->belongsTo(Employee::class , 'employee_id' , 'id');
    }
    protected $guarded = [];
}
