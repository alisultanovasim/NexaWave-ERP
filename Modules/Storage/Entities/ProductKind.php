<?php

namespace Modules\Storage\Entities;

use Illuminate\Database\Eloquent\Model;

class ProductKind extends Model
{
    protected $table = 'product_kinds';

    protected $guarded = ['id'];
    protected $fillable = ['name', 'title_id', 'company_id','unit_id'];

    public function title(){
        return $this->belongsTo(ProductTitle::class);
    }
    public function unit(){
        return $this->belongsTo(Unit::class);
    }
    public function products(){
        return $this->hasMany(Product::class , 'kind_id' , 'id');
    }
    public function models(){
        return $this->hasMany(ProductModel::class );
    }

    public function demands()
    {
        return $this->hasMany(Demand::class);
    }

    public function demandItem()
    {
        return $this->hasOne(DemandItem::class,'kind_id');
    }

    public function purchaseProduct()
    {
        return $this->hasOne(PurchaseProduct::class,'kind_id','id');
    }


    public function scopeCompany($q)
    {
        return $q->whereHas('title' , function ($q){
            $q->where('company_id' , request('company_id'));
        });
    }
}
