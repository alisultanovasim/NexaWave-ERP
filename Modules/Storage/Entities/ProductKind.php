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
}
