<?php

namespace Modules\Hr\Entities\User;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Modules\Hr\Entities\EducationLevel;
use Modules\Hr\Entities\EducationPlace;
use Modules\Hr\Entities\EducationSpecialty;
use Modules\Hr\Entities\EducationState;
use Modules\Hr\Entities\Language;

class UserEducation extends Model
{
    protected $table = 'user_educations';
    protected $guarded = ['id'];

    public function user(){
        return $this->belongsTo(User::class);
    }
    public function speciality(){
        return $this->belongsTo(EducationSpecialty::class);
    }
    public function place(){
        return $this->belongsTo(EducationPlace::class);
    }
    public function level(){
        return $this->belongsTo(EducationLevel::class);
    }
    public function state(){
        return $this->belongsTo(EducationState::class);
    }
    public function language(){
        return $this->belongsTo(Language::class);
    }

    public function scopeCompany($q , $company_id){
        return $q->whereHas('user' , function ($q) use($company_id){
            $q->whereHas('employment' , function ($q) use ($company_id){
                $q->where('company_id' , $company_id);
            });
        });
    }
}
