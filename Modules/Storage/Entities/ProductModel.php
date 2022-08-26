<?php

namespace Modules\Storage\Entities;

use Illuminate\Database\Eloquent\Model;

class ProductModel extends Model
{
    protected $table=['product_models'];
//    protected $guarded = ['id'];
//    protected $fillable=['name','kind_id'];


    public function kind(){
        return $this->belongsTo(ProductKind::class , 'kind_id');
    }

//    public function scopeCompany($q){
//        return $q->whereHas('kind' , function ($q){
//            $q->company();
//        });
//    }
}
