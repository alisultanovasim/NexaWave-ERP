<?php


namespace Modules\Plaza\Entities;


use Illuminate\Database\Eloquent\Model;

class MeetingRooms extends  Model
{
    protected $guarded = ['id'];

    public $timestamps = false;

    public function meetings(){
        return $this->hasMany('Modules\Plaza\Entities\Meeting' , 'meeting_room' , 'id');
    }

    public function type(){
        return $this->hasMany('Modules\Plaza\Entities\RoomType' , 'room_id', 'id' );
    }

    public function getSchemaAttribute($value){
        if ($value)
            return env('APP_URL') . '/documents/'  .  $value;
        return $value;
    }
    public function images(){
        return $this->hasMany('Modules\Plaza\Entities\MeetingRoomImage' , 'meeting_room_id' , 'id');
    }
}
