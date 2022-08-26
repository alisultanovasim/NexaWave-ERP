<?php

namespace Modules\Storage\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Hr\Entities\Employee\Employee;

class Purchase extends Model
{
    use SoftDeletes;

    protected $fillable=[
        'supplier_id',
        'sender',
        'company_id',
        'custom_fee',
        'transport_fee',
        'transport_tax',
        'payment_condition',
        'deliver_condition',
        'deliver_deadline',
        'payment_deadline',
        'total_price'
    ];
    public function supplier()
    {
        return $this->belongsTo(Employee::class);
    }
    public function purchase_products(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PurchaseProduct::class);
    }
}
