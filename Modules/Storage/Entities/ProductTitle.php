<?php

namespace Modules\Storage\Entities;

use Illuminate\Database\Eloquent\Model;

class ProductTitle extends Model
{
    protected $guarded = ['id'];
//    protected $fillable=[
//      'name',
//      'company_id'
//    ];

    public function products(){
        return $this->hasMany(Product::class, 'title_id' , 'id');
    }

    public function demands()
    {
        return $this->hasMany(Demand::class);
    }

    public function kinds(){
        return $this->hasMany(ProductKind::class , 'title_id');
    }
}
