<?php

namespace Modules\Storage\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProposeDocument extends Model
{
    use SoftDeletes;

    const STATUS_WAIT=1;
    const STATUS_REJECTED=2;
    const STATUS_ACCEPTED=3;

    public function proposes()
    {
        return $this->hasMany(Propose::class);
    }
}
