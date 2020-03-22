<?php

namespace Modules\Hr\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contract extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public function workShifts()
    {
        return $this->hasMany(WorkShift::class);
    }
}
