<?php

namespace Modules\Storage\Entities;

use Illuminate\Database\Eloquent\Model;

class ProductTitle extends Model
{
    protected $guarded = ['id'];

    public function products(){
        return $this->hasMany(Product::class, 'title_id' , 'id');
    }

    public function kinds(){
        return $this->hasMany(ProductKind::class , 'title_id');
    }
}
