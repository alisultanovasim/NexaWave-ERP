<?php


namespace Modules\Hr\Entities\EmployeeOrders;


use Illuminate\Validation\Rule;
use Modules\Hr\Entities\EmployeeOrders\Contracts\OrderType;

class BusinessTrip extends Order implements OrderType
{

    public function getEmployeeValidateRules(): array
    {
        return [
            'employees.*.id' => 'nullable|integer',
            'employees.*.details' => 'exclude_if:is_confirmed,0',
            'employees.*.details.employee_id' => 'exclude_if:is_confirmed,0|integer',
            'employees.*.details.employee_tabel_no' => 'exclude_if:is_confirmed,0|max:255',
            'employees.*.details.employee_name' => 'exclude_if:is_confirmed,0|min:3|max:255',
            'employees.*.details.employee_surname' => 'exclude_if:is_confirmed,0|min:3|max:255',
            'employees.*.details.employee_father_name' => 'exclude_if:is_confirmed,0|min:3|max:255',
            'employees.*.details.employee_gender' => [
                'exclude_if:is_confirmed,0',
                Rule::in(['f', 'm'])
            ],
            'employees.*.details.position_id' => 'exclude_if:is_confirmed,0|integer',
            'employees.*.details.position_name' => 'exclude_if:is_confirmed,0|max:255',
            'employees.*.details.substitute_worker_id' => 'exclude_if:is_confirmed,0|integer',
            'employees.*.details.substitute_worker_tabel_no' => 'exclude_if:is_confirmed,0|min:3|max:255',
            'employees.*.details.substitute_worker_position_id' => 'exclude_if:is_confirmed,0|integer',
            'employees.*.details.substitute_worker_position_name' => 'exclude_if:is_confirmed,0|max:255',
            'employees.*.details.substitute_worker_name' => 'exclude_if:is_confirmed,0|max:255',
            'employees.*.details.substitute_worker_surname' => 'exclude_if:is_confirmed,0|max:255',
            'employees.*.details.substitute_worker_father_name' => 'exclude_if:is_confirmed,0|max:255',
            'employees.*.details.substitute_worker_gender' => [
                'exclude_if:is_confirmed,0',
                Rule::in(['f', 'm'])
            ],
            'employees.*.details.vacation_place' => 'exclude_if:is_confirmed,0|max:255',
            'employees.*.details.vacation_purpose' => 'exclude_if:is_confirmed,0|max:255',
            'employees.*.details.vacation_start_date' => 'exclude_if:is_confirmed,0|date|date_format:Y-m-d',
            'employees.*.details.vacation_end_date' => 'exclude_if:is_confirmed,0|date|date_format:Y-m-d',
            'employees.*.details.work_start_date' => 'exclude_if:is_confirmed,0|date|date_format:Y-m-d',
            'employees.*.details.day' => 'exclude_if:is_confirmed,0|numeric',
            'employees.*.details.note' => 'nullable|min:3|max:255',
        ];
    }
}
