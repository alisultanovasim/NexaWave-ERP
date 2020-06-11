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
    use ApiResponse, ValidatesRequests, Query;

    public function index(Request $request)
    {
        $this->validate($request, [
            'per_page' => ['nullable', 'integer'],
            'demand_id' => ['nullable', 'integer'],
        ]);
        $assignments = DemandAssignment::with(['items', 'items.employee', 'items.employee.user'])
            ->where('demand_id' , $request->get('demand_id'))
            ->get();

        return $this->successResponse($assignments);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'employees' => ['nullable', 'array'],
            'employees.*.id' => ['require_with:employees', 'integer'],
                'employees.*.expiry_time' => ['nullable', 'date', 'date_format:Y-m-d H:i:s'],
            'expiry_time' => ['nullable', 'date', 'date_format:Y-m-d H:i:s'],
            'demand_id' => ['required', 'integer']
        ]);

        DB::beginTransaction();


        if ($notExists = $this->companyInfo($request->get('company_id'), $request->only(['demand_id'])))
            return $this->errorResponse($notExists);

        $demand = DemandAssignment::firstOrCreate([
            'employee_id' => $request->get('employee_id'),
            'demand_id' => $request->get('demand_id'),
        ], [
            'expiry_time' => $request->get('expiry_time'),
            'description' => $request->get('description')
        ]);


        //todo check employee
        if ($request->has('employee_id')) {
            $data = [];
            foreach ($request->get('employees') as $assignment) {
                $data = [
                    'demand_id' => $demand->id,
                    'expiry_time' => $assignment["expiry_time"],
                    'description' => $assignment["description"],
                    "employee_id" => $assignment["employee_id"],
                    'status' => DemandItem::WAIT
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
            'status' => ['nullable', 'integer', Rule::in([
                DemandAssignment::STATUS_WAIT,
                DemandAssignment::STATUS_REJECTED,
                DemandAssignment::STATUS_ACCEPTED,
            ])]
        ]);
        $demand = DemandAssignment::where('id', $id)
            ->where('company_id', $request->get('company_id'))
            ->first();
        $demand->update($request->only(['expiry_time', 'description', 'status']));
    }

    public function delete(Request $request, $id)
    {
        $demand = DemandAssignment::where('id', $id)
            ->where('company_id', $request->get('company_id'))
            ->delete();
        return $this->successResponse('ok');
    }

    public function addItem(Request $request)
    {
        $this->validate($request, [
            'employee_id' => ['required', 'integer'],
            'expiry_time' => ['nullable', 'date_format:Y-m-d H:i:s'],
            'demand_assignment_id' => ['required', 'integer']
        ]);


        if (!DemandAssignment::company()
            ->where('id', $request->get('company_id'))
            ->exists())
            return $this->errorResponse(trans('response.assignmentNotFound'), 404);

        //todo check employee
        DemandItem::create([
            'description' => $request->get('description'),
            'expiry_time' => $request->get('expiry_time'),
            'employee_id' => $request->get('employee_id'),
            'demand_assignment_id' => $request->get('demand_assignment_id')
        ]);
        return $this->successResponse('ok');
    }

    public function updateItem(Request $request, $id)
    {
        $this->validate($request, [
            'employee_id' => ['nullable', 'integer'],
            'expiry_time' => ['nullable', 'date_format:Y-m-d H:i:s'],
//            'demand_assignment_id' => ['required', 'integer']
        ]);


        //todo check employee
        if (!DemandItem::company()->where([
//            ['demand_assignment_id', '=', $request->get('demand_assignment_id')],
            ['id', '=', $id],
        ])->exists())
            return $this->errorResponse(trans('response.ItemNotFound'), 404);

        DemandItem::where('id' , $id)
            ->update($request->only([
                'employee_id',
                'expiry_time',
                'description'
            ]));

        return $this->successResponse('ok');
    }

    public function deleteItem(Request $request, $id)
    {
         DemandItem::company()->where([
            ['id', '=', $id],
        ])->delete();
        return $this->successResponse('ok');
    }

    public function employeeUpdate(Request $request, $id)
    {
        $this->validate($request, [
            'employee_id' => ['nullable', 'integer'],
            'expiry_time' => ['nullable', 'date_format:Y-m-d H:i:s'],
            'status' => ['nullable' , 'integer' , Rule::in([
                DemandItem::ACCEPTED,
                DemandItem::REJECTED,
            ])]
        ]);

        if (!DemandItem::company()->where([
            ['employee_id', '=', $request->get('auth_employee_id')],
            ['id', '=', $id],
        ])->exists())
            return $this->errorResponse(trans('response.ItemNotFound'), 404);

        DemandItem::where([
            ['id', '=', $id],
        ])->update([
            'description' => $request->get('description'),
            'status' => $request->get('status')??0
        ]);

        return $this->successResponse('ok');


    }
}
