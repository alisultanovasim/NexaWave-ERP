<?php


namespace Modules\Plaza\Entities;


use Illuminate\Database\Eloquent\Model;

class Floor extends Model
{
    public $timestamps = false;

    protected $guarded = ['id'];

    protected $fillable=['company_id','number','common_size','solid_size'];

    public function offices(){
        return $this->belongsToMany('Modules\Plaza\Entities\Office' , 'offices_locations' , 'office_id' , 'floor_id');
    }
    public function getImageAttribute($value){
        if ($value)
            return config('app.url') . '/storage/' . $value;
        return $value;
    }



}
