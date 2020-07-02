<?php

namespace Modules\Hr\Http\Controllers;

use App\Traits\ApiResponse;
use http\Exception\InvalidArgumentException;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Modules\Hr\Entities\Employee\Employee;
use Modules\Hr\Entities\EmployeeOrders\ContractConclusion;
use Modules\Hr\Entities\EmployeeOrders\Contracts\OrderType;
use Modules\Hr\Entities\EmployeeOrders\Order;
use Illuminate\Http\Request;
use Modules\Hr\Entities\EmployeeOrders\OrderEmployee;


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

    }

    public function getByTypeId(Request $request, $typeId){
        $this->validate($request, [
            'per_page' => 'nullable|integer'
        ]);

        $orders = $this->order
        ->companyId($request->get('company_id'))
        ->where('type', $request->get('type'))
        ->with('orderEmployees')
        ->paginate($request->get('per_page'));
        return $this->successResponse($orders);
    }

    public function show(Request $request){

    }

    public function create(Request $request){
        $this->validate($request, $this->getRules());
        $this->saveOrder($request, $this->order);
        return $this->successResponse(trans('messages.saved'), 201);
    }

    public function update(Request $request, $id){
        $this->validate($request, $this->getRules());
        $order = $this->order->where([
            'id' => $id,
            'company_id' => $request->get('company_id'),
            'confirmed_date' => null
        ])->firstOrFail(['id']);
        $this->saveOrder($request, $order);
        return $this->successResponse(trans('messages.saved'));
    }

    public function saveOrder(Request $request, Order $order){
        $order->fill([
            'company_id' => $request->get('company_id'),
            'type' => $request->get('type'),
            'number' => $request->get('number'),
            'labor_code_id' => $request->get('labor_code_id'),
            'order_sign_date' => $request->get('order_sign_date'),
            'created_by' => ($this->getEmployeeByUserId(Auth::id(), $request->get('company_id')))->getKey()
        ]);
        $order->save();
        $this->saveOrderEmployees($order->getKey(), $request->get('order_employees'));
    }

    private function getEmployeeByUserId(int $userId, int $companyId): Employee {
        return Employee::where([
            'user_id' => $userId,
            'company_id' => $companyId
        ])->first();
    }

    private function saveOrderEmployees(int $orderId, array $orderEmployees): void {
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
                ]
            );
            $ids[] = $orderEmployee->getKey();
        }
        if (count($ids)){
            $this->deleteOrderEmployeesWhichIsNotInArray($orderId, $ids);
        }
    }

    private function deleteOrderEmployeesWhichIsNotInArray(int $orderId, array $ids): void {
        $this->orderEmployee->where('order_id', $orderId)->whereNotIn('id', $ids)->delete();
    }

    public function destroy(Request $request, $id){
        $order = $this->order->where([
            'id' => $id,
            'company_id' => $request->get('company_id')
        ])->firstOrFail(['id']);
        return $order->delete()
            ? $this->successResponse(trans('messages.saved'))
            : $this->errorMessage(trans('messages.not_saved'), 400);
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
            'employees' => 'required|array'
        ];
        if (\request()->get('type')){
            $rules = array_merge(
                $rules,
                $this->getOrderModelByType(\request()->get('type'))->getRules()
            );
        }
        return $rules;
    }

    private function getOrderModelByType($typeId): OrderType {
        if ($typeId == 1)
            return new ContractConclusion();
        else if ($typeId == 2)
            return new ContractConclusion();
        else
           throw new InvalidArgumentException('Invalid order type');
    }

}
