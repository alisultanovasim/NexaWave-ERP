<?php

namespace Modules\Contracts\Entities;

use App\Traits\Filterable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompanyAgreement extends Model
{
    use Filterable;

    const activeStatus = 1;
    const terminatedStatus = 2;

    protected $guarded = ['id'];
    protected $appends = ['remain_days'];
    protected $casts = [
        'contract_type' => 'json',
        'currency' => 'json'
    ];

    public function files(): HasMany
    {
        return $this->hasMany(CompanyAgreementFile::class, 'company_agreement_id', 'id');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(CompanyAgreementParticipant::class, 'company_agreement_id', 'id');
    }

    public function getRemainDaysAttribute(): string
    {
        return Carbon::parse($this->getAttribute('end_date'))->diffInDays(Carbon::parse($this->getAttribute('start_date')));
    }
}
