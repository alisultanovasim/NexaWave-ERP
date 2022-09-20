<?php

namespace Modules\Storage\Entities;

use Illuminate\Database\Eloquent\Model;

class DemandCorrect extends Model
{
    protected $table='demand_corrects';

    public function demands()
    {
        return $this->hasMany(Demand::class);
    }
}
