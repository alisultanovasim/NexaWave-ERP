<?php


namespace Modules\Hr\Entities\EmployeeOrders;


use Illuminate\Validation\Rule;
use Modules\Hr\Entities\EmployeeOrders\Contracts\OrderType;

class SocialVacation extends Order implements OrderType
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
            'employees.*.details.position_id' => 'required|integer',
            'employees.*.details.position_name' => 'required|max:255',
            'employees.*.details.substitute_worker_id' => 'required|integer',
            'employees.*.details.substitute_worker_tabel_no' => 'required|min:3|max:255',
            'employees.*.details.substitute_worker_position_id' => 'required|integer',
            'employees.*.details.substitute_worker_position_name' => 'required|max:255',
            'employees.*.details.substitute_worker_name' => 'required|max:255',
            'employees.*.details.substitute_worker_surname' => 'required|max:255',
            'employees.*.details.substitute_worker_father_name' => 'required|max:255',
            'employees.*.details.substitute_worker_gender' => [
                'required',
                Rule::in(['f', 'm'])
            ],
            'employees.*.details.vacation_start_date' => 'required|date|date_format:Y-m-d',
            'employees.*.details.vacation_end_date' => 'required|date|date_format:Y-m-d',
            'employees.*.details.work_start_date' => 'required|date|date_format:Y-m-d',
            'employees.*.details.day' => 'required|numeric',
            'employees.*.details.note' => 'nullable|min:3|max:255',
        ];
    }
}
