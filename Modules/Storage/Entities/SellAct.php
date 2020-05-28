<?php

namespace Modules\Storage\Entities;

use Illuminate\Database\Eloquent\Model;

class SellAct extends Model
{
    protected $guarded = ['id'];
    public function demands(){
        return $this->belongsToMany(
            Demand::class,
            'sell_act_demands',
            'sell_act_id',
            'demand_id'
        );
    }

    public function scopeCompany($q){
        return $q->where('company_id' , request('company_id'));
    }

    public function supplier(){
        return $this->belongsTo(Supplier::class);
    }
}
