<?php

namespace Modules\Storage\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Hr\Entities\Employee\Employee;

class   ProductModel extends Model
{
    protected $table='product_models';
    protected $fillable=['kind_id','name'];

    public function kind()
    {
        return $this->belongsTo(ProductKind::class);
    }

    public function demands()
    {
        return $this->hasMany(Demand::class);
    }

    public function demandItem()
    {
        return $this->hasMany(DemandItem::class,'model_id');
    }

    public function purchaseProduct()
    {
        return $this->hasOne(PurchaseProduct::class,'mark_id','id');
    }
//    protected $guarded = [];
}
