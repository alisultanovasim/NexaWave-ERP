<?php

namespace Modules\Hr\Http\Controllers\Employee;

use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\DB;
use Modules\Hr\Entities\Employee\Contract;
use Modules\Hr\Entities\Employee\Employee;
use App\Traits\ApiResponse;
use App\Traits\Query;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Hr\Traits\DocumentUploader;

class ContractController extends Controller
{

    use ApiResponse, Query, DocumentUploader, ValidatesRequests;

    public function index(Request $request)
    {
        $this->validate($request, [
            'company_id' => ['required', 'integer'],
            'paginateCount' => ['integer'],
            'status' => ['sometimes', 'required', 'in:0,1,2'],
            'employee_id' => ['sometimes', 'required', 'integer'],
            'employee_status' => ['sometimes', 'required', 'in:0,1,2'],
        ]);
        try {
            $contract = Contract::with([
                'employee:id,user_id,is_active', 'employee.user:id,name,surname' , 'position:id,name'
            ])->whereHas('employee', function ($q) use ($request) {
                $q->where('company_id', $request->get('company_id'));
                if ($request->has('employee_status') and $request->get('employee_status') != 2) {
                    $q->where('is_active', $request->get('employee_status'));
                } else {
                    $q->where('is_active', true);
                }
            });

            if ($request->has('status') and $request->get('status') != 2) {
                $contract->where('is_active', $request->get('status'));
            } else {
                $contract->where('is_active', true);
            }

            $contract = $contract->paginate();

            return $this->successResponse($contract);

        } catch (\Exception $e) {
            return $this->errorResponse(trans('response.tryLater'));
        }
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'department_id' => ['sometimes', 'required', 'integer'],
            'section_id' => ['sometimes', 'required', 'integer'],
            'sector_id' => ['sometimes', 'required', 'integer'],
            'position_id' => ['required', 'integer'],
            'salary' => ['required', 'numeric'],
            'contract' => ['sometimes', 'mimes:pdf,doc,docx'],
            'start_date' => ['sometimes', 'required', 'date'],
            'end_date' => ['sometimes', 'required', 'date'],
            'employee_id' => ['required', 'integer'],
            'company_id' => ['required', 'integer']
        ]);
        DB::beginTransaction();


        if ($notExists = $this->companyInfo($request->get('company_id'), $request->only(['department_id', 'section_id', 'position_id', 'sector_id']))) return $notExists;

        $employee = Employee::where('id', $request->get('employee_id'))
            ->where('company_id', $request->get('company_id'))
            ->first(['id', 'is_active']);

        if (!$employee) return $this->errorResponse(trans('response.employeeNotFound'));

        if (!$employee->is_active) return $this->errorResponse(trans('response.employeeIsOut'));

        Contract::create(array_merge($request->only(
            [
                'department_id',
                'section_id',
                'sector_id',
                'position_id',
                'salary',
                'contract',
                'start_date',
                'end_date',
                'employee_id',
            ]
        ), [
                'employee_id' => $employee->id
            ]
        ));


        $data = $request->only([
            'department_id', 'section_id', 'sector_id', 'position_id', 'salary', 'start_date', 'end_date', 'employee_id'
        ]);


        if ($request->has('contract') and $request->hasFile('contract'))
            $data['contract'] = $this->save($request->file('contract'), $request->get('company_id'), 'contracts');


        $ctr = Contract::create($data);


        Contract::where('employee_id', $request->get('employee_id'))
            ->where('id', '!=', $ctr->id)
            ->update(['is_active' => false]);


        DB::commit();
        return $this->successResponse('ok');

    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'department_id' => ['sometimes', 'required', 'integer'],
            'section_id' => ['sometimes', 'required', 'integer'],
            'sector_id' => ['sometimes', 'required', 'integer'],
            'position_id' => ['sometimes', 'required', 'integer'],
            'salary' => ['sometimes', 'required', 'numeric'],
            'contract' => ['sometimes', 'mimes:pdf,doc,docx'],
            'start_date' => ['sometimes', 'required', 'date'],
            'end_date' => ['sometimes', 'required', 'date'],
            'employee_id' => ['required', 'integer'],
            'company_id' => ['required', 'integer'],
            'is_active' => ['sometimes' , 'required' , 'boolean']
        ]);
        $data = $request->only(['department_id', 'section_id', 'sector_id', 'position_id', 'salary', 'start_date', 'end_date', 'contract' , 'is_active']);
        if (!$data) return $this->errorResponse(trans('response.nothing'));
        try {
            DB::beginTransaction();

            if ($notExists = $this->companyInfo($request->get('company_id'), $request->only([
                'department_id', 'section_id', 'position_id', 'sector_id'
            ]))) return $notExists;

            $employee = Employee::where('id', $request->get('employee_id'))
                ->where('company_id', $request->get('company_id'))
                ->first(['id', 'is_active']);

            if (!$employee) return $this->errorResponse(trans('response.employeeNotFound'), 404);

            if (!$employee->is_active) return $this->errorResponse(trans('response.employeeNotActiveWorker'), 400);

            $contract = Contract::where('id', $id)->where('employee_id', $employee->id)->first(['position_id']);

            if (!$contract) return $this->errorResponse(trans('response.contractNotFound'), 404);
//            if (!$contract->is_active) return $this->errorResponse(trans('response.contractIsNotActive'), 404);

            if ($request->hasFile('contract'))
                $data['contract'] = $this->save($request->file('contract'), $request->get('company_id'), 'contracts');

            Contract::where('id', $id)->update($data);

            if ($request->get('is_active')) Contract::where('employee_id' , $employee->id) -> update(['is_active' , false]);

            DB::commit();
            return $this->successResponse('ok');
        } catch (\Exception $e) {
            dd($e);
            DB::rollBack();
            return $this->errorResponse(trans('response.tryLater'));
        }
    }

    public function delete(Request $request, $id)
    {
        $this->validate($request, [
            'company_id' => ['required', 'integer']
        ]);

        $employee = Employee::where('id', $request->get('employee_id'))
            ->where('company_id', $request->get('company_id'))
            ->first(['id', 'is_active']);
        if (!$employee) return $this->errorResponse(trans('response.employeeNotFound'), 404);
        if (!$employee->is_active) return $this->errorResponse(trans('response.employeeNotActiveWorker'), 400);

        $contract = Contract::where('employee_id', $employee->id)->where('id', $id)->first(['id', 'is_active']);

        if (!$contract) return $this->errorResponse(trans('response.contractNotFound'), 404);

        if ($contract->is_active) return $this->errorResponse(trans('response.cannotDeleteActiveContract'));

        $contract->delete();

        return $this->successResponse();
    }

    public function show(Request $request, $id)
    {
        $this->validate($request, [
            'company_id' => ['required', 'integer'],
        ]);
        $contract = Contract::with(['position' , 'department' , 'section' , 'sector' , 'employee:id,is_active', 'employee.user:id,name,surname'])
            ->whereHas('employee', function ($q) use ($request) {
            $q->where('company_id', $request->get('company_id'));
        })->where('id', $id)->first();
        return $this->successResponse($contract);
    }


}
