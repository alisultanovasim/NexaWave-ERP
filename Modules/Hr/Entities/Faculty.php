<?php

namespace Modules\Hr\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Faculty extends Model
{
    use SoftDeletes;

    protected $guarded = [];
}
