<?php

namespace Modules\Hr\Http\Controllers;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Modules\Hr\Entities\CompanyAuthorizedEmployee;
use Modules\Hr\Entities\Employee\Employee;

class CompanyAuthorizedUsersController extends Controller
{
    use ApiResponse, ValidatesRequests;

    private $companyAuthorizedEmployee;

    public function __construct(CompanyAuthorizedEmployee $companyAuthorizedUser)
    {
        $this->companyAuthorizedEmployee = $companyAuthorizedUser;
    }

    public function index(Request $request){
        $employees = Employee::where('company_id', $request->get('company_id'))
        ->isAuthorizedCompanyEmployee()
        ->with([
            'user:id,name',
            'authorizedDetails:employee_id,position'
        ])
        ->get([
            'id',
            'user_id'
        ])
        ->sortBy(function ($query){
            return $query->authorizedDetails->position;
        })
        ->all();

        return $this->successResponse(array_values($employees));
    }

    public function addOrUpdateAuthorizedEmployee(Request $request){
        $this->validate($request, [
            'employee_id' => [
                'required',
                Rule::exists('employees', 'id')->where('company_id', $request->get('company_id'))
            ],
            'position' => 'nullable|numeric'
        ]);
        $this->companyAuthorizedEmployee::updateOrCreate(
            [
                'employee_id' => $request->get('employee_id')
            ],
            [
                'employee_id' => $request->get('employee_id'),
                'position' => $request->get('position')
            ]
        );
        return $this->successResponse(trans('messages.saved'), 201);
    }

    public function removeEmployeeFromAuthorizedUsers(Request $request){
        $this->validate($request, [
            'employee_id' => [
                'required',
                Rule::exists('employees', 'id')->where('company_id', $request->get('company_id'))
            ],
        ]);
        $saved = $this->companyAuthorizedEmployee::where('employee_id', $request->get('employee_id'))->delete();
        return $saved
            ? $this->successResponse(trans('messages.saved'))
            : $this->errorMessage(trans('messages.not_saved'), 400);
    }
}
