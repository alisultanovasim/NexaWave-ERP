<?php

namespace Modules\Storage\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Propose extends Model
{
    use SoftDeletes;

    const STATUS_WAIT=1;
    const STATUS_REJECTED=2;
    const STATUS_ACCEPTED=3;

    protected $fillable=[
        'demand_id',
        'company_name',
        'company_id',
        'price',
        'offer_file',
        'description',
        'employee_id',
        'status'
    ];

    public function demand()
    {
        return $this->belongsTo(Demand::class);
    }

    public function storageDocument()
    {
        return $this->belongsTo(StorageDocyment::class);
    }
}
