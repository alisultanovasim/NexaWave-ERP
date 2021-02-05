<?php


namespace Modules\Plaza\Entities;


use Illuminate\Database\Eloquent\Model;

class RoomType extends Model
{
    public $timestamps = false;


    public function room(){
        return $this->belongsTo('Modules\Plaza\Entities\MeetingRooms' , 'room_id' , 'id');
    }
}
