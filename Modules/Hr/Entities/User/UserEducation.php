<?php

namespace Modules\Hr\Entities\User;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Modules\Hr\Entities\EducationLevel;
use Modules\Hr\Entities\EducationPlace;
use Modules\Hr\Entities\EducationSpecialty;
use Modules\Hr\Entities\EducationState;
use Modules\Hr\Entities\Faculty;
use Modules\Hr\Entities\Language;

class UserEducation extends Model
{
    protected $table = 'user_educations';
    protected $guarded = ['id'];

    public function user(){
        return $this->belongsTo(User::class);
    }
    public function speciality(){
        return $this->belongsTo(EducationSpecialty::class , 'education_specialty_id');
    }
    public function place(){
        return $this->belongsTo(EducationPlace::class , 'education_place_id');
    }
    public function level(){
        return $this->belongsTo(EducationLevel::class , 'education_level_id');
    }
    public function state(){
        return $this->belongsTo(EducationState::class , 'education_state_id');
    }
    public function language(){
        return $this->belongsTo(Language::class);
    }
    public function faculty(){
        return $this->belongsTo(Faculty::class);
    }

    public function scopeCompany($q ){



        return $q->whereHas('user' , function ($q){
            $q->company();
        });
    }
}
