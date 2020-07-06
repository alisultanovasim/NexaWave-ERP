<?php

namespace Modules\Hr\Http\Controllers\Employee;

use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Modules\Hr\Entities\CompanyAuthorizedEmployee;
use Modules\Hr\Entities\Employee\Contract;
use Modules\Hr\Entities\Employee\Employee;
use App\Traits\ApiResponse;
use App\Traits\Query;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Hr\Traits\DocumentUploader;

class ContractController extends Controller
{

    use ApiResponse, Query, DocumentUploader, ValidatesRequests;

    public static function storeContract(Request $request)
    {
        $data = $request->only([
            'draft',
            'department_id',
            'section_id',
            'sector_id',
            'position_id',
            'start_date',
            'end_date',
            'employee_id',
            'personal_category_id',
            'specialization_degree_id',
            'work_environment_id',
            'state_value',
            'intern_start_date',
            'intern_end_date',
            'currency_id',
            'contract_type_id',
            'work_start_date',
            'work_end_date',
            'break_time_start',
            'break_time_end',
            'work_time_start_at',
            'work_time_end_at',
            'contract_no',
            'duration_type_id',
            'labor_protection_addition',
            'labor_meal_addition',
            'labor_sport_addition',
            'vacation_main',
            'vacation_work_insurance',
            'vacation_work_envs',
            'vacation_for_child',
            'vacation_collective_contract',
            'vacation_total',
            'vacation_social_benefits',
            'vacation_start_date',
            'vacation_end_date',
            'contract_sing_date',
            'company_authorized_employee_id',
            'contract_id',
            'position_salary_praise_about',
            'addition_package_fee',
            'award_amount',
            'award_period',
            'work_environment_addition',
            'overtime_addition',
            'incomplete_work_hours',
            'work_days_in_week',
            'work_shift_count',
            'first_shift_start_at',
            'first_shift_end_at',
            'second_shift_start_at',
            'second_shift_end_at',
            'third_shift_start_at',
            'third_shift_end_at',
            'social_amount',
            'addition_social_amount',
            'company_share',
            'dividend_amount',
            'user_personal_property',
            'provided_transport',
            'res_days',
        ]);
        return Contract::create($data + [
                'salary' =>
                    +$request->get('position_salary_praise_about') +
                    +$request->header('addition_package_fee') +
                    +$request->get('award_amount') +
                    +$request->get('work_environment_addition') +
                    +$request->get('overtime_addition')
            ]);
    }

    public static function getValidateRules()
    {
        return [
            'draft'=>['sometimes' , 'boolean'],
            'department_id' => ['sometimes', 'required', 'integer'], //exists m
            'section_id' => ['sometimes', 'required', 'integer'], //exists m
            'sector_id' => ['sometimes', 'required', 'integer'],//exists m
            'position_id' => ['required', 'integer'],//exists m
//            'salary' => ['required', 'numeric'],
            'start_date' => ['required', 'date', 'date_format:Y-m-d'],
            'end_date' => ['required', 'date', 'date_format:Y-m-d'],
            'employee_id' => ['required', 'integer'],//exists m
            'personal_category_id' => ['nullable', 'integer'],//exists
            'specialization_degree_id' => ['nullable', 'integer'],//exists
            'work_environment_id' => ['nullable', 'integer'],//exists
            'state_value' => ['nullable', 'numeric'],
            'intern_start_date' => ['nullable', 'date'],
            'intern_end_date' => ['nullable', 'date'],
            'currency_id' => ['required', 'integer'],//exists
            'contract_type_id' => ['nullable', 'integer'],//exists
            'work_start_date' => ['nullable', 'date_format:Y-m-d'],
            'work_end_date' => ['nullable', 'date_format:Y-m-d'],
            'break_time_start' => ['nullable', 'date_format:H:i'],
            'break_time_end' => ['nullable', 'date_format:H:i'],
            'work_time_start_at' => ['nullable', 'date_format:H:i'],
            'work_time_end_at' => ['nullable', 'date_format:H:i'],
            'contract_no' => ['required', 'string', 'max:255'],
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
            'vacation_start_date' => ['nullable', 'date_format:Y-m-d'],
            'vacation_end_date' => ['nullable', 'date_format:Y-m-d'],
            'contract_sing_date' => ['nullable', 'date_format:Y-m-d'],
            'company_authorized_employee_id' => ['nullable', 'integer'],
            'contract_id' => ['nullable', 'integer'],
            'position_salary_praise_about' => ['nullable', 'numeric'],
            'addition_package_fee' => ['nullable', 'numeric', "min:0", "max:100"],
            'award_amount' => ['nullable', 'numeric'],
            'award_period' => ['nullable', Rule::in(Contract::AWARD_PERIODS)],
            'work_environment_addition' => ['nullable', 'numeric'],
            'overtime_addition' => ['nullable', 'numeric'],
            'incomplete_work_hours' => ['nullable', 'numeric', "min:0", 'max:12'],
            'work_days_in_week' => ['nullable', 'integer', 'min:1', 'max:7'],
            'work_shift_count' => ['nullable', 'integer', 'min:0'],
            'first_shift_start_at' => ['nullable', 'date_format:H:i'],
            'first_shift_end_at' => ['nullable', 'date_format:H:i'],
            'second_shift_start_at' => ['nullable', 'date_format:H:i'],
            'second_shift_end_at' => ['nullable', 'date_format:H:i'],
            'third_shift_start_at' => ['nullable', 'date_format:H:i'],
            'third_shift_end_at' => ['nullable', 'date_format:H:i'],
            'social_amount' => ['nullable', 'string', 'max:255'],
            'addition_social_amount' => ['nullable', 'string', 'max:255'],
            'company_share' => ['nullable', 'string', 'max:255'],
            'dividend_amount' => ['nullable', 'string', 'max:255'],
            'user_personal_property' => ['nullable', 'string', 'max:255'],
            'provided_transport' => ['nullable', 'string', 'max:255'],
            'res_days' => ['nullable', Rule::in(Contract::WEEK_DAYS)],
        ];
    }

