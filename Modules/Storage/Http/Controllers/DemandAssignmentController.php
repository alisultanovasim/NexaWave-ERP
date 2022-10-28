<?php

namespace Modules\Storage\Http\Controllers;

use App\Traits\ApiResponse;
use App\Traits\Query;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Modules\Hr\Entities\Employee\Employee;
use Modules\Storage\Entities\Demand;
use Modules\Storage\Entities\DemandAssignment;
use Modules\Storage\Entities\DemandItem;
use Modules\Storage\Entities\ProductAssignment;

class DemandAssignmentController extends Controller
{
    use ApiResponse, ValidatesRequests, Query;

    public function index(Request $request)
    {
        $this->validate($request, [
            'per_page' => ['nullable', 'integer'],
            'demand_id' => ['required', 'integer'],
        ]);
        $assignments = DemandAssignment::with(['items', 'items.employee', 'items.employee.user' , 'employee.user'])
            ->company()
            ->where('demand_id', $request->get('demand_id'))
            ->get();

        return $this->successResponse($assignments);
    }

    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->validate($request, [
            'employees' => ['nullable', 'array'],
            'company_id' => ['required', 'integer'],
            'employees.*.id' => ['required_with:employees', 'integer'],
            'employees.*.expiry_time' => ['nullable', 'date', 'date_format:Y-m-d H:i:s'],
            'employees.*.description' => ['nullable','string','min:3','max:100'],
            'demand_id'=>['required','integer',Rule::exists('demands','id')]
        ]);

//        DB::beginTransaction();

//        if ($notExists = $this->companyInfo($request->get('company_id'), $request->only(['demand_id'])))
//            return $this->errorResponse($notExists);
//        $demandAssignment = DemandAssignment::firstOrCreate([
//            'demand_id' => $id,
//            'employee_id' => Auth::user()->getEmployeeId($request->get('company_id'))
//        ], [
//            'expiry_time' => $request->get('expiry_time'),
//            'description' => $request->get('description')
//        ]);
        $counter=0;
        foreach ($request->employees as $employee){
            $data=[
                'description'=>$employee['description'],
                'employee_id'=>$employee['id'],
                'demand_id'=>$request->demand_id,
                'expiry_time'=>$employee['expiry_time']
            ];
            if (count($demandAssignment=DemandAssignment::query()->where([
                    'employee_id'=>$employee['id'],
                    'demand_id'=>$request->demand_id])->get())>0){
                $assignmentId=$demandAssignment[0]['id'];
                $counter++;
                echo "The assignment is already exist that with $assignmentId \n";
            }
            else{
                DemandAssignment::query()->insert($data);
            }
            if ($counter==count($request->employees)){
                return $this->errorResponse('The all of assignments already exist');
            }
        }
        Demand::query()->where('id',$request->demand_id)->increment('progress_status',1);
        return $this->successResponse('Assignment successfully created!',201);



//        //todo check employee
//        if ($request->has('employees')) {
//            $data = [];
//            foreach ($request->get('employees') as $assignment) {
//                $data = [
//                    'demand_assignment_id' => $demandAssignment->id,
//                    'expiry_time' => isset($assignment["expiry_time"]) ? $assignment["expiry_time"] : null,
//                    'description' => isset($assignment["description"]) ? $assignment["description"] : null,
//                    "employee_id" => $assignment["id"],
//                    'status' => DemandItem::WAIT
//                ];
//            }
//            DemandItem::insert($data);
//        }
//        DB::commit();
//        return $this->successResponse('ok');
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
        $demandAssignment = DemandAssignment::where('id', $id)
            ->company()
            ->first(['id', 'demand_id']);

        if (!$demandAssignment)
            return $this->errorResponse(trans('response.demandAssignmentNotFound') , 404);

        $demandAssignment->update($request->only(['expiry_time', 'description', 'status']));

        Demand::where('id' , $demandAssignment->demand_id)
            ->update([
                'status' => $request->get('status')
            ]);

        return $this->successResponse('ok');
    }

    public function delete(Request $request, $id)
    {
        $demand = DemandAssignment::company()->where('id', $id)
            ->delete();
        return $this->successResponse($demand);
    }

    public function addItem(Request $request)
    {
        $this->validate($request, [
            'employee_id' => ['required', 'integer'],
            'expiry_time' => ['nullable', 'date_format:Y-m-d H:i:s'],
            'demand_assignment_id' => ['required', 'integer']
        ]);

        if (!DemandAssignment::company()
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

        DemandItem::where('id', $id)
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
            'status' => ['nullable', 'integer', Rule::in([
                DemandItem::ACCEPTED,
                DemandItem::REJECTED,
            ])]
        ]);

        if (!DemandItem::company()->where([
            ['employee_id', '=', Auth::user()->getEmployeeId($request->get('company_id'))],
            ['id', '=', $id],
        ])->exists())
            return $this->errorResponse(trans('response.ItemNotFound'), 404);

        DemandItem::where([
            ['id', '=', $id],
        ])->update([
            'description' => $request->get('description'),
            'status' => $request->get('status') ?? 0
        ]);
        return $this->successResponse('ok');

    }
    public function getEmployeeId($companyId)
    {
        return Employee::query()
            ->where([
                'user_id'=>Auth::id(),
                'company_id'=>$companyId
            ])
            ->first()['id'];
    }
}
