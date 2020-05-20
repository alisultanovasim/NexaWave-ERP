<?php

namespace Modules\Hr\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserSocialState extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function currency(){
        return $this->belongsTo(Currency::class);
    }

    public function stateType(){
        return $this->belongsTo(SocialState::class, 'social_state_type_id');
    }

    public function scopeCompany($query){
        return $query->whereHas('user', function ($query){
            $query->company();
        });
    }
}
