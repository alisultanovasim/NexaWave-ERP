<?php

namespace Modules\Esd\Entities;

use Illuminate\Database\Eloquent\Model;

class Archive extends Model
{
    protected $guarded = [ "id"];

    public $timestamps = false;

    public function acceptor(){
        return $this->belongsTo("Modules\Esd\Entities\Worker")->select(["name" , "id"]);
    }
    public function creator(){
        return $this->belongsTo("Modules\Esd\Entities\Worker")->select(["name" , "id"]);
    }
}
