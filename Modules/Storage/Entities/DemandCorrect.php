<?php

namespace Modules\Storage\Entities;

use Illuminate\Database\Eloquent\Model;

class DemandCorrect extends Model
{
    protected $table='demand_corrects';

    public function demand()
    {
        return $this->belongsTo(Demand::class);
    }
}
