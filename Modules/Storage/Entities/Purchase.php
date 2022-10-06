<?php

namespace Modules\Storage\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Hr\Entities\Employee\Employee;

class Purchase extends Model
{
    use SoftDeletes;
    const STATUS_WAIT=1;
    const STATUS_REJECTED=2;
    const STATUS_ACCEPTED=3;

    const DIRECTOR_ROLE=8;

    protected $fillable=[
        'company_name',
        'supplier_id',
        'sender_id',
        'company_id',
        'progress_status',
        'send_back',
        'status'
    ];
    public function supplier()
    {
        return $this->belongsTo(Employee::class);
    }
    public function purchaseProducts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PurchaseProduct::class);
    }

    public static function boot() {
        parent::boot();
        self::deleting(function($purchase) {
            $purchase->purchaseProducts()->each(function($products) {
                $products->delete();
            });
        });
    }
}
