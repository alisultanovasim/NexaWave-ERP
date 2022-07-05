<?php

namespace Modules\Hr\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Hr\Entities\Employee\Employee;

class Consent extends Model
{
    use SoftDeletes;
    protected $fillable=[
      'company_id',
      'requester_id',
      'start_date',
      'end_date',
      'work_date',
      'responsible_id',
      'reason',
      'office_id',
    ];
    public function employee(){
        return $this->belongsTo(Employee::class,'requester_id');
    }
}
