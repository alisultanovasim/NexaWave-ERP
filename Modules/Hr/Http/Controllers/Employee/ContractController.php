<?php

namespace Modules\Hr\Http\Controllers\Employee;

use Illuminate\Database\QueryException;
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

    public static function storeContract(Request $request)
    {
        $data = $request->only([
            'department_id' ,
            'section_id' ,
            'sector_id' ,
            'position_id' ,
            'salary' ,
            'contract' ,
            'start_date' ,
            'end_date' ,
            'employee_id' ,
            'personal_category_id' ,
            'specialization_degree_id' ,
            'work_environment_id' ,
            'state_value' ,
            'intern_start_at' ,
            'intern_end_at' ,
            'currency_id' ,
            'contract_type_id' ,
            'work_start_at' ,
            'work_end_at' ,
            'break_time_start' ,
            'break_time_end' ,
            'work_time_start_at' ,
            'work_time_end_at' ,
            'contract_no' ,
            'acceptor_id' ,
            'duration_type_id' ,
            'labor_protection_addition' ,
            'labor_meal_addition' ,
            'labor_sport_addition' ,
            'vacation_main' ,
            'vacation_work_insurance' ,
            'vacation_work_envs' ,
            'vacation_for_child' ,
            'vacation_collective_contract' ,
            'vacation_total' ,
            'vacation_social_benefits' ,
        ]);


        if ($request->has('contract') and $request->hasFile('contract'))
            $data['contract'] = self::save($request->file('contract'), $request->get('company_id'), 'contracts');

        return Contract::create($data);
    }

    public static function getValidateRules()
    {
        return [
            'department_id' => ['sometimes', 'required', 'integer'], //exists m
            'section_id' => ['sometimes', 'required', 'integer'], //exists m
            'sector_id' => ['sometimes', 'required', 'integer'],//exists m
            'position_id' => ['required', 'integer'],//exists m
            'salary' => ['required', 'numeric'],
            'contract' => ['sometimes', 'mimes:pdf,doc,docx'],
            'start_date' => ['required', 'date', 'date_format:Y-m-d'],
            'end_date' => ['required', 'date', 'date_format:Y-m-d'],
            'employee_id' => ['required', 'integer'],//exists m
            'personal_category_id' => ['nullable', 'integer'],//exists
            'specialization_degree_id' => ['nullable', 'integer'],//exists
            'work_environment_id' => ['nullable', 'integer'],//exists
            'state_value' => ['nullable', 'numeric'],
            'intern_start_at' => ['nullable', 'date'],
            'intern_end_at' => ['nullable', 'time'],
            'currency_id' => ['required', 'integer'],//exists
            'contract_type_id' => ['nullable', 'integer'],//exists
            'work_start_at' => ['nullable', 'date_format:Y-m-d'],
            'work_end_at' => ['nullable', 'date_format:Y-m-d'],
            'break_time_start' => ['nullable', 'date_format:H:i'],
            'break_time_end' => ['nullable', 'date_format:H:i'],
            'work_time_start_at' => ['nullable', 'date_format:H:i'],
            'work_time_end_at' => ['nullable', 'date_format:H:i'],
            'contract_no' => ['required', 'string', 'max:255'],
            'acceptor_id' => ['required', 'integer', 'min:1'], //exists
            'duration_type_id' => ['required', 'integer', 'min:1'], //exists
            'labor_protection_addition' => ['nullable', 'string'],
            'labor_meal_addition' => ['nullable', 'string'],
            'labor_sport_addition' => ['nullable', 'string'],
            'vacation_main' => ['nullable', 'integer'],
            'vacation_work_insurance' => ['nullable', 'integer'],
            'vacation_work_envs' => ['nullable', 'integer'],
            'vacation_for_child' => ['nullable', 'integer'],
            'vacation_collective_contract' => ['nullable', 'integer'],
            'vacation_total' => ['nullable', 'integer'],
            'vacation_social_benefits' => ['nullable', 'numeric'],
        ];
    }

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
                'employee:id,user_id,is_active',
                'employee.user:id,name,surname',
                'position:id,name',
                'section:id,name,short_name',
                'sector:id,name,short_name',
                'department:id,name,short_name',
                'currency'
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
        $this->validate($request, self::getValidateRules());

        try {

            DB::beginTransaction();

            if ($notExists = $this->companyInfo($request->get('company_id'), $request->only(['department_id', 'section_id', 'position_id', 'sector_id']))) return $notExists;

            $employee = Employee::where('id', $request->get('employee_id'))
                ->where('company_id', $request->get('company_id'))
                ->first(['id', 'is_active']);

            if (!$employee) return $this->errorResponse(trans('response.employeeNotFound'));

            if (!$employee->is_active) return $this->errorResponse(trans('response.employeeIsOut'));


            $relations = $request->only(['department_id', 'section_id', 'sector_id', 'position_id']);

            if ($notExists = $this->companyInfo($request->get('company_id'), $relations)) return $this->errorResponse($notExists);

            $newContract = self::storeContract($request);


            Contract::where('employee_id', $request->get('employee_id'))
                ->where('id', '!=', $newContract->id)
                ->update(['is_active' => false]);


            DB::commit();
            return $this->successResponse('ok');
        } catch (QueryException $exception) {
            if ($exception->errorInfo[1] == 1452)
                return $this->errorResponse([trans('response.SomeFiledIsNotFoundInDatabase')], 422);
            dd($exception);
            return $this->errorResponse(trans('response.tryLater'), 500);
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
            'start_date' => ['sometimes', 'required', 'date'],
            'end_date' => ['sometimes', 'required', 'date'],
            'employee_id' => ['required', 'integer'],
            'company_id' => ['required', 'integer'],
            'is_active' => ['sometimes', 'required', 'boolean']
        ]);
        $data = $request->only(['department_id', 'section_id', 'sector_id', 'position_id', 'salary', 'start_date', 'end_date', 'contract', 'is_active']);
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

            if ($request->get('is_active')) Contract::where('employee_id', $employee->id)->update(['is_active', false]);

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
        $contract = Contract::with([
            'position',
            'section:id,name,short_name',
            'sector:id,name,short_name',
            'department:id,name,short_name',
            'employee:id,is_active,user_id',
            'employee.user:id,name,surname',
            'contract_type' ,
            'duration_type',
            'acceptor:id,is_active,user_id',
            'acceptor.user:id,name,surname',
        ])
            ->whereHas('employee', function ($q) use ($request) {
                $q->where('company_id', $request->get('company_id'));
            })->where('id', $id)->first();
        return $this->successResponse($contract);
    }

}
