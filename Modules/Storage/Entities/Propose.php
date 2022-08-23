<?php

namespace Modules\Storage\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Request;

class Propose extends Model
{
    use SoftDeletes;
    protected $fillable=[
        'demand_name',
        'company_name',
        'company_id',
        'price',
        'offer_file',
        'description'
    ];
}
