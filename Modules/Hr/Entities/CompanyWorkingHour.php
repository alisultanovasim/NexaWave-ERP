<?php

namespace Modules\Hr\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompanyWorkingHour extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    public function contractType(){
        return $this->belongsTo(ContractType::class, 'contract_id');
    }
}
