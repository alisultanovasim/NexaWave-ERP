<?php

namespace Modules\Contracts\Entities;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompanyAgreementAddition extends Model
{
    protected $guarded = ['id'];

    protected $appends = ['remain_days'];

    protected $casts = [
        'currency' => 'json'
    ];

    public function files(): HasMany
    {
        return $this->hasMany(CompanyAgreementFile::class, 'company_agreement_additional_id', 'id');
    }

    public function agreement(): BelongsTo
    {
        return $this->belongsTo(CompanyAgreement::class, 'company_agreement_id', 'id');
    }

    public function getRemainDaysAttribute(): string
    {
        return Carbon::parse($this->getAttribute('end_date'))->diffInDays(Carbon::parse($this->getAttribute('start_date')));
    }

    public function scopeBelongsToCompanyId($query, $companyId): Builder
    {
        return $query->whereHas('agreement', function ($query) use ($companyId) {
            $query->where('id', $companyId);
        });
    }
}
