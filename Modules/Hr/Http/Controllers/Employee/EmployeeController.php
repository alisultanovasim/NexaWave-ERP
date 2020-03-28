<?php

namespace Modules\Hr\Http\Controllers\Employee;

use App\Http\Controllers\Auth\UserController;
use App\Jobs\SendMailCreatePassword;
use App\Models\User;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Modules\Hr\Entities\Employee\Contract;
use Modules\Hr\Entities\Employee\Employee;
use App\Traits\ApiResponse;
use App\Traits\Query;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Routing\Controller;
use Modules\Hr\Traits\DocumentUploader;
use Modules\Plaza\Entities\Contact;

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
        ]);
        try {

            if ($notExists = $this->companyInfo($request->get('company_id'), $request->only(['profession_id']))) return $this->errorResponse($notExists);

            $employees = Employee::with(['user:id,name,surname', 'contracts' => function ($q) {
                $q->with(['department:id,name', 'section:id,name', 'sector:id,name', 'position:id,name'])->active();
            }])->where('company_id', $request->get('company_id'));

            if ($request->has('state') and $request->get('state') != '2')
                $employees->where('is_active', $request->get('state'));
            else $employees->where('is_active', true);

            $employees = $employees->paginate($request->get('paginateCount'));

            return $this->successResponse($employees);
        } catch (\Exception $exception) {
            return $this->errorResponse(trans('response.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(Request $request, $id)
    {
        $this->validate($request, [
            'company_id' => ['required', 'integer'],
        ]);
        $employees = Employee::with([
            'user', 'contracts', 'contracts.department:id,name', 'contracts.section:id,name', 'contracts.sector:id,name', 'contracts.position:id,name'
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
            'contract' => ['sometimes', 'required', 'file', 'mimes:pdf,doc,docx'],
            'department_id' => ['sometimes', 'required', 'integer'],
            'section_id' => ['sometimes', 'required', 'integer'],
            'sector_id' => ['sometimes', 'required', 'integer'],
            'position_id' => ['required', 'integer'],
            'salary' => ['required', 'numeric'],
            'from' => ['sometimes', 'required', 'date'],
            'to' => ['sometimes', 'required', 'date'],
        ]);
        $created = false;
        try {
            DB::beginTransaction();

            if ($request->has('user_id')) {
                $user = User::where('id', $request->get('user_id'))->first(['id']);
                if (!$user) return $this->errorResponse(trans('response.userNotFound'));
            } else{
                $created = true;
                $user = UserController::createUser($request);
            }

            $employee = Employee::create([
                'company_id' => $request->get('company_id'),
                'user_id' => $user->id,
            ]);

            $relations = $request->only(['department_id', 'section_id', 'sector_id', 'position_id']);

            if ($notExists = $this->companyInfo($request->get('company_id'), $relations)) return $this->errorResponse($notExists);

            if ($request->hasFile('contract'))
                $relations['contract'] = $this->save($request->file('contract'), $request->get('company_id'), 'contracts');

            $relations['employee_id'] = $employee->id;

            Contract::create(array_merge($relations, $request->only(['salary', 'start_date', 'end_date'])));



            if ($created) SendMailCreatePassword::dispatch([
                'password' => $user->generatedPassword ,
                'username' => $user->username,
                'name' => $user->name,
                'surname' => $user->username,
                'email' => $user->email
            ]);


            DB::commit();


            return $this->successResponse('ok');
        } catch (QueryException  $exception) {

            DB::rollBack();
            if ($exception->errorInfo[1] == 1062)
                return $this->errorResponse(['fin' => trans('response.alreadyExists')], 422);

            return $this->errorResponse(trans('response.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'is_active' => ['sometimes', 'required', 'boolean'],
            'company_id' => ['required', 'integer'],
            'update_user' => ['sometimes' , 'boolean']
        ]);
        $data = $request->only(['is_active' , 'update_user']);
        if (!$data) return $this->errorResponse(trans('response.nothing'));

        DB::beginTransaction();

        $employee = Employee::where('company_id', $request->get('company_id'))
            ->where('id', $id)
            ->first(['id', 'user_id', 'is_active']);

        if (!$employee) return $this->errorResponse(trans('response.employeeNotFound'), 404);
//         todo
//        if ($request->has('is_active'))
//           if (!$request->get('is_active') and $employee->is_active)
//                Contact::where('employee_id', $employee->id)->update(['is_active' => false]);

        Employee::where('id', $id)->update($request->get('is_active'));

        if ($request->get('update_user')){
            //todo some condition
            UserController::updateUser($request , $employee->use_id);
        }

        DB::commit();
        return $this->successResponse('ok');
    }

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

            return $this->successResponse('ok');
        } catch (\Exception $exception) {
            return $this->errorResponse(trans('response.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }



}
