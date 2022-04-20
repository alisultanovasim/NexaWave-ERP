<?php


namespace Modules\Plaza\Entities;


use App\Models\Company;
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
    public function company(){
        return $this->belongsTo(Company::class,'company_id','id');
    }
}
