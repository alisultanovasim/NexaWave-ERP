<?php

namespace Modules\Hr\Entities\Employee;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Hr\Entities\CompanyAuthorizedEmployee;

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


    public function authorizedDetails(){
        return $this->hasOne(CompanyAuthorizedEmployee::class, 'employee_id', 'id')
            ->latest();
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

    public function scopeIsAuthorizedCompanyEmployee($query){
        return $query->whereHas('authorizedDetails');
    }

    public function scopeHasActiveContracts($queyr){
        return $queyr->whereHas('contracts', function ($query){
            $query->where('is_active', true);
            $query->where('start_date', '<', Carbon::now());
            $query->where('end_date', '>', Carbon::now());
        });
    }
}
