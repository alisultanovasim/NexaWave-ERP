<?php

namespace Modules\Hr\Entities\Employee;

use Illuminate\Database\Eloquent\Model;
use Modules\Hr\Entities\BloodGroup;
use Modules\Hr\Entities\Color;

class UserDetail extends Model
{
    const DRIVING_CATEGORIES = ['A1', 'A', 'B', 'C', 'D', 'E'];
    protected $table = 'user_details';
    protected $guarded = ['id'];
//    protected $fillable = [
//        'fin',
//        'birthday',
//        'father_name',
//        'gender',
//        'nationality_id',
//        'citizen_id',
//        'birthday_country_id',
//        'birthday_city_id',
//        'birthday_region_id',
//        'blood_id',
//        'eye_color_id',
//        'user_id',
//        'passport_seria',
//        'passport_number',
//        'passport_from_organ',
//        'passport_get_date',
//        'passport_expire_date',
//        'military_status',
//        'military_start_date',
//        'military_end_date',
//        'military_state_id',
//        'military_passport_number',
//        'military_place',
//        'driving_license_number',
//        'driving_license_categories',
//        'driving_license_organ',
//        'driving_license_get_at',
//        'driving_license_expire_date',
//        'foreign_passport_number',
//        'foreign_passport_organ',
//        'foreign_passport_get_date',
//        'foreign_passport_expire_date',
//        'family_status_document_number',
//        'family_status_state',
//        'family_status_register_date',
//        'avatar',
//        'social_insurance_no'
//    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function nationality()
    {
        return $this->belongsTo('Modules\Hr\Entities\Nationality');
    }

    public function citizen()
    {
        return $this->belongsTo('Modules\Hr\Entities\Country');
    }


    public function birthdayCity()
    {
        return $this->belongsTo('Modules\Hr\Entities\City');
    }


    public function birthdayCountry()
    {
        return $this->belongsTo('Modules\Hr\Entities\Country');
    }

    public function birthdayRegion()
    {
        return $this->belongsTo('Modules\Hr\Entities\Region');
    }

    public function blood()
    {
        return $this->belongsTo(BloodGroup::class, "blood_id");
    }

    public function eyeColor()
    {
        return $this->belongsTo(Color::class, "eye_color_id");
    }

    public function getAvatarAttribute($value)
    {
        if ($value)
            return env("APP_URL") . "/storage/public/users/" . $value;
        return null;
    }
}
