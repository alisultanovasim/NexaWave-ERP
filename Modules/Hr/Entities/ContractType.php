<?php

namespace Modules\Hr\Entities;

use Illuminate\Database\Eloquent\Model;

class ContractType extends Model
{
    protected $table = 'contract_types';
    protected $guarded = ['id'];
}
