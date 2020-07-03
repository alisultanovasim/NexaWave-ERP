<?php


namespace Modules\Hr\Entities\EmployeeOrders;


use Illuminate\Validation\Rule;
use Modules\Hr\Entities\EmployeeOrders\Contracts\OrderType;

class LaborVacation extends Order implements OrderType
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
            'employees.*.details.substitute_worker_id' => 'required|integer',
            'employees.*.details.substitute_worker_tabel_no' => 'required|min:3|max:255',
            'employees.*.details.substitute_worker_name' => 'required|min:3|max:255',
            'employees.*.details.substitute_worker_surname' => 'required|min:3|max:255',
            'employees.*.details.substitute_worker_father_name' => 'required|min:3|max:255',
            'employees.*.details.substitute_worker_gender' => [
                'required',
                Rule::in(['f', 'm'])
            ],
            'employees.*.details.non_working_days' => 'required|array',
            'employees.*.details.non_working_days.*' => 'required|max:50',
            'employees.*.details.non_working_days_count' => 'required|numeric|max:100',
            'employees.*.details.start_date' => 'required|date|date_format:Y-m-d',
            'employees.*.details.end_date' => 'required|date|date_format:Y-m-d',
            'employees.*.details.vacation_details' => 'nullable|array',
            'employees.*.details.vacation_details.*.beginning_of_work_year' => 'required|numeric',
            'employees.*.details.vacation_details.*.end_of_work_year' => 'required|numeric',
            'employees.*.details.vacation_details.*.day' => 'required|numeric',
            'employees.*.details.vacation_details.*.part_of_vacation' => 'required|max:50',
            'employees.*.details.note' => 'nullable|min:3|max:255',
        ];
    }
}
