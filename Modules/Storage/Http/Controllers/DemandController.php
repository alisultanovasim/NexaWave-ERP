<?php

namespace Modules\Storage\Http\Controllers;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Storage\Entities\Demand;
use Modules\Storage\Entities\ProductColor;

class DemandController extends Controller
{
    use ApiResponse , ValidatesRequests;
    public function index(Request $request)
    {
        $this->validate($request, [
            'per_page' => ['nullable' , 'integer'],
            'product_id' => ['nullable' , 'integer'] ,
            'employee_id' => ['nullable' , 'integer'],
            'want_from'=> ['date' , 'date_format' , 'Y-m-d H:i:s'],
            'want_to'=> ['date' , 'date_format' , 'Y-m-d H:i:s'],
        ]);
        $demands = Demand::where('company_id', $request->get('company_id'));

        if ($request->has('product_id'))
            $demands->where('product_id' , $request->get('product_id'));

        if ($request->has('employee_id'))
            $demands->where('employee_id' , $request->get('employee_id'));

        if ($request->has('want_from'))
            $demands->where('want_till'  , ">=", $request->get('employee_id'));

        if ($request->has('want_to'))
            $demands->where('want_till' , "<=" , $request->get('employee_id'));

        if ($request->has('status'))
            $demands->where('status' , $request->get('status'));

        $demands = $demands->paginate($request->get('per_page'));

        return $this->successResponse($demands);
    }

    public function store(Request $request)
    {
        $this->validate($request, ProductController::getValidationRules() + [
                'demand_description' => ['nullable' , 'string'],
                'want_till' => ['nullable' , 'date_format:Y-m-d H:i:s'],
            ]);

        $product = ProductColor::create($request->all());


        $demand = Demand::create([
            'description' => $request->get('demand_description') ,
            'want_till' => $request->get('want_till'),
            'product_id' => $product->id,
            'employee_id'  => $request->get('auth_employee_id'),
            'company_id' => $request->get('company_id')
        ]);

        return $this->successResponse('ok');
    }

    public function show(Request $request , $id)
    {
        $demands = Demand::where('company_id', $request->get('company_id'))
            ->where('id' , $id)
            ->first();
        if (!$demands)
            return $this->errorResponse(trans('response.demandNotFound'), 404 );
        return $this->successResponse($demands);
    }

    public function update(Request $request, $id)
    {
        $demand = Demand::where('company_id', $request->get('company_id'))
            ->where('id' , $id)
            ->first(['id' , 'status']);
        if (!$demand)
            return $this->errorResponse(trans('response.demandNotFound'), 404 );


        $demand->update($request->only([
            'description' ,
            'want_till'
        ]));

        return $this->successResponse('ok');


    }

    public function destroy($id)
    {
        //
    }
}
