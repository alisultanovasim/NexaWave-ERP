<?php

namespace Modules\Hr\Entities\Employee;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Human extends Model
{
    use SoftDeletes;
    protected $table = 'humans';

    protected $fillable = [
        'fin', 'name', 'surname', 'father_name',
        'birthday', 'gender', 'nationality_id',
        'citizen_id', 'birthday_country_id',
        'birthday_city_id', 'birthday_region_id',
        'blood_id', 'eye_color_id', 'passport_seria',
        'passport_number', 'passport_from_organ',
        'passport_get_at', 'passport_expire_at', 'email',
    ];


    public function employment()
    {
        return $this->hasMany('Modules\Hr\Entities\Employee\Employee', 'human_id', 'id');
    }
}
