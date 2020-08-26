<?php

namespace Modules\Contracts\Entities;

use Illuminate\Database\Eloquent\Model;

class CompanyContractFile extends Model
{
    protected $guarded = ['id'];

    public function contract(){
        return $this->belongsTo(CompanyContract::class, 'company_contract_id');
    }
}
