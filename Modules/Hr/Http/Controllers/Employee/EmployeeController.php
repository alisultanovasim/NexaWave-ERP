<?php

namespace Modules\Hr\Http\Controllers\Employee;

use Modules\Hr\Entities\Employee\Contract;
use Modules\Hr\Entities\Employee\Employee;
use Modules\Hr\Entities\Employee\Human;
use App\Traits\ApiResponse;
use App\Traits\DocumentUploader;
use App\Traits\Query;
use Doctrine\DBAL\Schema\TableDiff;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Routing\Controller;

class EmployeeController extends Controller
{
    use ApiResponse, Query, DocumentUploader;


    public function index(Request $request)
    {
        $this->validate($request, [
            'company_id' => ['required', 'integer'],
            'paginateCount' => ['sometimes', 'required', 'integer'],
//            'state' => ['nullable', 'integer', 'in:0,1,2'],
//            'name' => ['nullable', 'string', 'max:255'],
//            'department_id' => ['nullable', 'integer'],
//            'position_id' => ['nullable', 'integer'],
//            'section_id' => ['nullable', 'integer'],
//            'sector_id' => ['nullable', 'integer'],
        ]);
        try {

//            if ($notExists = $this->companyInfo($request->get('company_id'), $request->only([
//                'department_id', 'profession_id', 'section_id', 'sector_id'
//            ]))) return $this->errorResponse($notExists);

            $employees = Employee::with(['user:id,name,surname', 'contracts' => function ($q) {
                $q->with(['department:id,name', 'section:id,name', 'sector:id,name', 'position:id,name'])->where('is_active', true)->select();
            }])->where('company_id', $request->get('company_id'));

            if ($request->has('state') and $request->get('state') != '2')
                $employees->where('is_active', $request->get('state'));
            else $employees->where('is_active', true);

            if ($request->has('name'))
                $employees->whereHas('human', function ($q) use ($request) {
                    $q->where('name', 'like', $request->get('name') . "%");
                });

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
        try {

            $employees = Employee::with(['human', 'contracts', 'contracts.department', 'contracts.section', 'contracts.sector', 'contracts.position'])
                ->where('id', $id)
                ->where('company_id', $request->get('company_id'))
                ->first();

            if (!$employees) return $this->errorResponse(trans('response.employeeNotFound'), Response::HTTP_NOT_FOUND);

            return $this->successResponse($employees);
        } catch (\Exception $exception) {
            return $this->errorResponse(trans('response.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'company_id' => ['required', 'integer'],
            'is_active' => ['sometimes', 'required', 'boolean'],
            'human_id' => ['sometimes', 'required', 'integer'],

            'human' => ['required_without:human_id', 'array'],

            'contract' => ['sometimes', 'required', 'file', 'mimes:pdf,doc,docx'],
            'department_id' => ['sometimes', 'required', 'integer'],
            'section_id' => ['sometimes', 'required', 'integer'],
            'sector_id' => ['sometimes', 'required', 'integer'],
            'position_id' => ['required', 'integer'],
            'salary' => ['required', 'numeric'],
            'from' => ['sometimes', 'required', 'date'],
            'to' => ['sometimes', 'required', 'date'],

        ]);
        try {


            DB::beginTransaction();

            if ($request->has('human_id'))
                $human_id = $request->get('human_id');
            else {
                (new HumanController)->validateStore($request->get('human'));
                $human = Human::create($request->get('human'));
                $human_id = $human->id;
            }

            $employee = Employee::create($request->except(['human', 'contract']) + ['human_id' => $human_id]);
            $checkingData = $request->only([
                'department_id', 'section_id', 'sector_id', 'position_id'
            ]);
            if ($no = $this->companyInfo($request->get('company_id'), $checkingData))
                return $this->errorResponse($no);

            if ($request->hasFile('contract'))
                $checkingData['contract'] = $this->save($request->file('contract'), $request->get('company_id'), 'contracts');
            Contract::create(
                $checkingData +
                $request->only('salary', 'from', 'to') +
                [
                    'employee_id' => $employee->id
                ]
            );
            DB::commit();
            return $this->dataResponse(['human' => [
                'id' => $human_id,
            ]]);
        } catch (ValidationException $v) {
            DB::rollBack();
            return $this->errorResponse($v->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (QueryException  $exception) {
            DB::rollBack();
            if ($exception->errorInfo[1] == 1062)
                return $this->errorResponse(['fin' => trans('response.alreadyExists')], 422);
            return $this->errorResponse(trans('response.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->errorResponse(trans('response.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(Request $request, $id)
    {
//        $this->validate($request, [
//            'is_active' => ['sometimes', 'required', 'boolean'],
//            'company_id' => ['required', 'integer']
//        ]);
//        $data = $request->only(['is_active']);
//        if (!$data) return $this->errorResponse(trans('response.nothing'));
//        try {
//            Employee::where('company_id', $request->get('company_id'))
//                ->where('id', $id)
//                ->update($data);
//            return $this->successResponse('ok');
//        } catch (\Exception $exception) {
//            return $this->errorResponse(trans('response.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
//        }
    }

    public function makeOut(Request $request , $id){
        $this->validate($request, [
            'company_id' => ['required', 'integer']
        ]);
        DB::beginTransaction();
        $employee = Employee::where('company_id' , $request->get('company_id'))
            ->where('id' ,$id)
            ->first(['is_active' , 'human_id']);
        if (!$employee) return $this->errorResponse(trans('response.employeeNotFound') ,404);
        if (!$employee->is_active) return $this->errorResponse(trans('response.employeeAlreadyOut') ,400);

        Employee::where('id' , $id) -> update(['is_active' => false]);

        Contract::where('employee_id' , $id) -> update (['is_active' => false]);
        DB::commit();
        return $this->successResponse(['human_id' => $employee->human_id]);
    }

    public function makeIn(Request $request , $id){
        $this->validate($request, [
            'company_id' => ['required', 'integer'],
        ]);
        DB::beginTransaction();
        $employee = Employee::where('company_id' , $request->get('company_id'))
            ->where('id' ,$id)
            ->first(['is_active']);
        if (!$employee) return $this->errorResponse(trans('response.employeeNotFound') ,404);
        if ($employee->is_active) return $this->errorResponse(trans('response.employeeAlreadyIn') ,400);

        Employee::where('id' , $id) -> update(['is_active' => true]);

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
            ->first(['id' , 'is_active' , 'human_id']);
        if (!$employee) return $this->errorResponse(trans('response.employeeNotFound'));

        $employee->delete();
        return $this->successResponse(['is_active' => $employee->is_active , 'human_id' => $employee->human_id]);
    } catch (\Exception $exception) {
        return $this->errorResponse(trans('response.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}


}
