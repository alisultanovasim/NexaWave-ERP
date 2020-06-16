<?php

namespace Modules\Storage\Http\Controllers;

use App\Traits\ApiResponse;
use App\Traits\Query;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Modules\Storage\Entities\Product;
use Modules\Storage\Entities\ProductAssignment;

class ProductAssignmentController extends Controller
{
    use ValidatesRequests, ApiResponse , Query;

    public function index(Request $request)
    {
        $this->validate($request, [
            'employee_id' => ['nullable', 'integer'],
            'department_id' => ['nullable'  , 'integer'],
            'section_id' => ['nullable' , 'integer'],
            'sector_id' => ['nullable' , 'integer'],
        ]);

        $assignments = ProductAssignment::with(['employee' , 'department' , 'section' , 'sector' , 'product' ,'product.kind'])
            ->company()
            ->orderBy('id' , 'desc');

        if ($request->get('employee_id'))
            $assignments->where('employee_id' , $request->get('employee_id'));

        if ($request->get('department_id'))
            $assignments->where('department_id' , $request->get('department_id'));

        if ($request->get('section_id'))
            $assignments->where('section_id' , $request->get('section_id'));

        if ($request->get('sector_id'))
            $assignments->where('sector_id' , $request->get('sector_id'));

        $assignments = $assignments->paginate($request->get('per_page'));

        return $this->successResponse($assignments);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'product_id' => ['required', 'integer'],
            'amount' => ['required', 'float'],
            'assignment_type' => ['required', 'integer',
                Rule::in([ProductAssignment::ASSIGN_TO_PLACE, ProductAssignment::ASSIGN_TO_USER])
            ],
            'employee_id' => ['nullable', 'integer'],
            'section_id' => ['nullable', 'integer'],
            'sector_id' => ['nullable', 'integer'],
            'department_id' => ['nullable', 'integer'],
        ]);

        $product = Product::company()
            ->where('product_id', $request->get('product_id'))
            ->first(['amount', 'status' , 'id']);

        if (!$product)
            return $this->errorResponse(trans('response.productNotFound'), 404);

        if ($product->amount < $request->get('amount'))
            return $this->errorResponse(trans('response.productAmountLessThanAssign'), 422);

        $product->decrement('amount' , $request->get('amount'));

        if ($notExists = $this->companyInfo($request->get('company_id'),
            $request->only([
                'department_id',
                'section_id',
                'sector_id',
                'employee_id',
                'state_id'
            ]))) return $this->errorResponse($notExists);

        $data = array_merge($request->only([
            'product_id',
            'amount',
            'assignment_type',
            'employee_id',
            'section_id',
            'department_id',
            'sector_id'
        ]), [
            'initial_state' => $product->state,
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

        return $this->successResponse($request);
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
            ->first(['id' , 'product_id' , 'amount']);
        if (!$assignment)
            return $this->errorResponse(trans('response.assignmentNotFound'), 404);

        if ($assignment->product_id == $request->get('product_id')){
            if ($request->get('amount') > $assignment->amount){
                $product = Product::company()
                    ->where('product_id', $request->get('product_id'))
                    ->first(['amount' ,'id']);
                if ($product->amount < $request->get('amount'))
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

            if ($product->amount < $request->get('amount'))
                return $this->errorResponse(trans('response.productAmountLessThanAssign'), 422);

            $product->decrement('amount' , $request->get('amount'));
        }

        $assignment->update($data);

        return $this->successResponse('ok');
    }

    public function delete(Request $request, $id)
    {
        $assignment = ProductAssignment::where('id', $id)
            ->company()
            ->first(['id' , 'product_id' , 'amount']);
        if (!$assignment)
            return $this->errorResponse(trans('response.assignmentNotFound'), 404);

        $assignment->delete();


        Product::where('id' , $id)
            ->increment('amount' , $assignment->amount);

        return $this->successResponse('ok');
    }
}
