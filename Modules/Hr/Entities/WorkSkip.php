<?php

namespace Modules\Hr\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Hr\Entities\Employee\Employee;

class WorkSkip extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    private $reasonSickness = 1;

    private $reasonUnknown = 2;

    public function employee(){
        return $this->belongsTo(Employee::class);
    }

    public function confirmedEmployee(){
        return $this->belongsTo(Employee::class, 'confirmed_employee_id');
    }

    public function scopeCompanyId($query, $companyId){
        return $query->where('company_id', $companyId);
    }

    public function scopeType($query, $type){
        return $query->where('reason_type', $type);
    }

    /**
     * @return array
     */
    public function getReasonTypes(): array {
        return [
            $this->getReasonSickness(),
            $this->getReasonUnknown()
        ];
    }

    /**
     * @return int
     */
    public function getReasonSickness(): int
    {
        return $this->reasonSickness;
    }

    /**
     * @return int
     */
    public function getReasonUnknown(): int
    {
        return $this->reasonUnknown;
    }
}
