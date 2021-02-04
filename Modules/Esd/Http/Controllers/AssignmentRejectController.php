<?php

namespace Modules\Esd\Http\Controllers;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Esd\Entities\AssignmentItem;
use Modules\Esd\Entities\AssignmentReject;
use Modules\Esd\Entities\User;
use Modules\Hr\Entities\Employee\Employee;

class AssignmentRejectController extends Controller
{
    use ApiResponse, ValidatesRequests;

    public function index(Request $request)
    {
        $this->validate($request  , [
            'document_id' => ['nullable' , 'integer'],
            'assignment_id' => ['nullable' , 'integer'],
            'tome' => ['nullable' , 'boolean'],
            'per_page' => ['nullable' , 'integer']
        ]);

        $rejects = AssignmentReject::
        with([
                    'employee' , 'item' , 'item.employee', 'item.employee.user'
                ])->whereHas('item' , function($q) use ($request){
                    $q->whereHas('assignment',function ($q) use ($request){
                        $q->whereHas('document' , function ($q) use ($request){
                            $q->where('company_id' , $request->get('company_id'));
                            if($request->has('document_id'))
                                $q->where('id' , $request->get('document_id'));
                        });
                        if ($request->has('assignment_id'))
                            $q->where('id' , $request->get('assignment_id'));
                    });
                    if($request->has('tome'))
                        $q->where('user_id' , Auth::id());
                })->paginate($request->get('per_page'));
        return $this->successResponse($rejects);
    }

    public function store(Request $request, $id)
    {
        $this->validate($request, [
            'item_id' => ['required', 'integer', 'min:1'],
        ]);
        $item = AssignmentItem::whereHas('assignment', function ($q) use ($id) {
            $q->whereHas('document', function ($q) use ($id) {
                $q->where('company_id', \request('company_id'));
                $q->where('id', $id);
            });
        })->first(['id' , 'status']);
        if (!$item)
            return $this->errorResponse(trans('response.AssignmentItemNotFound'), 404);
        if (!$item->status == AssignmentItem::REJECTED)
            return $this->errorResponse(trans('response.itemAlreadyRejectedBro'), Response::HTTP_ALREADY_REPORTED);
        $employee = Employee::where([
            ['user_id', '=', Auth::id()],
            ['company_id', '=', $request->get('company_id')],
            ['is_active', '=', true],
        ])->first(['id']);

        if (!$employee) return $this->errorResponse(trans('response.employeeError') , 422);
        DB::beginTransaction();
        AssignmentReject::create([
            'employee_id' => $employee->id,
            'item_id' => $request->get('item_id'),
            'description' => $request->get('description')
        ]);
        AssignmentItem::where('id', $item->id)->update(['status' => AssignmentItem::REJECTED]);
        DB::commit();
        return $this->successResponse('ok');
    }

}
