<?php


namespace Modules\Plaza\Entities;


use Illuminate\Database\Eloquent\Model;

class OfficeUser extends  Model
{
    protected $guarded = ['id'];

    public $timestamps = false;

    public function office(){
        return $this->belongsTo('Modules\Plaza\Entities\Office');
    }
}
