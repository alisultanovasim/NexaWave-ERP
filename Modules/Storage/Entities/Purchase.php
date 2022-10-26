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
    const STATUS_FINISHED=4;

    const DIRECTOR_ROLE=8;
    const PURCHASED_ROLE=42;
    const FINANCIER_ROLE=25;

    protected $fillable=[
        'propose_document_id',
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

    public function proposeDocument()
    {
        return $this->belongsTo(ProposeDocument::class);
    }

    public static function boot() {
        parent::boot();
        self::deleting(function($purchase) {
            $purchase->purchaseProducts()->each(function($products) {
                $products->delete();
            });
        });
    }

    public function storage()
    {
        return $this->hasOne(StoragePurchase::class);
    }
}
