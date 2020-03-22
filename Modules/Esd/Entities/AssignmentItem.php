<?php


namespace Modules\Entities;


use Illuminate\Database\Eloquent\Model;

class AssignmentItem extends Model
{

    const WAIT = 1;
    const DONE = 2;
    const DENY = 3;
    const NOT_SEEN = 0;



    public $timestamps = false;
    protected $guarded = ['id'];
    protected $table = 'assignment_items';

    public function notes()
    {
        return $this->hasMany('Modules\Entities\Note');
    }

    public function assignment()
    {
        return $this->belongsTo('Modules\Entities\Assignment' , 'assignment_id' , 'id');
    }


}
