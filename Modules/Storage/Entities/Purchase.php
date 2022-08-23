<?php

namespace Modules\Storage\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Hr\Entities\Employee\Employee;

class Purchase extends Model
{
    use SoftDeletes;

    public function supplier()
    {
        return $this->belongsTo(Employee::class);
    }
    public function purchase_products(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PurchaseProduct::class);
    }
}
