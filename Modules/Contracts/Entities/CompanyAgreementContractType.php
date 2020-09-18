<?php

namespace Modules\Contracts\Entities;

use Illuminate\Database\Eloquent\Model;

class CompanyAgreementContractType extends Model
{
    protected $guarded = [];

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
}
