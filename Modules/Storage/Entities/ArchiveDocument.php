<?php

namespace Modules\Storage\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ArchiveDocument extends Model
{
    use SoftDeletes;

    const DEMAND_TYPE=1;
    const PPROPOSE_TYPE=2;
    const PURCHASE_TYPE=3;
    const REJECTED_STATUS=1;
    const FINISHED_STATUS=2;

    protected $table='archive_documents';
    protected $fillable=[
        'demand_id',
        'propose_id',
        'purchase_id',
        'demand_draft_id',
        'from_id',
        'reason',
        'status',
    ];

    public function demands()
    {
        return $this->hasMany(Demand::class,'id','demand_id');
    }

    public function demandDrafts()
    {
        return $this->hasMany(DemandDraft::class,'id','demand_draft_id');
    }

    public function proposes()
    {
        return $this->hasMany(ProposeDocument::class,'id','propose_id');
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class,'id','purchase_id');
    }
}
