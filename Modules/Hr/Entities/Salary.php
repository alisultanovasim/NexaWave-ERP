<?php

namespace Modules\Hr\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Salary extends Model
{
    protected $guarded = ['id'];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function salaryType(){
        return $this->belongsTo(SupplementSalaryType::class, 'salary_type_id');
    }

    public function position(){
        return $this->belongsTo(Positions::class);
    }

    public function currency(){
        return $this->belongsTo(Currency::class);
    }


    public function scopeCompany($q ){
        return $q->whereHas('user' , function ($q){
            $q->company();
        });
    }
}
