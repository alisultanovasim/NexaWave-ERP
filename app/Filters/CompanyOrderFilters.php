<?php

namespace App\Filters;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class CompanyOrderFilters extends QueryFilters {

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

    public function type(int $type): Builder {
        return $this->builder->where('type', $type);
    }

    public function typeIds(array $ids): Builder {
        return $this->builder->whereIn('type', $ids);
    }

    public function isConfirmed(bool $isConfirmed): Builder {
        if ($isConfirmed)
            return $this->builder->where('confirmed_date', '!=', null);
        else
            return $this->builder->where('confirmed_date', '=', null);
    }

    public function confirmedDate(string $date): Builder {
        return $this->builder->whereRaw('DATE_FORMAT(confirmed_date, "%Y-%m-%d") = ?', Carbon::parse($date)->format('Y-m-d'));
    }

    public function signDate(string $date): Builder {
        return $this->builder->whereRaw('DATE_FORMAT(order_sign_date, "%Y-%m-%d") = ?', Carbon::parse($date)->format('Y-m-d'));
    }

    public function companyId(int $companyId): Builder {
        return $this->builder->where('company_id', $companyId);
    }

    public function number(string $number): Builder {
        return $this->builder->where('number', 'like', "%{$number}%");
    }

    public function hasEmployeeId(int $id): Builder {
        return $this->builder->whereHas('employees', function ($query) use ($id){
            $query->where('details->employee_id', $id);
        });
    }
}
