<?php

namespace Modules\Hr\Http\Controllers;

use App\Filters\CompanyOrderFilters;
use App\Filters\OrderEmployeeFilters;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use http\Exception\InvalidArgumentException;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Modules\Hr\Entities\Employee\Employee;
use Modules\Hr\Entities\EmployeeOrders\BusinessTrip;
use Modules\Hr\Entities\EmployeeOrders\ContractConclusion;
use Modules\Hr\Entities\EmployeeOrders\Contracts\OrderType;
use Modules\Hr\Entities\EmployeeOrders\EducationVacation;
use Modules\Hr\Entities\EmployeeOrders\LaborVacation;
use Modules\Hr\Entities\EmployeeOrders\Order;
use Illuminate\Http\Request;
use Modules\Hr\Entities\EmployeeOrders\OrderEmployee;
use Modules\Hr\Entities\EmployeeOrders\SocialVacation;
use Modules\Hr\Entities\EmployeeOrders\Termination;
use Modules\Hr\Entities\EmployeeOrders\UnpaidVacation;
use Modules\Hr\Entities\EmployeeOrders\WorkChange;


class CompanyOrderController extends Controller
{
    use ApiResponse, ValidatesRequests;

    private $order;
    private $orderEmployee;

    public function __construct(Order $order, OrderEmployee $orderEmployee)
    {
        $this->order = $order;
        $this->orderEmployee = $orderEmployee;
    }

    public function index(Request $request){
        $sql = "select type, count(type) as count, (case when confirmed_date is not null then 1 else 0 end) as is_confirmed from orders where company_id = ? group by type, is_confirmed";
        $orders = DB::select($sql, [$request->get('company_id')]);
        $response = [];

        foreach ($this->order->getTypeIds() as $id){
            $response[$id] = [
                'type' => $id,
                'name' => trans('hr_orders.type.'.$id.'.name'),
                'description' => trans('hr_orders.type.'.$id.'.description'),
                'route' => trans('hr_orders.type.'.$id.'.route'),
                'sum' => 0,
                'sum_of_confirmed' => 0,
                'sum_of_not_confirmed' => 0
            ];
        }

        foreach ($orders as $order){
            $response[$order->type]['sum']++;
            if ($order->is_confirmed)
                $response[$order->type]['sum_of_confirmed']++;
            else
                $response[$order->type]['sum_of_not_confirmed']++;
        }
        return $this->successResponse(array_values($response));
    }

    public function getOrderEmployees(Request $request, CompanyOrderFilters $orderFilters, OrderEmployeeFilters $employeeFilters)
    {
        $employees = $this->orderEmployee
            ->filter($employeeFilters)
            ->whereHas('order', function ($query) use ($request, $orderFilters){
                $query->filter($orderFilters);
            })
            ->with('order:id,type,number,labor_code_id,order_sign_date,confirmed_date')
            ->orderBy('id', 'desc')
            ->paginate($request->get('per_page'), [
                'id',
                'order_id',
                'details'
            ]);
        return $this->successResponse($employees);
    }

    public function show(Request $request, $id)
    {
        $order = $this->order->where([
            'id' => $id,
            'company_id' => $request->get('company_id')
        ])
        ->with('employees')
        ->firstOrFail();
        return $this->successResponse($order);
    }

    public function create(Request $request)
    {
        $this->validate($request, $this->getRules());
        $this->saveOrder($request, $this->order);
        return $this->successResponse(trans('messages.saved'), 201);
    }

    public function update(Request $request, $id)
    {
        $order = $this->order->where([
            'id' => $id,
            'company_id' => $request->get('company_id'),
            'confirmed_date' => null
        ])->firstOrFail(['id']);
        $this->validate($request, $this->getRules());
        $this->saveOrder($request, $order);
        return $this->successResponse(trans('messages.saved'));
    }

    public function confirm(Request $request, $id)
    {
        $order = $this->order->where([
            'id' => $id,
            'company_id' => $request->get('company_id'),
            'confirmed_date' => null
        ])->firstOrFail(['id']);
        $order->update([
            'confirmed_by' => ($this->getEmployeeByUserId(Auth::id(), $request->get('company_id')))->getKey(),
            'confirmed_date' => Carbon::now()
        ]);
        return $this->successResponse(trans('messages.saved'));
    }

