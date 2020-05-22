<?php

namespace Modules\Storage\Http\Controllers;

use App\Traits\ApiResponse;
use App\Traits\Query;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Modules\Storage\Entities\Demand;
use Modules\Storage\Entities\DemandAssignment;
use Modules\Storage\Entities\DemandItem;

class DemandAssignmentController extends Controller
{
    use ApiResponse, ValidatesRequests , Query;

    public function index(Request $request)
    {
        $this->validate($request, [
            'per_page' => ['nullable', 'integer'],
            'product_id' => ['nullable', 'integer']
        ]);
        DemandAssignment::with(['items', 'items.employee', 'items.employee.user'])->where('product_id', $request->get('product_id'))
            ->get();
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'employee_id' => ['nullable', 'array'],
            'expiry_time' => ['nullable', 'date', 'date_format:Y-m-d H:i:s'],
            'expiry_time_for_assignment' => ['nullable', 'date', 'date_format:Y-m-d H:i:s'],
            'demand_id' => ['nullable', 'integer']
        ]);

        if ($notExists = $this->companyInfo($request->get('company_id'), $request->only(['employee_id' , 'demand_id']))) return $this->errorResponse($notExists);

        $demand = DemandAssignment::firstOrCreate([
            'employee_id' => $request->get('employee_id'),
            'demand_id' => $request->get('demand_id'),
            'company_id' => $request->get('company_id')
        ], [
            'expiry_time' => $request->get('expiry_time_for_assignment') ,
            'description' => $request->get('description_for_assignment')
        ]);

        if ($request->has('employee_id')){
            DemandItem::create([
                'employee_id' => $request->get('employee_id'),
                'expiry_time' => $request->get('expiry_time'),
                'demand_id' => $demand->id,
            ]);
        }

        return $this->successResponse('ok');
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'expiry_time' => ['nullable', 'date', 'date_format:Y-m-d H:i:s'],
            'status' => ['nullable' , 'integer' , Rule::in([
                DemandAssignment::STATUS_WAIT,
                DemandAssignment::STATUS_REJECTED,
                DemandAssignment::STATUS_ACCEPTED,
            ])]
        ]);
        $demand = DemandAssignment::where('id' , $id)
            ->where('company_id' , $request->get('company_id'))
            ->first();
        $demand->update($request->only(['expiry_time'  , 'description' , 'status']));
    }

    public function delete(Request $request, $id)
    {

    }
}
