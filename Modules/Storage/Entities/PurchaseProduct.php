<?php

namespace Modules\Storage\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseProduct extends Model
{
    use SoftDeletes;

    protected $fillable=[
        'purchase_id',
        'title_id',
        'kind_id',
        'mark_id',
        'model_id',
        'color',
        'made_in',
        'custom_fee',
        'transport_fee',
        'measure',
        'price',
        'amount',
        'discount',
        'edv_percent',
        'excise_percent',
        'status',
        'total_price'
    ];
    public function purchase(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }
}
