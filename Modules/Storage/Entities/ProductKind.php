<?php

namespace Modules\Storage\Entities;

use Illuminate\Database\Eloquent\Model;

class ProductKind extends Model
{
    protected $table = 'product_kinds';
    protected $guarded = ['id'];
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
        return $this->hasMany(ProductModel::class , 'kind_id');
    }


    public function scopeCompany($q)
    {
        return $q->whereHas('title' , function ($q){
            $q->where('company_id' , request('company_id'));
        });
    }
}
