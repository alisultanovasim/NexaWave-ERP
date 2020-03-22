<?php


namespace Modules\Plaza\Entities;


use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    public $timestamps = false;
    public $guarded = ['id', 'created_at'];

    public function worker(){
        return $this->hasOne('Modules\Plaza\Entities\Worker' , 'card' , 'id');
    }
    public function attendance(){
        return $this->hasMany('Modules\Plaza\Entities\Attendance' , 'card_id' , 'alias');
    }
}
