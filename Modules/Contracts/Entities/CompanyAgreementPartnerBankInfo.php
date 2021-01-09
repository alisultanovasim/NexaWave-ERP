<?php

namespace Modules\Contracts\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Hr\Entities\Currency;

class CompanyAgreementPartnerBankInfo extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    public function currency(): HasOne
    {
        return $this->hasOne(Currency::class, 'id', 'currency_id');
    }
}
