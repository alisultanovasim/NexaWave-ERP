<?php

namespace Modules\Contracts\Entities;

use Illuminate\Database\Eloquent\Model;

class CompanyAgreementAddition extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'currency' => 'json'
    ];
}
