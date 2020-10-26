<?php


namespace Modules\Hr\Entities\EmployeeOrders;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Modules\Hr\Entities\EmployeeOrders\Contracts\OrderType;

class ContractConclusion extends Order implements OrderType
{

    public function getEmployeeValidateRules(): array
    {
        return [
            'employees.*.id' => 'nullable|integer',
            'employees.*.details' => 'exclude_if:is_confirmed,0',
            'employees.*.details.employee_id' => 'exclude_if:is_confirmed,0|integer',
            'employees.*.details.employee_tabel_no' => 'exclude_if:is_confirmed,0|max:255',
            'employees.*.details.employee_name' => 'exclude_if:is_confirmed,0|max:255',
            'employees.*.details.employee_surname' => 'exclude_if:is_confirmed,0|max:255',
            'employees.*.details.employee_father_name' => 'exclude_if:is_confirmed,0|max:255',
            'employees.*.details.employee_gender' => [
                'exclude_if:is_confirmed,0',
                Rule::in(['f', 'm'])
            ],
            'employees.*.details.department_id' => 'nullable|integer',
            'employees.*.details.department_name' => 'nullable|min:2|max:255',
            'employees.*.details.section_id' => 'nullable|integer',
            'employees.*.details.section_name' => 'nullable|min:2|max:255',
            'employees.*.details.sector_id' => 'nullable|integer',
            'employees.*.details.sector_name' => 'nullable|min:2|max:255',
            'employees.*.details.position_id' => 'nullable|integer',
            'employees.*.details.position_name' => 'nullable|min:3|max:255',
            'employees.*.details.personal_category_id' => 'nullable|integer',
            'employees.*.details.personal_category_name' => 'nullable|min:3|max:255',
            'employees.*.details.qualification_degree' => 'nullable|min:3|max:255',
            'employees.*.details.recruitment_date' => 'exclude_if:is_confirmed,0|date|date_format:Y-m-d',
            'employees.*.details.contract_date' => 'exclude_if:is_confirmed,0|date|date_format:Y-m-d',
            'employees.*.details.contract_number' => 'exclude_if:is_confirmed,0|max:255',
            'employees.*.details.probation_time' => 'exclude_if:is_confirmed,0|max:255',
            'employees.*.details.salary' => 'exclude_if:is_confirmed,0|integer',
            'employees.*.details.note' => 'nullable|min:3|max:255',
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
