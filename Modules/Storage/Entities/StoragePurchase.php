<?php

namespace Modules\Storage\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StoragePurchase extends Model
{
    use SoftDeletes;
    protected $table='storage_purchases';
    protected $fillable=[
      'purchase_id',
      'company_name',
      'is_completed',
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function items()
    {
        return $this->hasMany(StoragePurchaseItem::class,'storage_purchase_id','id');
    }
}
