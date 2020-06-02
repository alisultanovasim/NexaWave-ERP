<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PositionModulePermission extends Model
{
    protected $guarded = [];


    public function module(){
        return $this->belongsTo(Module::class);
    }

    public function permission(){
        return $this->belongsTo(Permission::class);
    }
}
