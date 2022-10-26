<?php

namespace Modules\Storage\Entities;

use Illuminate\Database\Eloquent\Model;

class DemandCorrect extends Model
{
    protected $fillable=[
    'from_id',
    'demand_id',
    'description',
    'role_id'
    ];
    protected $table='demand_corrects';

    public function demand()
    {
        return $this->belongsTo(Demand::class);
    }
}
