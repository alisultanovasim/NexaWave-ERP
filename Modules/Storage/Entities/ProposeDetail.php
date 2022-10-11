<?php

namespace Modules\Storage\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProposeDetail extends Model
{
    use SoftDeletes;
    protected $table='propose_details';
    protected $fillable=[
      'propose_id',
      'price',
      'amount',
    ];

    public function propose()
    {
        return $this->belongsTo(Propose::class);
    }

    public function demandItem(){
        return $this->belongsTo(DemandItem::class);
    }
}
