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
      'storage_name',
      'company_name',
      'product_id',
      'title_id',
      'kind_id',
      'model_id',
      'mark_id',
      'unit_id',
      'color',
      'price',
      'amount',
      'situation',
      'is_completed',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }
}
