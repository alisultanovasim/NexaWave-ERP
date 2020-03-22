<?php


namespace Modules\Plaza\Entities;


use Illuminate\Database\Eloquent\Model;

class Guest extends  Model
{

    public $timestamps = false;

    protected  $guarded = ['id'];

    public function office(){
        return $this->belongsTo('Modules\Plaza\Entities\Office');
    }
    public function worker(){
        return $this->belongsTo('Modules\Plaza\Entities\Worker');
    }

}
