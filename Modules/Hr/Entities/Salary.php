<?php

namespace Modules\Hr\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Hr\Entities\Employee\Employee;

class Salary extends Model
{
    use SoftDeletes;
    
    protected $guarded = ['id'];

    public function employee(){
        return $this->belongsTo(Employee::class);
    }

    public function salaryType(){
        return $this->belongsTo(SupplementSalaryType::class, 'salary_type_id');
    }

    public function currency(){
        return $this->belongsTo(Currency::class);
    }


    public function scopeWhereBelongsToCompany($query, $companyId){
        return $query->whereHas('employee', function ($query) use ($companyId){
            $query->where('company_id', $companyId);
        });
    }
}