    public function saveOrder(Request $request, Order $order)
    {
        if ($request->get('is_confirmed')){
            $confirmedBy = ($this->getEmployeeByUserId(Auth::id(), $request->get('company_id')))->getKey();
            $confirmedDate = Carbon::now();
        }
        else {
            $confirmedBy = null;
            $confirmedDate = null;
        }
        DB::transaction(function () use ($request, $order, $confirmedBy, $confirmedDate){
            $order->fill([
                'company_id' => $request->get('company_id'),
                'type' => $request->get('type'),
                'number' => $request->get('number'),
                'labor_code_id' => $request->get('labor_code_id'),
                'order_sign_date' => $request->get('order_sign_date'),
                'created_by' => ($this->getEmployeeByUserId(Auth::id(), $request->get('company_id')))->getKey(),
                'confirmed_by' => $confirmedBy,
                'confirmed_date' => $confirmedDate
            ]);
            $order->save();
            $this->saveOrderEmployees($order->getKey(), $request->get('employees'), $this->getOrderModelByType($request->get('type')));
        });
    }

    private function saveOrderEmployees(int $orderId, array $orderEmployees, OrderType $orderType): void
    {
        $ids = [];
        foreach ($orderEmployees as $orderEmployee){
            $orderEmployee = $this->orderEmployee->updateOrCreate(
                [
                    'order_id' => $orderId,
                    'id' => $orderEmployee['id'] ?? null
                ],
                [
                    'order_id' => $orderId,
                    'details' => $orderEmployee['details']
//                    'details' => $this->trimOrderEmployeeDetailsJsonFromUnvalidatedFields($orderEmployee['details'], $orderType)
                ]
            );
            $ids[] = $orderEmployee->getKey();
        }
        if (count($ids)){
            $this->deleteOrderEmployeesWhichIsNotInArray($orderId, $ids);
        }
    }

    private function trimOrderEmployeeDetailsJsonFromUnvalidatedFields($details, OrderType $orderType)
    {
        $rules = $orderType->getEmployeeValidateRules();
        $newRules = [];
        foreach ($rules as $ruleName => $rule){
            $newRules[] = explode('employees.*.details', $ruleName);
        }
//        dd($rules);
    }

    private function deleteOrderEmployeesWhichIsNotInArray(int $orderId, array $ids): void
    {
        $this->orderEmployee->where('order_id', $orderId)->whereNotIn('id', $ids)->delete();
    }

    public function destroy(Request $request, $id)
    {
        $order = $this->order->where([
            'id' => $id,
            'company_id' => $request->get('company_id'),
            'confirmed_date' => null
        ])->firstOrFail(['id']);
        return $order->delete()
            ? $this->successResponse(trans('messages.saved'))
            : $this->errorMessage(trans('messages.not_saved'), 400);
    }

    private function getEmployeeByUserId(int $userId, int $companyId): Employee
    {
        return Employee::where([
            'user_id' => $userId,
            'company_id' => $companyId
        ])->first();
    }

    private function getRules(): array {
        $rules = [
            'type' => [
                'required',
                Rule::in($this->order->getTypeIds())
            ],
            'number' => 'required|min:2|max:255',
            'labor_code_id' => 'required|exists:labor_codes,id',
            'order_sign_date' => 'required|date|date_format:Y-m-d',
            'employees' => 'required|array',
            'is_confirmed' => 'required|boolean'
        ];
        if (\request()->get('type')){
            $rules = array_merge(
                $rules,
                $this->getOrderModelByType(\request()->get('type'))->getEmployeeValidateRules()
            );
        }
        return $rules;
    }

    private function getOrderModelByType($typeId): OrderType {
        if ($typeId == 1)
            return new ContractConclusion();
        else if ($typeId == 2)
            return new Termination();
        else if ($typeId == 3)
            return new WorkChange();
        else if ($typeId == 4)
            return new LaborVacation();
        else if ($typeId == 5)
            return new EducationVacation();
        else if ($typeId == 6)
            return new UnpaidVacation();
        else if ($typeId == 7)
            return new SocialVacation();
        else if ($typeId == 8)
            return new BusinessTrip();
        else
           throw new InvalidArgumentException('Invalid order type');
    }

}
