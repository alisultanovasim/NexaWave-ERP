<?php

namespace Modules\Hr\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Hr\Entities\Employee\Employee;

class PastUnusedVacation extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    public function employee(){
        return $this->belongsTo(Employee::class);
    }

    public function scopeBelongsToCompany($query, $companyId){
        return $query->whereHas('employee', function ($query) use ($companyId){
            $query->where('company_id', $companyId);
        });
    }
}
