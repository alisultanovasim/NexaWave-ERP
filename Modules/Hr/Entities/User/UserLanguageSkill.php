<?php

namespace Modules\Hr\Entities\User;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Modules\Hr\Entities\EducationLevel;
use Modules\Hr\Entities\Language;
use Modules\Hr\Entities\LanguageLevel;


class UserLanguageSkill extends Model
{
    protected $table = 'user_language_skills';

    protected $guarded = ['id'];

    public function user(){
        return $this->belongsTo(User::class);
    }
    public function language(){
        return $this->belongsTo(Language::class);
    }
    public function listening(){
        return $this->belongsTo(LanguageLevel::class  ,'listening');
    }
    public function reading(){
        return $this->belongsTo(LanguageLevel::class , 'reading');
    }
    public function comprehension(){
        return $this->belongsTo(LanguageLevel::class , 'comprehension');
    }
    public function writing(){
        return $this->belongsTo(LanguageLevel::class , 'writing');
    }

    public function scopeCompany($q){
        return $q->whereHas('user' , function ($q){
            $q->company();
        });
    }
}
