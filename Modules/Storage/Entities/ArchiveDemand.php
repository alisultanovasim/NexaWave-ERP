<?php

namespace Modules\Storage\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Hr\Entities\Employee\Employee;

class ArchiveDemand extends Model
{
    protected $fillable=['from_id','reason','demand_id'];
    protected $table='archive_rejected_demands';

    public function employee()
    {
        return $this->belongsTo(Employee::class,'from_id');
    }
}
