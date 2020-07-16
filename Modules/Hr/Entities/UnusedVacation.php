<?php

namespace Modules\Hr\Entities;

use Illuminate\Database\Eloquent\Model;

class UnusedVacation extends Model
{
    protected $guarded = ['id'];

    public function scopeCompanyId($query, $companyId){
        return $query->where('company_id', $companyId);
    }
}
