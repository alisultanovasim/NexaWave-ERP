<?php

namespace Modules\Esd\Entities;

use Illuminate\Database\Eloquent\Model;

class Note extends Model
{

    protected $guarded = ["id"];


    const FILE = 1;
    const EDITOR = 1;

    public function getResourceAttribute($value){
        if ($this->type == self::FILE and $value)
            return env('APP_URL','http://office-backend.vac.az') . '/documents/'  .  $value;

        return $value;
    }
}
