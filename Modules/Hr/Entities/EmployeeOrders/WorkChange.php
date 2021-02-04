<?php


namespace Modules\Hr\Entities\EmployeeOrders;


use Illuminate\Validation\Rule;
use Modules\Hr\Entities\EmployeeOrders\Contracts\OrderType;

class WorkChange extends Order implements OrderType
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
            'employees.*.details.new_department_id' => 'nullable|integer',
            'employees.*.details.new_department_name' => 'nullable|min:2|max:255',
            'employees.*.details.new_section_id' => 'nullable|integer',
            'employees.*.details.new_section_name' => 'nullable|min:2|max:255',
            'employees.*.details.new_sector_id' => 'nullable|integer',
            'employees.*.details.new_sector_name' => 'nullable|min:2|max:255',
            'employees.*.details.new_position_id' => 'nullable|integer',
            'employees.*.details.new_position_name' => 'nullable|min:3|max:255',
            'employees.*.details.new_personal_category_id' => 'nullable|integer',
            'employees.*.details.new_personal_category_name' => 'nullable|min:3|max:255',
            'employees.*.details.new_qualification_degree_id' => 'nullable|integer',
            'employees.*.details.new_qualification_degree_name' => 'nullable|min:3|max:255',
            'employees.*.details.contract_date' => 'exclude_if:is_confirmed,0|date|date_format:Y-m-d',
            'employees.*.details.contract_number' => 'exclude_if:is_confirmed,0|min:3|max:255',
            'employees.*.details.new_contract_date' => 'exclude_if:is_confirmed,0|date|date_format:Y-m-d',
            'employees.*.details.new_contract_number' => 'exclude_if:is_confirmed,0|min:3|max:255',
            'employees.*.details.note' => 'nullable|min:3|max:255',
        ];
    }
}
