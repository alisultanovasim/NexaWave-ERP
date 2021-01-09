<?php


namespace Modules\Contracts\Filters;


use App\Filters\QueryFilters;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Modules\Contracts\Entities\CompanyAgreement;

class AgreementFilters extends QueryFilters
{
    protected $request;

    /**
     * UserFilters constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
        parent::__construct($request);
    }

    public function isExpired(bool $isExpired)
    {
        $condition = $isExpired ? '<' : '>';
        return $this->builder->where('end_date', $condition, Carbon::now());
    }

    public function isTerminated(bool $isTerminated)
    {
        return $this->builder->where('status', $isTerminated ? CompanyAgreement::terminatedStatus : CompanyAgreement::approvedStatus);
    }

    public function parentId(int $parentId)
    {
        return $this->builder->where('parent_id', $parentId);
    }
}
