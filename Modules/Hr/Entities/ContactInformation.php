<?php

namespace Modules\Hr\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContactInformation extends Model
{

    use SoftDeletes;

    protected $table = 'contact_information';

    protected $fillable = [
        'user_id', 'country_id', 'city_id', 'region_id', 'address_type_id', 'address',
        'expire_date', 'post_index', 'fax', 'number', 'created_at', 'updated_at', 'deleted_at'
    ];

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
