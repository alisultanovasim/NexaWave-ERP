<?php

namespace App\Models;

use App\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TemporaryFile extends Model
{
    use UsesUuid, SoftDeletes;

    protected $guarded = [];
    protected $fillable=['uuid','file','disk','name','extension','size','size_type'];
}
