<?php

namespace Modules\Hr\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Esd\Entities\User;

class ContactInformation extends Model
{
    protected $guarded = ['id'];

    public function country(){
        return $this->belongsTo(Country::class);
    }

    public function city(){
        return $this->belongsTo(City::class);
    }

    public function addressType(){
        return $this->belongsTo(AddressType::class);
    }

    public function region(){
        return $this->belongsTo(Region::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function scopeCompany($q ){
        return $q->whereHas('user' , function ($q){
            $q->company();
        });
    }
}
