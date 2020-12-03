<?php


namespace Modules\Plaza\Entities;


use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $table = 'offices_locations';

    protected $guarded = ['id'];

    public  function  floor(){
        return $this->belongsTo('Modules\Plaza\Entities\Floor');
    }

    public function getSchemaAttribute($value){
        if ($value)
            return config('app.url') . '/storage/' . $value;
        return $value;
    }
}
