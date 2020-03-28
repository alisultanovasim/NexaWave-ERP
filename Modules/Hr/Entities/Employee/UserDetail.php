<?php

namespace Modules\Hr\Entities\Employee;

use Illuminate\Database\Eloquent\Model;

class UserDetail extends Model
{
    const DRIVING_CATEGORIES = ['A1','A','B','C','D','E'];
    protected $table = 'user_details';
    protected $fillable = [
        'fin',
        'birthday',
        'father_name',
        'gender',
        'nationality_id',
        'citizen_id',
        'birthday_country_id',
        'birthday_city_id',
        'birthday_region_id',
        'blood_id',
        'eye_color_id',
        'user_id',
        'passport_seria',
        'passport_number',
        'passport_from_organ',
        'passport_get_at',
        'passport_expire_at',
        'military_status',
        'military_start_at',
        'military_end_at',
        'military_state_id',
        'military_passport_number',
        'military_place',
        'driving_license_number',
        'driving_license_categories',
        'driving_license_organ',
        'driving_license_get_at',
        'driving_license_expire_at',
        'foreign_passport_number',
        'foreign_passport_organ',
        'foreign_passport_get_at',
        'foreign_passport_expire_at',
        'family_status_document_number',
        'family_status_state',
        'family_status_register_at',
        'avatar',
    ];

    public function user(){
        return $this->belongsTo('App\Models\User');
    }

    public function nationality(){
        return $this->belongsTo('Modules\Hr\Entities\Nationality');
    }

    public function citizen(){
        return $this->belongsTo('Modules\Hr\Entities\Country');
    }


    public function birthdayCity(){
        return $this->belongsTo('Modules\Hr\Entities\City');
    }


    public function birthdayCountry(){
        return $this->belongsTo('Modules\Hr\Entities\Country');
    }

    public function birthdayRegion(){
        return $this->belongsTo('Modules\Hr\Entities\Region');
    }
}
