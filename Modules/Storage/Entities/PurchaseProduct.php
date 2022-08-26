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
        'model_id',
        'mark',
        'color_id',
        'made_in',
        'custom_tax',
        'price',
        'amount',
        'discount',
        'edv_percent',
        'edv_tax',
        'excise_percent',
        'excise_tax',
        'total_price'
    ];
    public function purchase(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }
}
