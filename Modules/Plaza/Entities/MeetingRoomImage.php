<?php

namespace Modules\Plaza\Entities;
use Illuminate\Database\Eloquent\Model;


class MeetingRoomImage extends Model
{
    public $timestamps = false;
    protected $guarded = ['id'];

    public function room(){
        return $this->belongsTo('Modules\Plaza\Entities\MeetingRooms' ,  'meeting_room_id' , 'id');
    }

    public function getUrlAttribute($value){
        if ($value)
            return env('APP_URL') . '/storage/'  . $value;
        return $value;
    }
}
