<?php


namespace Modules\Plaza\Entities;


use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    public $timestamps = false;

    protected  $fillable = ['card_id' , 'incoming_id' , 'timeStamp' ,'action_type'];



    public function card(){
        return $this->belongsTo('Modules\Plaza\Entities\Card' , 'card_id' , 'alias');
    }


    public function getActionTypeAttribute($value){
        switch ($value){
            case config('plaza.action_type.in') :
                return 1;
                break;
            case config('plaza.action_type.out') :
                return 0;
                break;
            default:
                return 2;
                break;
        }
    }
}
