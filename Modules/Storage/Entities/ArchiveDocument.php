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

    protected $table='archive_documents';
    protected $fillable=[
        'document_id',
        'document_type',
        'from_id',
        'reason',
        'status',
    ];

    public function demands()
    {
        return $this->hasMany(Demand::class,'id','document_id');
    }

    public function proposes()
    {
        return $this->hasMany(ProposeDocument::class,'id','document_id');
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class,'id','document_id');
    }
}
