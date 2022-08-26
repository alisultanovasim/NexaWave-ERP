<?php

namespace Modules\Storage\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StorageDocyment extends Model
{
    use SoftDeletes;
    protected $table='storage_documents';
    protected $fillable=[
        'propose_id',
        'company_id',
        'barcode',
        'storage_id',
        'expiration_date',
        'amount',
        'document'
    ];

    public function propose(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Propose::class);
    }
}
