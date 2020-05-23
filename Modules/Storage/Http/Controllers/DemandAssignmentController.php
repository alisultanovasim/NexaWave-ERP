<?php

namespace Modules\Storage\Http\Controllers;

use App\Traits\ApiResponse;
use App\Traits\Query;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
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
            'employees' => ['nullable', 'array'],
            'employees.*.id' => ['require_with:employees', 'integer'],
            'employees.*.expiry_time' =>['nullable', 'date', 'date_format:Y-m-d H:i:s'],

            'expiry_time' => ['nullable', 'date', 'date_format:Y-m-d H:i:s'],
            'demand_id' => ['nullable', 'integer']
        ]);

        DB::beginTransaction();


        $data = [];
        foreach ($request->get('employees') as $employee)
            $data[] = [
                'employee_id' => $employee->id,
            ];



        if ($notExists = $this->companyInfo($request->get('company_id'), $request->only(['demand_id'])))
            return $this->errorResponse($notExists);

        $demand = DemandAssignment::firstOrCreate([
            'employee_id' => $request->get('employee_id'),
            'demand_id' => $request->get('demand_id'),
            'company_id' => $request->get('company_id')
        ], [
            'expiry_time' => $request->get('expiry_time') ,
            'description' => $request->get('description')
        ]);


        //todo check employee
        if ($request->has('employee_id')){
            $data  = [];
            foreach ($request->get('employees') as $assignment){
                $data = [
                    'demand_id' => $demand->id,
                    'expiry_time' => $assignment["expiry_time"],
                    'description' => $assignment["description"],
                    "employee_id" => $assignment["employee_id"],
                    'status' => DemandAssignment::STATUS_WAIT
                ];
            }
            DemandItem::insert($data);
        }
        DB::commit();

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
