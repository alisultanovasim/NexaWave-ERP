<?php


namespace Modules\Esd\Entities;


use Illuminate\Database\Eloquent\Model;

class Citizen extends Model
{

    public $timestamps = false;

    protected  $guarded = ['id'];

    protected  $table = 'citizen_docs';

    public function region(){
        return $this->belongsTo('Modules\Esd\Entities\Region');
    }

}
