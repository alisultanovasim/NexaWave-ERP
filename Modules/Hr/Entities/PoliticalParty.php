<?php

namespace Modules\Hr\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PoliticalParty extends Model
{
    use SoftDeletes;

    protected $fillable = ['company_id','name', 'code', 'address', 'position', 'register_date'];
}
