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

    public function user(){
        return $this->belongsTo('App\Models\User');
    }
}
