<?php

namespace Modules\Hr\Http\Controllers\Employee;

use Modules\Hr\Entities\Employee\Contract;
use Modules\Hr\Entities\Employee\Employee;
use App\Traits\ApiResponse;
use App\Traits\DocumentUploader;
use App\Traits\Query;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class ContractController extends Controller
{

    use ApiResponse, Query, DocumentUploader;

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
            $contract = Contract::with(['employee:id,human_id', 'employee.human:id,name,surname'])->whereHas('employee', function ($q) use ($request) {
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
            'from' => ['sometimes', 'required', 'date'],
            'to' => ['sometimes', 'required', 'date'],
            'employee_id' => ['required', 'integer'],
            'company_id' => ['required', 'integer']
        ]);
        try {
            \DB::beginTransaction();

            $response = "ok";

            if ($notExists = $this->companyInfo($request->get('company_id'), $request->only([
                'department_id', 'section_id', 'position_id', 'sector_id'
            ]))) return $notExists;

            $employee = Employee::where('id', $request->get('employee_id'))
                ->where('company_id', $request->get('company_id'))
                ->where('is_active', true)
                ->first(['id', 'human_id']);

            if (!$employee) return $this->errorResponse(trans('response.employeeNotFound'));


            $data = $request->only([
                'department_id', 'section_id', 'sector_id', 'position_id', 'salary', 'from', 'to', 'employee_id'
            ]);


            if ($request->has('contract') and $request->hasFile('contract'))
                $data['contract'] = $this->save($request->file('contract'), $request->get('company_id'), 'contracts');


            $ctr = Contract::create($data);


            Contract::where('employee_id', $request->get('employee_id'))
                ->where('id', '!=', $ctr->id)
                ->update(['is_active' => false]);


            if ($request->has('return_human')) $response = ['human_id' => $employee->human_id];


            \DB::commit();
            return $this->successResponse($response);
        } catch (\Exception $e) {
            \DB::rollBack();
            return $this->errorResponse(trans('response.tryLater'));
        }
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
            'from' => ['sometimes', 'required', 'date'],
            'to' => ['sometimes', 'required', 'date'],
            'employee_id' => ['required', 'integer'],
            'company_id' => ['required', 'integer'],
        ]);
        $data = $request->only(['department_id', 'section_id', 'sector_id', 'position_id', 'salary', 'from', 'to', 'contract']);
        if (!$data) return $this->errorResponse(trans('response.nothing'));
        try {
            \DB::beginTransaction();

            if ($notExists = $this->companyInfo($request->get('company_id'), $request->only([
                'department_id', 'section_id', 'position_id', 'sector_id'
            ]))) return $notExists;
            $employee = Employee::where('id', $request->get('employee_id'))
                ->where('company_id', $request->get('company_id'))
                ->where('is_active', true)
                ->first(['id', 'human_id']);
            if (!$employee) return $this->errorResponse(trans('response.employeeNotFound'), 404);

            $contract = Contract::where('employee_id', $employee->id)->where('id', $id)->first(['position_id']);


            if (!$contract) return $this->errorResponse([trans('response.contractNotFound'),$employee->id,$id], 404);

            if ($request->has('contract') and $request->hasFile('contract'))
                $data['contract'] = $this->save($request->file('contract'), $request->get('company_id'), 'contracts');

            Contract::where('id', $id)->update($data);

            \DB::commit();
            return $this->dataResponse([
                'human' => $employee->human_id,
                'change_position' => $request->has('position_id') ? $request->get('position_id') != $contract->position_id : false,
            ]);
        } catch (\Exception $e) {
            \DB::rollBack();
            return $this->errorMessage(trans('response.tryLater'));
        }
    }

    public function delete(Request $request, $id)
    {
        $this->validate($request, [
            'company_id' => ['required', 'integer']
        ]);
        try {

            $employee = Employee::where('id', $request->get('employee_id'))
                ->where('company_id', $request->get('company_id'))
                ->first(['id', 'human_id' , 'is_active']);
            if (!$employee) return $this->errorResponse(trans('response.employeeNotFound'), 404);
            if (!$employee->is_active) return $this->errorResponse(trans('response.employeeNotActiveWorker'), 400);

            $contract = Contract::where('employee_id',$employee->id)->where('id', $id)->first(['id', 'is_active']);

            if (!$contract) return $this->errorResponse(trans('response.contractNotFound'), 404);

//            if ($contract->is_active) return $this->errorResponse(trans('response.cannotDeleteActiveContract'));

            $contract->delete();

            return $this->successResponse(['is_active' => $contract->is_active , 'human_id' => $employee->human_id]);
        } catch (\Exception $e) {
            return $this->errorResponse(trans('response.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(Request $request, $id)
    {
        $this->validate($request, [
            'company_id' => ['required', 'integer'],
        ]);
        try {
            $contract = Contract::with(['employee', 'employee.human'])->whereHas('employee', function ($q) use ($request) {
                $q->where('company_id', $request->get('company_id'));
            })->where('id', $id)->first();
            return $this->successResponse($contract);
        } catch (\Exception $e) {
            return $this->errorResponse(trans('response.tryLater'));
        }
    }


}
