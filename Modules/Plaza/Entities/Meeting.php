<?php


namespace Modules\Plaza\Entities;


use Illuminate\Database\Eloquent\Model;

class Meeting extends Model
{
    protected $table = 'meeting_room_reservations';

    public $timestamps = false;

    protected $guarded = ['id'];


    public function room(){
        return $this->belongsTo('Modules\Plaza\Entities\MeetingRooms' , 'meeting_room' , 'id');
    }
    public function office(){
        return $this->belongsTo('Modules\Plaza\Entities\Office');

    }
}
