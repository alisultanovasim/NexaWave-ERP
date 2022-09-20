<?php

namespace Modules\Storage\Entities;

use Illuminate\Database\Eloquent\Model;

class ArchiveRejectedDemand extends Model
{
    protected $fillable=['from_id','reason','demand_id'];
    protected $table='archive_rejected_demands';
}
