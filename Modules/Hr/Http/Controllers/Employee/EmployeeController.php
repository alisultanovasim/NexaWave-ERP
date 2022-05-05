<?php

namespace Modules\Hr\Http\Controllers\Employee;

use App\Http\Controllers\Auth\UserController;
use App\Models\User;
use App\Models\UserRole;
use App\Traits\ApiResponse;
use App\Traits\Query;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Modules\Hr\Emails\EmployeeCreate;
use Modules\Hr\Entities\Employee\Contract;
use Modules\Hr\Entities\Employee\Employee;
use Modules\Hr\Traits\DocumentUploader;
use Throwable;

/**
 * Class EmployeeController
 * @package Modules\Hr\Http\Controllers\Employee
 */
class EmployeeController extends Controller
{
    use ApiResponse, Query, DocumentUploader, ValidatesRequests;

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function index(Request $request)
    {

        $this->validate($request, [
            'company_id' => ['required', 'integer'],
            'per_page' => ['sometimes', 'required', 'integer'],
            'state' => ['nullable', 'integer', 'in:0,1,2'],
            'name' => ['nullable', 'string', 'max:255'],
            'department_id' => ['nullable', 'integer'],
            'position_id' => ['nullable', 'integer'],
            'section_id' => ['nullable', 'integer'],
            'sector_id' => ['nullable', 'integer'],
            'is_filter' => ['nullable', 'boolean'],
            "tabel_no" => ['nullable', 'string', 'max:255'],
            'order_by' => [
                'nullable', Rule::in(['employee_contracts.start_date'])
            ],
        ]);
        $orderBy = $request->get('order_by') ?? 'employees.id';
        $sortBy = $request->get('sort_by') == 'asc' ? 'asc' : 'desc';

        if ($notExists = $this->companyInfo($request->get('company_id'), $request->only(['profession_id'])))
            return $this->errorResponse($notExists);

        $employees = Employee::query()
            ->where('company_id', $request->get('company_id'))
            ->with("contracts")
            ->whereHas('contract', function ($q) use ($request) {
                $q->where('is_terminated', 0);
            });
        //            ->join('employee_contracts', 'employees.id', 'employee_contracts.employee_id');

        //        dd($employees->get()->toArray());

//        if ($request->has('state') and $request->get('state') != '2')
//            $employees->where('employees.is_active', $request->get('state'));
//        else
//            $employees->where('employees.is_active', true);

        if ($request->get('department_id'))
            $employees->whereHas('contract', function ($q) use ($request) {
                $q->where('department_id', $request->get('department_id'));
            });

        if ($request->get('section_id'))
            $employees->whereHas('contract', function ($q) use ($request) {
                $q->where('section_id', $request->get('section_id'));
            });

        if ($request->get('sector_id'))
            $employees->whereHas('contract', function ($q) use ($request) {
                $q->where('sector_id', $request->get('sector_id'));
            });
        if ($request->get('tabel_no'))
            $employees->where('tabel_no', 'like', $request->get('tabel_no') . "%");

        if ($request->get('is_filter')) {
            $employees = $employees->with([
                'user:id,name,surname',
                'contract:id,employee_id,position_id',
                'contract.position:id,name'
            ])
                ->orderBy($orderBy, $sortBy)
                ->take(50)
                ->get(['employees.id', 'employees.user_id', 'employees.company_id']);
            return $this->successResponse(['data' => $employees]);
        }

        $employees = $employees->with([
            'user:id,name,surname',
            'user.details:user_id,father_name,gender',
            'contracts',
            'contracts.position',
            'contracts.currency'
        ])
            ->where('is_active', 1)
            ->orderBy($orderBy, $sortBy)
            ->paginate($request->input('per_page', 200), ['employees.*']);

        return $this->successResponse($employees);
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     * @throws ValidationException
     */
    public function show(Request $request, $id)
    {
        $this->validate($request, [
            'company_id' => ['required', 'integer'],
        ]);
        $employees = Employee::with([
            'user',
            'contracts',
            'contracts.position',
            'contracts.department:id,name',
            'contracts.section:id,name',
            'contracts.sector:id,name',
            'contracts.specializationDegree',
            'contracts.contract_type',
            'contracts.personalCategory',
            'user.roles:user_roles.user_id,user_roles.role_id',
            'user.details',
            'user.details.nationality',
            'user.details.citizen',
            'user.details.birthdayCity',
            'user.details.birthdayCountry',
            'user.details.birthdayRegion',
            'user.details.eyeColor',
            'user.details.birthdayRegion',
            'user.details.birthdayRegion',
            'user.details.birthdayCountry',
            'user.details.birthdayCity',
            'user.details.blood',
            'user.details.familyStatusState',
        ])
            ->where('id', $id)
            ->where('company_id', $request->get('company_id'))
            ->first();

        if (!$employees) {
            return $this->errorResponse(trans('response.employeeNotFound'), Response::HTTP_NOT_FOUND);
        }
        return $this->successResponse($employees);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     * @throws Throwable
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'company_id' => ['required', 'integer'],
            'is_active' => ['sometimes', 'required', 'boolean'],
            'user_id' => ['sometimes', 'required', 'integer'],
            'create_contract' => ['required', 'boolean'],
            'tabel_no' => ['required', 'string', 'max:255'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => [
                Rule::exists('roles', 'id')->where('company_id', $request->get('company_id'))
            ],
            'name'=>'required|min:3|max:77',
            'surname'=>'required|min:3|max:77',
            'father_name'=>'required|min:3|max:77',
//            'date_of_birth' => 'required|date|before:'.now()->subYears(16)->toDateString(),
            'gender'=> 'required|in:m,f',
//            'citizenship'=>'required',
//            'id_seria'=>'required|string',
//            'id_number'=>'required|integer',
//            'id_fin'=>'required|string',
//            'institution'=>'required|string',
//            'date_of_issue'=>'required|date',
//            'validity_date'=>'required|date',
            'email'=>'required|email|unique:users,email'


        ]);
        try {
            DB::beginTransaction();

            if ($request->has('user_id')) {
                $user = User::where('id', $request->get('user_id'))->first(['id']);
                if (!$user)
                    return $this->errorResponse(trans('response.userNotFound'));
            } else {
                $user = UserController::createUser($request);
            }

            $this->setUserRoles($request->get('roles'), $user->id, $request->get('company_id'));

            $employee = Employee::create([
                'company_id' => $request->get('company_id'),
                'user_id' => $user->id,
                'tabel_no' => $request->get('tabel_no')
            ]);

            DB::commit();

             Mail::to($request->input('email'))->send(new EmployeeCreate($user));

            return $this->successResponse([
                'Username:' => $user->username,
                'Password:' => $user->password,
            ]);


        } catch (QueryException  $exception) {
            if ($exception->errorInfo[1] == 1062) {
                if (strpos($exception->errorInfo[2], 'employees_user_id_company_id_unique') !== false)
                    return $this->errorResponse(['user_id' => trans('response.userAlreadyWorkOn')], 422);
                return $this->errorResponse(['fin' => trans('response.alreadyExists')], 422);
            }
            if ($exception->errorInfo[1] == 1452)
                return $this->errorResponse([trans('response.SomeFiledIsNotFoundInDatabase')], 422);
            return $this->errorResponse(trans('response.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param $roles
     * @param $userId
     * @param $companyId
     */
    private function setUserRoles($roles, $userId, $companyId): void
    {
        $insertData = [];
        foreach ($roles as $role) {
            $insertData[] = [
                'user_id' => $userId,
                'company_id' => $companyId,
                'role_id' => $role,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ];
        }

        UserRole::where(['user_id' => $userId, 'company_id' => $companyId])->delete();
        UserRole::insert($insertData);
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     * @throws ValidationException
     * @throws Throwable
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'is_active' => ['sometimes', 'required', 'boolean'],
            'company_id' => ['required', 'integer'],
            'update_user' => ['sometimes', 'boolean'],
            'roles' => ['nullable', 'array', 'min:1'],
            'roles.*' => [
                Rule::exists('roles', 'id')->where('company_id', $request->get('company_id'))
            ]
        ]);

        $data = $request->only(['is_active', 'update_user']);
        if (!$data) return $this->errorResponse(trans('response.nothing'));

        DB::beginTransaction();

        $employee = Employee::where('company_id', $request->get('company_id'))
            ->where('id', $id)
            ->first(['id', 'user_id', 'is_active']);

        if (!$employee) return $this->errorResponse(trans('response.employeeNotFound'), 404);

        if ($request->get('roles'))
            $this->setUserRoles($request->get('roles'), $employee->user_id, $request->get('company_id'));

        $data = $request->only(['is_active', 'tabel_no']);

        if (count($data) != 0)
            Employee::where('id', $id)->update($data);

        if ($request->get('update_user'))
            UserController::updateUser($request, $employee->user_id);

        DB::commit();
        return $this->successResponse('ok');
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     * @throws ValidationException
     * @throws Throwable
     */
    public function delete(Request $request, $id)
    {
        $this->validate($request, [
            'company_id' => ['required', 'integer']
        ]);
        $employee = Employee::where('company_id', $request->get('company_id'))
            ->where('id', $id)
            ->firstOrFail(['id', 'user_id']);
        DB::transaction(function () use ($request, $employee) {
            $employee->delete();
            UserRole::where([
                'user_id' => $employee->user_id,
                'company_id' => $request->get('company_id')
            ])->delete();
        });
        return $this->successResponse('ok');
    }


    public function getEmployeeWithStructureData(Request $request, $id)
    {
        $employee = Employee::where([
            'id' => $id,
            'company_id' => $request->get('company_id'),
            'is_active' => 1
        ])
            ->select([
                'id',
                'user_id',
                'tabel_no'
            ])
            ->with([
                'user:id,name,surname',
                'user.details:user_id,father_name'
            ])
            ->firstOrFail();
        return $this->successResponse($employee);
    }

    public function setAuthorizedEmployees(Request $request)
    {
        $this->validate($request, [
            'employee_ids' => 'required|array'
        ]);

        DB::transaction(function () use ($request) {
            Employee::where('company_id', $request->get('company_id'))
                ->whereIn('id', $request->get('employee_ids'))->update([
                    'is_authorized_employee' => 1
                ]);
            Employee::where('company_id', $request->get('company_id'))
                ->whereNotIn('id', $request->get('employee_ids'))->update([
                    'is_authorized_employee' => 0
                ]);
        });

        return $this->successResponse(trans('messages.saved'), 200);
    }
}