    public function index(Request $request)
    {
        $this->validate($request, [
            'paginateCount' => ['integer'],
            'status' => ['sometimes', 'required', 'in:0,1,2'],
            'employee_id' => ['sometimes', 'required', 'integer'],
        ]);

        $contract = Contract::with([
            'employee:id,user_id,is_active',
            'employee.user:id,name,surname',
            'position:id,name',
            'section:id,name,short_name',
            'sector:id,name,short_name',
            'department:id,name,short_name',
            'currency',

        ])->whereHas(
            'employee', function ($q) use ($request) {
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


        if ($request->has('draft'))
            $contract->where('draft', $request->get('draft'));


        if ($request->has('employee_id'))
            $contract->where('employee_id', $request->get('employee_id'));


        $contract = $contract->orderBy('id','desc')->paginate();

        return $this->successResponse($contract);

    }

    public function store(Request $request)
    {
        $this->validate($request, self::getValidateRules());

        try {

            DB::beginTransaction();

            if ($notExists = $this->companyInfo($request->get('company_id'), $request->only(
                ['department_id', 'section_id', 'position_id', 'sector_id', 'contract_id']
            ))) return $notExists;

            $employee = Employee::where('id', $request->get('employee_id'))
                ->where('company_id', $request->get('company_id'))
                ->first(['id', 'is_active']);

            if (!$employee) return $this->errorResponse(trans('response.employeeNotFound'), 404);

            if (!$employee->is_active) return $this->errorResponse(trans('response.employeeIsOut'), 404);

            if ($request->has('company_authorized_employee_id')) {
                $check = CompanyAuthorizedEmployee::wherehHas('employee', function ($q) {
                    $q->active()->company();
                })->where('id', $request->get('company_authorized_employee_id'))
                    ->exists();
                if (!$check) return $this->errorResponse(trans('response.company_authorized_employee_not_found'), 404);
            }

            self::storeContract($request);

            DB::commit();

            return $this->successResponse('ok');

        } catch (QueryException $exception) {
            if ($exception->errorInfo[1] == 1452)
                return $this->errorResponse([trans('response.SomeFiledIsNotFoundInDatabase')], 422);
            return $this->errorResponse(trans('response.tryLater'), 500);
        }
    }

    public function update(Request $request, $id)
    {

        $rules = [
            'draft' => ['nullable' , 'boolean'],
            'department_id' => ['sometimes', 'required', 'integer'],
            'section_id' => ['sometimes', 'required', 'integer'],
            'sector_id' => ['sometimes', 'required', 'integer'],
            'position_id' => ['sometimes', 'required', 'integer'],
//            'salary' => ['sometimes', 'required', 'numeric'],
            'contract' => ['sometimes', 'mimes:pdf,doc,docx'],
            'start_date' => ['sometimes', 'required', 'date'],
            'end_date' => ['sometimes', 'required', 'date'],
            'personal_category_id' => ['nullable', 'integer'],
            'specialization_degree_id' => ['nullable', 'integer'],//exists
            'work_environment_id' => ['nullable', 'integer'],//exists
            'state_value' => ['nullable', 'numeric'],
            'contract_type_id' => ['nullable', 'integer'],//exists
            'position_description' => ['nullable', 'string'],
            'currency_id' => ['nullable', 'integer'],//exists
            'position_salary_praise_about' => ['nullable', 'numeric'],
            'addition_package_fee' => ['nullable', 'numeric', "min:0", "max:100"],
            'award_amount' => ['nullable', 'numeric'],
            'award_period' => ['nullable', Rule::in(Contract::AWARD_PERIODS)],
            'work_environment_addition' => ['nullable', 'numeric'],
            'overtime_addition' => ['nullable', 'numeric'],
            'labor_protection_addition' => ['nullable', 'string'],
            'labor_meal_addition' => ['nullable', 'string'],
            'work_start_date' => ['nullable', 'date_format:Y-m-d'],
            'work_end_date' => ['nullable', 'date_format:Y-m-d'],
            'break_time_start' => ['nullable', 'date_format:H:i'],
            'break_time_end' => ['nullable', 'date_format:H:i'],
            'incomplete_work_hours' => ['nullable', 'numeric', "min:0", 'max:12'],
            'work_days_in_week' => ['nullable', 'integer', 'min:1', 'max:7'],
            'work_shift_count' => ['nullable', 'integer', 'min:0'],
            'first_shift_start_at' => ['nullable', 'date_format:H:i'],
            'first_shift_end_at' => ['nullable', 'date_format:H:i'],
            'second_shift_start_at' => ['nullable', 'date_format:H:i'],
            'second_shift_end_at' => ['nullable', 'date_format:H:i'],
            'third_shift_start_at' => ['nullable', 'date_format:H:i'],
            'third_shift_end_at' => ['nullable', 'date_format:H:i'],
            'provided_transport' => ['nullable', 'string', 'max:255'],
            'res_days' => ['nullable', Rule::in(Contract::WEEK_DAYS)],
            'vacation_main' => ['nullable', 'integer'],
            'vacation_work_insurance' => ['nullable', 'integer'],
            'vacation_work_envs' => ['nullable', 'integer'],
            'vacation_for_child' => ['nullable', 'integer'],
            'vacation_collective_contract' => ['nullable', 'integer'],
            'vacation_total' => ['nullable', 'integer'],
            'vacation_social_benefits' => ['nullable', 'numeric'],
            'vacation_start_date' => ['nullable', 'date_format:Y-m-d'],
            'vacation_end_date' => ['nullable', 'date_format:Y-m-d'],
        ];
        $this->validate($request, $rules);

        if (!$request->only(array_keys($rules))) return $this->successResponse(trans('response.nothingToUpdate'),400);
        DB::beginTransaction();

        if ($notExists = $this->companyInfo($request->get('company_id'), $request->only([
            'department_id', 'section_id', 'position_id', 'sector_id', 'contract_id'
        ]))) return $notExists;


        $contract = Contract::with([
            'position',
            'section:id,name',
            'sector:id,name',
            'department:id,name',
        ])->where('id', $id)->first(['id', 'versions'] + array_keys($rules));

        if (!$contract) return $this->errorResponse(trans('response.contractNotFound'), 404);

        if (!$contract->versions) $contract->versions = [];

        $originalProgram  = $contract->versions;

        $originalProgram[] = [
            'user' => Auth::user(),
            'updated_at' => Carbon::now()->toDateTimeLocalString(),
            'from' => $contract,
        ];

        $contract->versions = $originalProgram;

        $contract->fill($request->only(array_keys($rules)));

        $contract->save();

        DB::commit();

        return $this->successResponse('ok');
    }

    public function add(Request $request, $id)
    {

        $this->validate($request, [
            'data' => ['required', 'array'],
            'data.*.key' => ['required', 'string', 'max:255'],
            'data.*.value' => ['required', 'string', 'max:255'],
        ]);
        $contract = Contract::whereHas('employee', function ($q) {
            $q->company();
        })->where('id', $id)->first(['id', 'additions']);

        if (!$contract->additions) $contract->additions = [];

        $contract->additions = array_merge((array)$request->additions, $request->get('data'));

        $contract->save();

        return $this->successResponse('ok');

    }

    public function delete(Request $request, $id)
    {
        $this->validate($request, [
            'company_id' => ['required', 'integer']
        ]);

        $contract = Contract::whereHas('employee', function ($q) {
            $q->company();
        })->where('id', $id)->first(['id']);

        if (!$contract) return $this->errorResponse(trans('response.contractNotFound'), 404);

        $contract->update(['is_active' => false]);

        return $this->successResponse('ok');
    }

    public function show(Request $request, $id)
    {
        $contract = Contract::with([
            'position',
            'section:id,name',
            'sector:id,name',
            'department:id,name',
            'employee:id,is_active,user_id',
            'employee.user:id,name,surname',
            'contract_type',
            'duration_type',
        ])
            ->whereHas('employee', function ($q) use ($request) {
                $q->where('company_id', $request->get('company_id'));
            })->where('id', $id)->first();
        return $this->successResponse($contract);
    }


}
