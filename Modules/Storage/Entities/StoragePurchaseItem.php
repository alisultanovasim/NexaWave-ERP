<?php

namespace Modules\Storage\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StoragePurchaseItem extends Model
{
    use SoftDeletes;
    protected $table='storage_purchase_items';
    protected $fillable=[
        'storage_purchase_id',
        'storage_id',
        'title_id',
        'kind_id',
        'mark_id',
        'model',
        'color',
        'measure',
        'price',
        'amount',
        'situation'
    ];

    public function storagePurchase()
    {
        return $this->belongsTo(StoragePurchase::class);
    }

    public function purchaseProduct()
    {
        return $this->belongsTo(PurchaseProduct::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
