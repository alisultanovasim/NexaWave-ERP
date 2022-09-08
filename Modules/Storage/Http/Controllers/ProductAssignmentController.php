<?php

namespace Modules\Storage\Http\Controllers;

use App\Traits\ApiResponse;
use App\Traits\Query;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Modules\Hr\Entities\Employee\Employee;
use Modules\Storage\Entities\Product;
use Modules\Storage\Entities\ProductAssignment;

class ProductAssignmentController extends Controller
{
    use ValidatesRequests, ApiResponse , Query;

    public function index(Request $request)
    {
        $this->validate($request, [
            'employee_id' => ['nullable', 'integer'],
            'department_id' => ['nullable', 'integer'],
            'section_id' => ['nullable', 'integer'],
            'sector_id' => ['nullable', 'integer'],
            'product_id' => ['nullable', 'integer'],
            'status' => ['nullable', Rule::in([
                ProductAssignment::RETURNED,
                ProductAssignment::ACTIVE,
                ProductAssignment::ALL
            ])],
        ]);

        $assignments = ProductAssignment::with(['employee:id,user_id' ,
            'employee.user:id,name,surname' ,
            'department' ,
            'section' ,
            'sector' ,
            'product' ,
            'product.kind',
            'product.kind.unit'])
            ->company()
            ->orderBy('id' , 'desc');
        if ($request->get('employee_id'))
            $assignments->where('employee_id' , $request->get('employee_id'));


        if ($request->get('status')){
            if ($request->get('status') != ProductAssignment::ALL)
                $assignments->where('status' , $request->get('status'));
        }else{
            $assignments->where('status' , ProductAssignment::ACTIVE);
        }
        if ($request->get('department_id'))
            $assignments->where('department_id' , $request->get('department_id'));

        if ($request->get('product_id'))
            $assignments->where('product_id' , $request->get('product_id'));

        if ($request->get('assignment_type'))
            $assignments->where('assignment_type' , $request->get('assignment_type'));


        if ($request->get('section_id'))
            $assignments->where('section_id' , $request->get('section_id'));

        if ($request->get('sector_id'))
            $assignments->where('sector_id' , $request->get('sector_id'));

        $assignments = $assignments->paginate($request->get('per_page'));
        return $this->dataResponse([
            'assignments'=>$assignments]);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'product_id' => ['required', 'integer'],
            'amount' => ['required', 'numeric'],
            'assignment_type' => ['required', 'integer',
                Rule::in([ProductAssignment::ASSIGN_TO_PLACE, ProductAssignment::ASSIGN_TO_USER])
            ],
            'user_id' => ['required_if:assignment_type,' . ProductAssignment::ASSIGN_TO_USER, 'integer'],
            'section_id' => ['nullable', 'integer'],
            'sector_id' => ['nullable', 'integer'],
            'department_id' => ['nullable', 'integer'],
        ]);
        $empId=Employee::query()
            ->where([
                'user_id'=>$request->user_id,
                'company_id'=>$request->company_id
            ])
            ->first(['id']);

        $product = Product::company()
            ->where('id', $request->get('product_id'))
            ->first(['initial_amount', 'status' , 'id','amount']);

        if (!$product)
            return $this->errorResponse(trans('response.productNotFound'), 422);

        if ($product->initial_amount < $request->get('amount'))
            return $this->errorResponse(trans('response.productAmountLessThanAssign'), 422);

        $product->decrement('amount' , $request->get('amount'));

        if ($notExists = $this->companyInfo($request->get('company_id'),
            $request->only([
                'department_id',
                'section_id',
                'sector_id',
//                'employee_id',
                'state_id'
            ]))) return $this->errorResponse($notExists);

        $data = array_merge($request->only([
            'product_id',
            'amount',
            'assignment_type',
            $empId,
            'section_id',
            'department_id',
            'sector_id'
        ]), [
            'reasons' => [] ,
            'company_id' => $request->get('company_id')
        ]);

        ProductAssignment::create($data);

        return $this->successResponse('ok');
    }

    public function show(Request $request, $id)
    {
        $assignment = ProductAssignment::with(['employee' , 'employee.user' ,'department' , 'section' , 'sector' , 'product' ,'product.kind','product.model' , 'product.title'])->where('id', $id)
            ->company()
            ->first();
        if (!$assignment)
            return $this->errorResponse(trans('response.assignmentNotFound'), 404);

        return $this->successResponse($assignment);
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'product_id' => ['nullable', 'integer'],
            'amount' => ['nullable', 'float'],
            'assignment_type' => ['nullable', 'integer',
                Rule::in([ProductAssignment::ASSIGN_TO_PLACE, ProductAssignment::ASSIGN_TO_USER])
            ],
            'employee_id' => ['nullable', 'integer'],
            'section_id' => ['nullable', 'integer'],
            'sector_id' => ['nullable', 'integer'],
            'department_id' => ['nullable', 'integer'],
            'initial_state' => ['nullable' ,'integer']
        ]);
        $data = $request->only([
            'product_id',
            'amount',
            'assignment_type',
            'employee_id',
            'section_id',
            'sector_id',
            'department_id',
            'initial_state',
        ]);
        $assignment = ProductAssignment::where('id', $id)
            ->company()
            ->first(['id' , 'product_id' , 'amount' , 'status']);
        if (!$assignment)
            return $this->errorResponse(trans('response.assignmentNotFound'), 404);


        if($assignment->status  == ProductAssignment::RETURNED)
            return $this->errorResponse(trans('response.assignmentStatusNotValid'), 404);

        if ($assignment->product_id == $request->get('product_id')){
            if ($request->get('amount') > $assignment->amount){
                $product = Product::company()
                    ->where('product_id', $request->get('product_id'))
                    ->first(['amount' ,'id']);
                if ($product->initial_amount < $request->get('amount'))
//                    dd('ol');
//                    exit();
                    return $this->errorResponse(trans('response.productAmountLessThanAssign'), 422);
                $product->decrement('amount' , $request->get('amount') - $assignment->amount  );
            }
            if ($request->get('amount') < $assignment->amount){
                Product::where('id' , $assignment->product_id)->increment('amount' ,  $assignment->amount - $request->get('amount')  );
            }
        }
        else{
            $product = Product::company()
                ->where('product_id', $request->get('product_id'))
                ->first(['amount']);


            if (!$product)
                return $this->errorResponse(trans('response.productNotFound'), 404);

            if ($product->initial_amount < $request->get('amount'))
                return $this->errorResponse(trans('response.productAmountLessThanAssign'), 422);

            $product->decrement('amount' , $request->get('amount'));
        }

        $assignment->update($data);

        return $this->successResponse('ok');
    }

    public function delete(Request $request, $id)
    {
        $this->validate($request , [
            'reasons' => ['required' , 'array' , 'min:1'],
            'reasons.*' => ['required' , 'string'],
        ]);
        $assignment = ProductAssignment::where('id', $id)
            ->company()
            ->first(['id' , 'product_id' , 'amount' , 'status']);
        if (!$assignment)
            return $this->errorResponse(trans('response.assignmentNotFound'), 404);

        if($assignment->status  == ProductAssignment::RETURNED)
            return $this->errorResponse(trans('response.assignmentStatusNotValid'), 422);

        $assignment->update([
            'status' => ProductAssignment::RETURNED,
            'reasons' => $request->get('reasons')
        ]);

        Product::where('id' , $id)
            ->increment('amount' , $assignment->amount);

        return $this->successResponse('ok');
    }
}
