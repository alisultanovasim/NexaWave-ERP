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
            return env('API_GATEWAY_STATIC_FILES') . 'Plaza?path=documents/' . $value;
        return $value;
    }
}
