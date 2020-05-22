<?php

namespace Modules\Hr\Entities\Employee;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'user_id',
        'company_id',
        'is_active',
        'tabel_no'
    ];

    protected $table = 'employees';


    public function contracts()
    {
        return $this->hasMany('Modules\Hr\Entities\Employee\Contract');
    }
    public function contract()
    {
        return $this->hasOne('Modules\Hr\Entities\Employee\Contract')->active();
    }


    public function company(){
        return $this->belongsTo('App\Models\Company');
    }
    public function user(){
        return $this->belongsTo('App\Models\User');
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }

}
