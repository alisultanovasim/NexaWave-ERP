<?php

namespace Modules\Storage\Entities;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    const KQ_ID=1;
    const EDED_ID=2;
    public $timestamps = false;
    protected $guarded = ['id'];
}
