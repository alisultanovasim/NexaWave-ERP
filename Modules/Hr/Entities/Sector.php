<?php

namespace Modules\Hr\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sector extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public function section()
    {
        return $this->belongsTo(Section::class);
    }
}
