<?php

namespace Modules\Hr\Http\Controllers;

use App\Filters\CompanyOrderFilters;
use App\Filters\OrderEmployeeFilters;
use App\Traits\ApiResponse;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Modules\Hr\Entities\EmployeeOrders\Order;
use Modules\Hr\Entities\WorkSkip;

class WorkSkipsController extends Controller
{
    use ApiResponse, ValidatesRequests;

    private $workSkip;
    private $order;


    /**
     * WorkSkipsController constructor.
     * @param WorkSkip $workSkip
     * @param Order $order
     */
    public function __construct(WorkSkip $workSkip, Order $order)
    {
        $this->workSkip = $workSkip;
        $this->order = $order;
    }

    public function getMainWorkSkips(Request $request, CompanyOrderFilters $orderFilters, OrderEmployeeFilters $employeeFilters): JsonResponse {
        $this->validate($request, [
            'employee_id' => [
                'required',
//                Rule::exists('employees', 'id')->where('company_id', $request->get('company_id'))
            ],
            'per_page' => 'nullable|integer'
        ]);
        $orderFilters->addFilter('type_ids', [5, 6, 7, 8]);
        $orderFilters->addFilter('has_employee_id', $request->get('employee_id'));
        $employeeFilters->addFilter('employee_id', $request->get('employee_id'));
        $orders = $this->order
        ->where('company_id', $request->get('company_id'))
        ->filter($orderFilters)
        ->with([
            'employees' => function ($query) use ($employeeFilters){
                $query->filter($employeeFilters);
                $query->select([
                    'id',
                    'order_id',
                    'details->day as day',
                    'details->work_start_date as work_start_date',
                    'details->vacation_start_date as vacation_start_date',
                    'details->vacation_end_date as vacation_end_date',
                    'details->note as note',
                ]);
            },
            'confirmedPerson:id,user_id',
            'confirmedPerson.user:id,name,surname',
        ])
        ->orderBy('id', 'desc')
        ->paginate($request->get('per_page'), [
            'id',
            'type',
            'number',
            'order_sign_date',
            'confirmed_date',
            'confirmed_by'
        ]);
        $orders->getCollection()->transform(function ($order) {
             return [
                'id' => $order['id'],
                'type' => $order['type'],
                'number' => $order['number'],
                'confirmed_date' => $order['confirmed_date'],
                'confirmed_by' => $order['confirmedPerson'],
                'day' => $order['employees'][0]->day,
                'work_start_date' => $order['employees'][0]->work_start_date,
                'vacation_start_date' => $order['employees'][0]->vacation_start_date,
                'vacation_end_date' => $order['employees'][0]->vacation_end_date,
                'note' => $order['employees'][0]->note,
            ];
        });
        return $this->successResponse($orders);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function index(Request $request): JsonResponse {
        $this->validate($request, [
            'per_page' => 'nullable|integer',
            'reason_type' => [
                'required',
                Rule::in($this->workSkip->getReasonTypes())
            ]
        ]);

        $workSkips = $this->workSkip
        ->companyId($request->get('company_id'))
        ->with([
            'employee:id,user_id',
            'employee.user:id,name,surname',
            'confirmedEmployee:id,user_id',
            'confirmedEmployee.user:id,name,surname',
        ])
        ->orderBy('id', 'desc');
        if ($request->get('reason_type'))
            $workSkips = $workSkips->where('reason_type', $request->get('reason_type'));
        if ($request->get('employee_id'))
            $workSkips = $workSkips->where('employee_id', $request->get('employee_id'));
        $workSkips = $workSkips->paginate($request->get('per_page'));

        return $this->successResponse($workSkips);
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function show(Request $request, $id): JsonResponse {
        $log = $this->workSkip
            ->companyId($request->get('company_id'))
            ->where('id', $id)
            ->with([
                'employee:id,user_id',
                'employee.user:id,name,surname',
                'confirmedEmployee:id,user_id',
                'confirmedEmployee.user:id,name,surname',
            ])
            ->firstOrFail();
        return  $this->successResponse($log);
    }


    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function create(Request $request): JsonResponse {
        $this->validate($request, $this->getWorkSkipRules());
        $this->saveWorkSkipDocument($request, $this->workSkip);
        return $this->successResponse(trans('messages.saved'), 201);
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     * @throws ValidationException
     */
    public function update(Request $request, $id): JsonResponse {
        $this->validate($request, $this->getWorkSkipRules());
        $log = $this->workSkip->companyId($request->get('company_id'))->where('id', $id)->firstOrFail(['id']);
        $this->saveWorkSkipDocument($request, $log);
        return $this->successResponse(trans('messages.saved'), 200);
    }


    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function destroy(Request $request, $id): JsonResponse {
        $log = $this->workSkip->companyId($request->get('company_id'))->where('id', $id)->firstOrFail(['id']);
        return $log->delete()
            ? $this->successResponse(trans('messages.saved'), 200)
            : $this->errorResponse(trans('messages.not_saved'), 400);
    }

    /**
     * @param Request $request
     * @param WorkSkip $workSkip
     */
    private function saveWorkSkipDocument(Request $request, WorkSkip $workSkip): void {
        $workSkip->fill($request->only([
            'company_id',
            'employee_id',
            'document_number',
            'reason_type',
            'day',
            'date_of_presentation',
            'start_date',
            'end_date',
            'is_confirmed',
            'confirmed_employee_id',
            'work_start_date',
            'note'
        ]))->save();
    }

    /**
     * @return array
     */
    private function getWorkSkipRules(): array  {
        return  [
            'employee_id' => [
                'required',
                Rule::exists('employees', 'id')->where('company_id', \request()->get('company_id'))
            ],
            'document_number' => 'required_if:reason_type,1|max:255',
            'reason_type' => [
                'required',
                Rule::in($this->workSkip->getReasonTypes())
            ],
            'day' => 'required|integer',
            'date_of_presentation' => 'required_if:reason_type,1|date',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'is_confirmed' => 'required|boolean',
            'confirmed_employee_id' => [
                'required_if:is_confirmed,1',
                Rule::exists('employees', 'id')->where('company_id', \request()->get('company_id'))
            ],
            'work_start_date' => 'required|date',
            'note' => 'nullable|max:255'
        ];
    }


}
