<?php

namespace Modules\Storage\Entities;

use Illuminate\Database\Eloquent\Model;

class ProductState extends Model
{
    protected $guarded = [];

    public function scopeCompany($q){
        return $q->where(function ($query){
            $query->where('company_id' , request('company_id') )
                ->orWhereNull('company_id' );
        });
    }
}
