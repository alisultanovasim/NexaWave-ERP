<?php

namespace Modules\Hr\Entities\Employee;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{

    protected $fillable = [
        'user_id',
        'company_id',
        'is_active',
    ];

    protected $table = 'employees';


    public function human()
    {
        return $this->belongsTo('Modules\Hr\Entities\Employee\Human', 'human_id', 'id');
    }

    public function contracts()
    {
        return $this->hasMany('Modules\Hr\Entities\Employee\Contract');
    }


    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }
}
