<?php

namespace Modules\Hr\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property integer $id
 * @property integer $nationality_id
 * @property integer $citizen_id
 * @property integer $birth_country_id
 * @property integer $birth_city_id
 * @property integer $birth_region_id
 * @property integer $eye_color_id
 * @property integer $blood_group_id
 * @property integer $sign_number
 * @property string $name
 * @property string $surname
 * @property string $father_name
 * @property string $gender
 * @property string $birthday
 * @property string $series
 * @property integer $number
 * @property string $fin
 * @property string $from_organ
 * @property string $from_date
 * @property string $expired_at
 * @property string $picture
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property City $birthCity
 * @property Country $birthCountry
 * @property Region $birthRegion
 * @property BloodGroup $bloodGroup
 * @property Country $citizen
 * @property Color $eyeColor
 * @property Nationality $nationality
 */
class EmployerPassportInformation extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'employer_passport_information';

    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'integer';

    /**
     * @var array
     */
    protected $fillable = ['nationality_id', 'citizen_id', 'birth_country_id', 'birth_city_id', 'birth_region_id', 'eye_color_id', 'blood_group_id', 'sign_number', 'name', 'surname', 'father_name', 'gender', 'birthday', 'series', 'number', 'fin', 'from_organ', 'from_date', 'expired_at', 'picture', 'created_at', 'updated_at', 'deleted_at'];

    /**
     * @return BelongsTo
     */
    public function birthCity()
    {
        return $this->belongsTo('Modules\Hr\EntitiesCity', 'birth_city_id');
    }

    /**
     * @return BelongsTo
     */
    public function birthCountry()
    {
        return $this->belongsTo('Modules\Hr\EntitiesCountry', 'birth_country_id');
    }

    /**
     * @return BelongsTo
     */
    public function birthRegion()
    {
        return $this->belongsTo('Modules\Hr\EntitiesRegion', 'birth_region_id');
    }

    /**
     * @return BelongsTo
     */
    public function bloodGroup()
    {
        return $this->belongsTo('Modules\Hr\EntitiesBloodGroup');
    }

    /**
     * @return BelongsTo
     */
    public function citizen()
    {
        return $this->belongsTo('Modules\Hr\EntitiesCountry', 'citizen_id');
    }

    /**
     * @return BelongsTo
     */
    public function eyeColor()
    {
        return $this->belongsTo('Modules\Hr\EntitiesColor', 'eye_color_id');
    }

    /**
     * @return BelongsTo
     */
    public function nationality()
    {
        return $this->belongsTo('Modules\Hr\EntitiesNationality');
    }
}
