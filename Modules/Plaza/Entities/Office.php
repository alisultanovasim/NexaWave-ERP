<?php


namespace Modules\Plaza\Entities;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Office  extends Model
{
    use SoftDeletes;
    public  $timestamps = false;

    protected $guarded = ['id'];

    public  function  location(){
        return $this->hasMany('Modules\Plaza\Entities\Location' , 'office_id' , 'id' );
    }

    public function contract(){
        return $this->hasOne('Modules\Plaza\Entities\Contract' , 'office_id' , 'id' );
    }
    public function contact(){
        return $this->hasMany('Modules\Plaza\Entities\Contact' );
    }
    public function workers(){
        return $this->hasMany('Modules\Plaza\Entities\Workers' , 'office_id' , 'id' );
    }
    public function documents(){
        return $this->hasMany('Modules\Plaza\Entities\Document');
    }

    public function getImageAttribute($value){
        if ($value)
            return env('APP_URL') . '/documents/'  .  $value;
        return $value;
    }

}
