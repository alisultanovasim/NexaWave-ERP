<?php

namespace Modules\Storage\Entities;

use Illuminate\Database\Eloquent\Model;

class DemandItem extends Model
{
    const WAIT = 0;
    const ACCEPTED = 1;
    const REJECTED = 2;

    protected $guarded = ["id"];


    public function assignment(){
        return $this->belongsTo(DemandAssignment::class);
    }
}
