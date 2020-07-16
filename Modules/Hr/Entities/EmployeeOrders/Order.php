<?php

namespace Modules\Hr\Entities\EmployeeOrders;

use App\Traits\Filterable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Hr\Entities\Employee\Employee;
use Rennokki\QueryCache\Traits\QueryCacheable;

class Order extends Model
{
    use SoftDeletes, Filterable, QueryCacheable;

    public $cacheFor = 604800;

    protected static $flushCacheOnUpdate = true;

    protected $guarded = ['id'];

    /*
     * Order types
     */
    private $contractConclusionTypeId = 1;
    private $terminationTypeId = 2;
    private $workChangeTypeId = 3;
    private $laborVacationTypeId = 4;
    private $educationVacationTypeId = 5;
    private $unpaidVacationTypeId = 6;
    private $socialVacationTypeId = 7;
    private $businessTripTypeId = 8;


    public function scopeCompanyId($query, $companyId){
        return $query->where('company_id', $companyId);
    }

    public function scopeIsConfirmed($query){
        return $query->where('confirmed_date', '!=', null);
    }

    public function employees(){
        return $this->hasMany(OrderEmployee::class);
    }

    public function confirmedPerson(){
        return $this->belongsTo(Employee::class, 'confirmed_by', 'id');
    }

    /**
     * @return array
     */
    public function getTypeIds(): array {
        return [
            $this->getContractConclusionTypeId(),
            $this->getTerminationTypeId(),
            $this->getWorkChangeTypeId(),
            $this->getLaborVacationTypeId(),
            $this->getEducationVacationTypeId(),
            $this->getUnpaidVacationTypeId(),
            $this->getSocialVacationTypeId(),
            $this->getBusinessTripTypeId()
        ];
    }

    /**
     * @return int
     */
    public function getContractConclusionTypeId(): int
    {
        return $this->contractConclusionTypeId;
    }

    /**
     * @return int
     */
    public function getTerminationTypeId(): int
    {
        return $this->terminationTypeId;
    }

    /**
     * @return int
     */
    public function getWorkChangeTypeId(): int
    {
        return $this->workChangeTypeId;
    }

    /**
     * @return int
     */
    public function getLaborVacationTypeId(): int
    {
        return $this->laborVacationTypeId;
    }

    /**
     * @return int
     */
    public function getEducationVacationTypeId(): int
    {
        return $this->educationVacationTypeId;
    }

    /**
     * @return int
     */
    public function getUnpaidVacationTypeId(): int
    {
        return $this->unpaidVacationTypeId;
    }

    /**
     * @return int
     */
    public function getSocialVacationTypeId(): int
    {
        return $this->socialVacationTypeId;
    }

    /**
     * @return int
     */
    public function getBusinessTripTypeId(): int
    {
        return $this->businessTripTypeId;
    }
}
