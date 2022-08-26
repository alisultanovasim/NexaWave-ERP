<?php

namespace Modules\Storage\Entities;

use Illuminate\Database\Eloquent\Model;

class PurchaseArchive extends Model
{
    protected $table='purchase_archives';
    protected $fillable=[
        'supplier_id',
        'company_id',
        'company_name',
        'product_name',
        'start_date',
        'end_date',
        'product_type',
        'demand_amount',
        'purchase_amount',
        'take_over_amount'
    ];
    protected $casts=[
        'start_date'=>'datetime:Y-m-d',
        'end_date'=>'datetime:Y-m-d'
    ];
}
