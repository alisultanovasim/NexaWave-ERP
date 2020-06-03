<?php

namespace Modules\Hr\Http\Controllers\Employee;

use App\Http\Controllers\Auth\UserController;
use App\Models\User;
use App\Models\UserRole;
use Carbon\Carbon;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Validation\Rule;
use Modules\Hr\Entities\Employee\Employee;
use App\Traits\ApiResponse;
use App\Traits\Query;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controller;
use Modules\Hr\Traits\DocumentUploader;

class EmployeeController extends Controller
{
    use ApiResponse, Query, DocumentUploader, ValidatesRequests;

    public function index(Request $request)
    {
        $this->validate($request, [
            'company_id' => ['required', 'integer'],
            'paginateCount' => ['sometimes', 'required', 'integer'],
            'state' => ['nullable', 'integer', 'in:0,1,2'],
            'name' => ['nullable', 'string', 'max:255'],
            'department_id' => ['nullable', 'integer'],
            'position_id' => ['nullable', 'integer'],
            'section_id' => ['nullable', 'integer'],
            'sector_id' => ['nullable', 'integer'],
            'is_filter' => ['nullable' , 'boolean'],
            "tabel_no" => ['nullable' , 'string' , 'max:255']
        ]);

        if ($notExists = $this->companyInfo($request->get('company_id'), $request->only(['profession_id']))) return $this->errorResponse($notExists);

        $employees = Employee::where('company_id', $request->get('company_id'));

        if ($request->has('state') and $request->get('state') != '2')
            $employees->where('is_active', $request->get('state'));
        else $employees->where('is_active', true);

        if ($request->get('department_id'))
            $employees->whereHas('contract' , function ($q) use ($request){
                $q->where('department_id', $request->get('department_id'));
            });

        if ($request->get('section_id'))
            $employees->whereHas('contract' , function ($q) use ($request){
                $q->where('section_id', $request->get('section_id'));
            });

        if ($request->get('sector_id'))
            $employees->whereHas('contract' , function ($q) use ($request){
                $q->where('sector_id', $request->get('sector_id'));
            });
        if ($request->get('tabel_no'))
            $employees->where('tabel_no' , 'like' , $request->get('tabel_no')."%");

        if ($request->get('is_filter')) {
            $employees = $employees->with([
                'user:id,name,surname',
                'contract:id,employee_id,position_id',
                'contract.position:id,name'
            ])->orderBy('id', 'desc')->take(50)->get(
                ['id', 'user_id', 'company_id']
            );
            return $this->successResponse(['data' => $employees]);
        }

        $employees = $employees->with([
                'user:id,name,surname',
                'user.details:user_id,father_name,gender',
                'contracts',
                'contracts.position',
                'contracts.currency'
            ])
//            ->orderBy('id' , 'desc')
            ->paginate($request->get('paginateCount'));

        return $this->successResponse($employees);

    }

    public function show(Request $request, $id)
    {
        $this->validate($request, [
            'company_id' => ['required', 'integer'],
        ]);
        $employees = Employee::with([
            'user',
            'user.roles:user_roles.user_id,user_roles.role_id',
            'user.details',
            'user.details.nationality',
            'user.details.citizen',
            'user.details.birthdayCity',
            'user.details.birthdayCountry',
            'user.details.birthdayRegion',
        ])
            ->where('id', $id)
            ->where('company_id', $request->get('company_id'))
            ->first();

        if (!$employees) return $this->errorResponse(trans('response.employeeNotFound'), Response::HTTP_NOT_FOUND);

        return $this->successResponse($employees);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'company_id' => ['required', 'integer'],
            'is_active' => ['sometimes', 'required', 'boolean'],
            'user_id' => ['sometimes', 'required', 'integer'],
            'create_contract' => ['required', 'boolean'],
            'tabel_no' => ['nullable' , 'string' , 'max:255'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => [
                Rule::exists('roles', 'id')->where('company_id', $request->get('company_id'))
            ]
        ]);
        try {
            DB::beginTransaction();

            if ($request->has('user_id')) {
                $user = User::where('id', $request->get('user_id'))->first(['id']);
                if (!$user) return $this->errorResponse(trans('response.userNotFound'));
            } else {
                $user = UserController::createUser($request);
            }

            $this->setUserRoles($request->get('roles'), $user->id, $request->get('company_id'));

            $employee = Employee::create([
                'company_id' => $request->get('company_id'),
                'user_id' => $user->id,
                'tabel_no' => $request->get('tabel_no')
            ]);

            if ($request->get('create_contract')) {

                $this->validate($request, ContractController::getValidateRules());

                $relations = $request->only(['department_id', 'section_id', 'sector_id', 'position_id']);

                if ($notExists = $this->companyInfo($request->get('company_id'), $relations)) return $this->errorResponse($notExists , 422);

                $request->request->set('employee_id', $employee->id);

                ContractController::storeContract($request);
            }
            DB::commit();
            return $this->successResponse('ok');
        } catch (QueryException  $exception) {
            if ($exception->errorInfo[1] == 1062){
                if (strpos($exception->errorInfo[2], 'employees_user_id_company_id_unique') !== false)
                    return $this->errorResponse(['user_id' => trans('response.userAlreadyWorkOn')], 422);
                return $this->errorResponse(['fin' => trans('response.alreadyExists')], 422);
            }
            if ($exception->errorInfo[1] == 1452)
                return $this->errorResponse([trans('response.SomeFiledIsNotFoundInDatabase')], 422);
            return $this->errorResponse(trans('response.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function setUserRoles($roles, $userId, $companyId): void {
        $insertData = [];
        foreach ($roles as $role){
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

        $data = $request->only(['is_active' , 'tabel_no']);

        if (count($data) != 0)
            Employee::where('id', $id)->update($data);

        if ($request->get('update_user'))
            UserController::updateUser($request, $employee->user_id);

        DB::commit();
        return $this->successResponse('ok');
    }

    //todo create left-job logic
    //todo delete user roles in this company

    public function delete(Request $request, $id)
    {
        $this->validate($request, [
            'company_id' => ['required', 'integer']
        ]);
        try {
            $employee = Employee::where('company_id', $request->get('company_id'))
                ->where('id', $id)
                ->exists();
            if (!$employee) return $this->errorResponse(trans('response.employeeNotFound'));

            Employee::where('id', $id)->delete();
            UserRole::where([
                'user_id' => $employee->user_id,
                'company_id' => $request->get('company_id')
            ])->delete();

            return $this->successResponse('ok');
        } catch (\Exception $exception) {
            return $this->errorResponse(trans('response.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


}
