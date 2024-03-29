<?php

namespace Modules\Storage\Entities;

use Illuminate\Database\Eloquent\Model;

class ProductModel extends Model
{
    protected $guarded = ['id'];


    public function kind(){
        return $this->belongsTo(ProductKind::class , 'kind_id');
    }

    public function scopeCompany($q){
        return $q->whereHas('kind' , function ($q){
            $q->company();
        });
    }
}
