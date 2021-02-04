<?php

namespace Modules\Hr\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RelationshipType extends Model
{
    use SoftDeletes;

    protected $guarded = [];
}
