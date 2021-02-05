<?php

namespace Modules\Storage\Entities;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $guarded = ['id'];


    public function scopeCompany($q){
        return $q->where('company_id' , request('company_id'));
    }
}
