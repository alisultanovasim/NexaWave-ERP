<?php

namespace Modules\Storage\Http\Controllers;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Modules\Storage\Entities\Product;
use Modules\Storage\Entities\ProductAssignment;

class ProductAssignmentController extends Controller
{
    use ValidatesRequests, ApiResponse;

    public function index(Request $request)
    {
        $this->validate($request, [
            'employee_id' => ['nullable', 'integer'],
        ]);

    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'product_id' => ['required', 'integer'],
            'amount' => ['required', 'float'],
            'assignment_type' => ['required', 'integer',
                Rule::in([ProductAssignment::ASSIGN_TO_SECTION, ProductAssignment::ASSIGN_TO_USER])
            ],
            'employee_id' => ['nullable', 'integer'],
            'section_id' => ['nullable' , 'integer'],
            'sector_id' => ['nullable' , 'integer'],
            'department_id' => ['nullable' , 'integer'],
        ]);

        $product = Product::company()
            ->where('product_id' , $request->get('product_id'))
            ->first(['amount' , 'status']);


        if (!$product)
            return $this->errorResponse(trans('response.productNotFound'),422);

        $data = array_merge($request->only([
            'product_id',
            'amount',
            'assignment_type',
        ]) , [
            'initial_state' => $product->state,
            'company_id' => $request->get('company_id')
        ]);

        if ($request->get('assignment_type') == ProductAssignment::ASSIGN_TO_USER){
            $data = array_merge($data , $request->only([
                'employee_id'
            ]));
        }else{
            $data = array_merge($data , $request->only([
                'section_id',
                'department_id',
                'sector_id',
            ]));
        }

        ProductAssignment::create($data);



        return $this->successResponse('ok');


    }

    public function show(Request $request, $id)
    {
    }

    public function update(Request $request, $id)
    {
    }

    public function delete(Request $request, $id)
    {
    }
}
