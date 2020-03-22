<?php


namespace Modules\Plaza\Entities;


use Illuminate\Database\Eloquent\Model;

class Offer extends  Model
{
    protected  $fillable = ['name' , 'client' , 'size' , 'description' ,'phone', 'come_at' , 'status' , 'company_id' , 'room_count' , 'worker_count' , 'car_count','specialization_id'];

    public function specialization(){
        return $this->belongsTo('Modules\Plaza\Entities\Specialization');
    }
}
