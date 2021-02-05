<?php

namespace Modules\Esd\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Hr\Entities\Employee\Employee;

class AssignmentReject extends Model
{
    protected $guarded = [];


    public function item(){
        return $this->belongsTo(AssignmentItem::class , 'item_id' , 'id');
    }
    public function employee(){
        return $this->belongsTo(Employee::class);
    }
}
