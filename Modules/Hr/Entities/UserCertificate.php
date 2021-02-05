<?php

namespace Modules\Hr\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserCertificate extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function scopeCompany($q ){
        return $q->whereHas('user' , function ($q){
            $q->company();
        });
    }
}
