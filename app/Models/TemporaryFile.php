<?php

namespace App\Models;

use App\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Model;

class TemporaryFile extends Model
{
    use UsesUuid;

    protected $guarded = [];
}
