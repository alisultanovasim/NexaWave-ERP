<?php

namespace Modules\Hr\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Hr\Entities\Employee\Employee;

class CompanyAuthorizedEmployee extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];


    public function employee(){
        return $this->belongsTo(Employee::class);
    }
}
