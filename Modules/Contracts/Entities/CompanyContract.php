<?php

namespace Modules\Contracts\Entities;

use Illuminate\Database\Eloquent\Model;

class CompanyContract extends Model
{
    protected $guarded = ['id'];

    public function details(){
        return $this->hasMany(CompanyContractDetail::class, 'company_contract_id');
    }

    public function subContracts(){
        return $this->hasMany(CompanyContract::class, 'parent_id');
    }

    public function changes(){
        return $this->hasMany(CompanyContractChange::class, 'company_contract_id');
    }
}
