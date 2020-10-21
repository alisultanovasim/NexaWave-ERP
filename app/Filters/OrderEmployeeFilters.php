<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class OrderEmployeeFilters extends QueryFilters {

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

    public function employeeId(int $id): Builder {
        return $this->builder->where('details->employee_id', $id);
    }

    public function employeeTableNo(string $tableNo): Builder {
        return $this->builder->where('details->employee_tabel_no', 'like', "{$tableNo}");
    }

    public function employeeName(string $name): Builder {
        return $this->builder->where('details->employee_name', 'like', "{$name}");
    }

    public function employeeSurname(string $surname): Builder {
        return $this->builder->where('details->employee_surname', 'like', "{$surname}");
    }

    public function employeeFatherName(string $name): Builder {
        return $this->builder->where('details->employee_father_name', 'like', "{$name}");
    }
}
