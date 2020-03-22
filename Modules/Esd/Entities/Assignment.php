<?php

namespace Modules\Entities;

use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    protected $guarded = ["id"];
    public  $timestamps = false;


    public  function  document(){
        return $this->belongsTo("Modules\Entities\Document" );
    }
    public function items(){
        return $this->hasMany('Modules\Entities\AssignmentItem' , 'assignment_id');
    }

}
