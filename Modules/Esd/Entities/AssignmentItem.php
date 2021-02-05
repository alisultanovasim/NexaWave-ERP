<?php


namespace Modules\Esd\Entities;


use Illuminate\Database\Eloquent\Model;

class AssignmentItem extends Model
{

    const WAIT = 1;
    const DONE = 2;
    const DENY = 3;
    const NOT_SEEN = 0;
    const REJECTED = 4;


    public $timestamps = false;
    protected $guarded = ['id'];
    protected $table = 'assignment_items';

    public function notes()
    {
        return $this->hasMany('Modules\Esd\Entities\Note');
    }

    public function employee()
    {
        return $this->belongsTo('Modules\Hr\Entities\Employee\Employee', 'user_id', 'id');
    }

    public function assignment()
    {
        return $this->belongsTo('Modules\Esd\Entities\Assignment', 'assignment_id', 'id');
    }

    public function rejects(){
        return $this->hasMany(AssignmentReject::class , 'item_id' , 'id');
    }
}
