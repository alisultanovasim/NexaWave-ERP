<?php

namespace Modules\Esd\Entities;

use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    protected $guarded = ["id"];
    public  $timestamps = false;


    public  function  document(){
        return $this->belongsTo("Modules\Esd\Entities\Document" );
    }
    public function items(){
        return $this->hasMany('Modules\Esd\Entities\AssignmentItem' , 'assignment_id');
    }

}
