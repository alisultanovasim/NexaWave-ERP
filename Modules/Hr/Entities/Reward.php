<?php

namespace Modules\Hr\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Hr\Entities\Employee\Employee;

class Reward extends Model
{
    use SoftDeletes;
    
    protected $guarded = ['id'];

    public function employee(){
        return $this->belongsTo(Employee::class);
    }

    public function currency(){
        return $this->belongsTo(Currency::class);
    }

    public function rewardType(){
        return $this->belongsTo(RewardType::class, 'reward_type_id');
    }

    public function scopeWhereBelongsToCompany($query, $companyId){
        return $query->whereHas('employee', function ($query) use ($companyId){
            $query->where('company_id', $companyId);
        });
    }
}
