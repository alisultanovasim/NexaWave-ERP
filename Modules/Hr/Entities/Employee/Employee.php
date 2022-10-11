<?php

namespace Modules\Hr\Entities\Employee;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Hr\Entities\CompanyAuthorizedEmployee;
use Modules\Storage\Entities\ArchiveDemand;
use Modules\Storage\Entities\ArchivePropose;
use Modules\Storage\Entities\ArchivePurchase;
use Modules\Storage\Entities\NewProductAmount;
use Modules\Storage\Entities\ProductAssignment;
use Modules\Storage\Entities\Purchase;

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


    public function demands()
    {
        return $this->hasMany(Employee::class,'forward_to');
    }
    public function contracts()
    {
        return $this->hasMany('Modules\Hr\Entities\Employee\Contract')->where(['is_active'=>1,'is_terminated'=>0]);
    }

    public function contract()
    {
        return $this->hasOne('Modules\Hr\Entities\Employee\Contract');
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

    public function purchases(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    public function scopeHasActiveContracts($queyr){
        return $queyr->whereHas('contracts', function ($query){
            $query->where('is_active', true);
            $query->where('start_date', '<', Carbon::now());
            $query->where('end_date', '>', Carbon::now());
        });
    }

    public function addedAmounts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(NewProductAmount::class);
    }

    public function assignments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProductAssignment::class);
    }

    public function archiveDemands()
    {
        return $this->hasMany(ArchiveDemand::class);
    }

    public function archiveProposes()
    {
        return $this->hasMany(ArchivePropose::class);
    }

    public function archivePurchases()
    {
        return $this->hasMany(ArchivePurchase::class);
    }
}
