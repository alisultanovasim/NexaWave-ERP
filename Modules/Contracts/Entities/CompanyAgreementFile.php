<?php

namespace Modules\Contracts\Entities;

use Illuminate\Database\Eloquent\Model;

class CompanyAgreementFile extends Model
{
    protected $guarded = [];

    public function getAllowedExtensions(): array
    {
        return ['pdf', 'doc', 'docx', 'xlxs'];
    }
}
