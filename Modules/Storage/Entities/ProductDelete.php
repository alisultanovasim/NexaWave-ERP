<?php

namespace Modules\Storage\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Hr\Entities\Employee\Employee;

class ProductDelete extends Model
{
    protected $guarded = [];
    public function employee(){
        return $this->belongsTo(Employee::class , 'employee_id' , 'id');
    }

    public function product(){
        return $this->belongsTo(Product::class);
    }
}
