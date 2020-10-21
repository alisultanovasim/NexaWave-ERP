<?php

namespace Modules\Contracts\Entities;

use Illuminate\Database\Eloquent\Model;

class CompanyAgreement extends Model
{
    const activeStatus = 1;
    const terminatedStatus = 2;

    protected $guarded = ['id'];

    protected $casts = [
        'contract_type' => 'json',
        'currency' => 'json'
    ];
}
