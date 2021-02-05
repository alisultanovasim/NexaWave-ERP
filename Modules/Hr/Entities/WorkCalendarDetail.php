<?php

namespace Modules\Hr\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkCalendarDetail extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    public function eventDetails(){
        return $this->belongsTo(CompanyEvent::class, 'event_id', 'id');
    }
}
