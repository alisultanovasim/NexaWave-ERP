<?php

namespace Modules\Storage\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProposeDocument extends Model
{

    use SoftDeletes;
    protected $fillable=[
      'status',
      'demand_id',
      'company_id',
      'offer_file',
      'description',
      'send_back'
    ];
    const STATUS_WAIT=1;
    const STATUS_REJECTED=2;
    const STATUS_CONFIRMED=3;

    const DIRECTOR_ROLE=8;

    public function proposes()
    {
        return $this->hasMany(Propose::class);
    }

    public function purchase()
    {
        return $this->hasOne(Purchase::class);
    }

    public function demand()
    {
        return $this->belongsTo(Demand::class);
    }

    public function archive()
    {
        return $this->belongsTo(ArchiveDocument::class);
    }
}
