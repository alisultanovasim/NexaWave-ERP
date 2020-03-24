<?php

namespace Modules\Entities;

use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    protected $guarded = ["id"];

    public function getResourceAttribute($value){
        if ($this->type == config('esd.document.type.file') and $value)
            return env('API_GATEWAY_STATIC_FILES') . 'Esd?path=' . 'documents/' .  $value;
        return $value;
    }
}
