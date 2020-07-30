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
            'employees.*.details' => 'required',
            'employees.*.details.employee_id' => 'required|integer',
            'employees.*.details.employee_tabel_no' => 'required|min:3|max:255',
            'employees.*.details.employee_name' => 'required|min:3|max:255',
            'employees.*.details.employee_surname' => 'required|min:3|max:255',
            'employees.*.details.employee_father_name' => 'required|min:3|max:255',
            'employees.*.details.employee_gender' => [
                'required',
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
            'employees.*.details.contract_date' => 'required|date|date_format:Y-m-d',
            'employees.*.details.contract_number' => 'required|min:3|max:255',
            'employees.*.details.new_contract_date' => 'required|date|date_format:Y-m-d',
            'employees.*.details.new_contract_number' => 'required|min:3|max:255',
            'employees.*.details.note' => 'nullable|min:3|max:255',
        ];
    }
}
