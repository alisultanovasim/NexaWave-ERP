<?php


namespace Modules\Hr\Entities\EmployeeOrders;

use Illuminate\Database\Eloquent\Builder;
use Modules\Hr\Entities\EmployeeOrders\Contracts\OrderType;

class ContractConclusion extends Order implements OrderType
{

    public function getRules(): array
    {
        return [
            'order_employees.*.id' => 'nullable|integer',
            'order_employees.*.details' => 'required',
            'order_employees.*.details.employee_id' => 'required|integer',
            'order_employees.*.details.employee_tabel_no' => 'required|min:3|max:255',
            'order_employees.*.details.employee_name' => 'required|min:3|max:255',
            'order_employees.*.details.employee_surname' => 'required|min:3|max:255',
            'order_employees.*.details.employee_father_name' => 'required|min:3|max:255',
            'order_employees.*.details.department_id' => 'required|integer',
            'order_employees.*.details.department_name' => 'required|min:3|max:255',
            'order_employees.*.details.section_id' => 'required|integer',
            'order_employees.*.details.section_name' => 'required|min:3|max:255',
            'order_employees.*.details.sector_id' => 'required|integer',
            'order_employees.*.details.sector_name' => 'required|min:3|max:255',
            'order_employees.*.details.position_id' => 'required|integer',
            'order_employees.*.details.position_name' => 'required|min:3|max:255',
            'order_employees.*.details.personal_category_id' => 'required|integer',
            'order_employees.*.details.personal_category_name' => 'required|min:3|max:255',
            'order_employees.*.details.qualification_degree' => 'required|min:3|max:255',
            'order_employees.*.details.recruitment_date' => 'required|date|date_format:Y-m-d',
            'order_employees.*.details.contract_date' => 'required|date|date_format:Y-m-d',
            'order_employees.*.details.contract_number' => 'required|min:3|max:255',
            'order_employees.*.details.probation_time' => 'required|min:3|max:255',
            'order_employees.*.details.salary' => 'required|min:3|max:255',
            'order_employees.*.details.note' => 'required|min:3|max:255',
        ];
    }


    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope('type', function (Builder $builder) {
            $builder->where('type', 1);
        });
    }
}
