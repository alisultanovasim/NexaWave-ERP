<?php


namespace Modules\Plaza\Entities;


use Illuminate\Database\Eloquent\Model;

class Message extends Model
{

    protected $guarded = ['id'];
    public $timestamps = false;
    public function dialog(){
        return $this->belongsTo('Modules\Plaza\Entities\Dialog');
    }
}
