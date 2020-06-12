<?php

namespace Modules\Storage\Http\Controllers;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Hr\Entities\Employee\Employee;
use Modules\Storage\Entities\Demand;
use Modules\Storage\Entities\Product;
use Modules\Storage\Entities\ProductColor;

class DemandController extends Controller
{
    use ApiResponse, ValidatesRequests;

    public function index(Request $request)
    {
        $this->validate($request, [
            'per_page' => ['nullable', 'integer'],
            'product_id' => ['nullable', 'integer'],
            'employee_id' => ['nullable', 'integer'],
            'want_from' => ['date', 'date_format', 'Y-m-d H:i:s'],
            'want_to' => ['date', 'date_format', 'Y-m-d H:i:s'],
        ]);

        $demands = Demand::with([
            'product:id,product_mark,product_model,amount,kind_id,title_id,unit_id',
            'product.kind',
            'product.model',
            'product.unit:id,name',
            'product.title:id,name',
            'employee:id,user_id,tabel_no',
            'employee.user:id,name,surname',
        ])
            ->withCount([
                'assignment as assigned_tome' => function($q) use ($request){
                    $q->whereHas('items' , function ($q) use ($request){
                        $q->where('employee_id' , $request->get('auth_employee_id'));
                    });
                }
            ])
            ->where('company_id', $request->get('company_id'));

        if ($request->has('product_id'))
            $demands->where('product_id', $request->get('product_id'));

        if ($request->has('employee_id'))
            $demands->where('employee_id', $request->get('employee_id'));

        if ($request->has('want_from'))
            $demands->where('want_till', ">=", $request->get('employee_id'));

        if ($request->has('want_to'))
            $demands->where('want_till', "<=", $request->get('employee_id'));

        if ($request->has('status'))
            $demands->where('status', $request->get('status'));

        $demands = $demands->orderBy('id', 'desc')->paginate($request->get('per_page'), [
            'id', 'product_id', 'description', 'created_at', 'want_till', 'employee_id'
        ]);

        return $this->successResponse($demands);
    }

    public function store(Request $request)
    {
        $this->validate($request, array_merge(ProductController::getValidationRules(), [
            'demand_description' => ['nullable', 'string'],
            'want_till' => ['nullable', 'date_format:Y-m-d H:i:s'],
            'storage_id' => ['nullable', 'integer'],
        ]));


        //todo check title and kind_id
        $product = Product::create(array_merge($request->all(), ['status' => Product::STATUS_DEMAND]));

        $employee_id = Employee::where([
            ['user_id' , Auth::id()],
            ['company_id' , $request->get('company_id')]
        ])->first(['id']);
        Demand::create([
            'description' => $request->get('demand_description'),
            'want_till' => $request->get('want_till'),
            'product_id' => $product->id,
            'employee_id' => $employee_id->id,
            'company_id' => $request->get('company_id')
        ]);
        return $this->successResponse('ok');
    }

    public function show(Request $request, $id)
    {
        $demands = Demand::with([
            'product',
            'product.color' ,
            'product.state' ,
            'product.kind',
            'product.unit',
            'product.title',
            'employee',
            'assignment',
            'assignment.employee',
            'assignment.items',
            'product.model',
            'employee.user',
            'product.buy_from_country' ,
            'product.made_in_country'
        ])
            ->where('company_id', $request->get('company_id'))
            ->where('id', $id)
            ->first();
        if (!$demands)
            return $this->errorResponse(trans('response.demandNotFound'), 404);
        return $this->successResponse($demands);
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, array_merge([
            'demand_description' => ['nullable', 'string'],
            'want_till' => ['nullable', 'date_format:Y-m-d H:i:s'],
            'update_product' => ['required', 'nullable']
        ], ProductController::getUpdateRules()));

        $demand = Demand::where('company_id', $request->get('company_id'))
            ->where('id', $id)
            ->where('employee_id', $request->get('auth_employee_id'))
            ->first(['id', 'status', 'product_id']);

        if (!$demand)
            return $this->errorResponse(trans('response.demandNotFound'), 404);

        if ($demand->status != Demand::STATUS_WAIT)
            return $this->errorResponse(trans('response.cannotUpdateStatusError'), 422);


        if ($request->get('update_product')) {
            //todo check title and kind_id
            Product::where('id', $demand->product_id)
                ->update($request->only([
                    'unit_id',
                    'less_value',
                    'quickly_old',
                    'title_id',
                    'kind_id',
                    'state_id',
                    'description',
                    'amount',
                    'storage_id',
                    'product_model',
                    'product_mark',
                    'product_no',
                    'color_id',
                    'main_funds',
                    'inv_no',
                    'exploitation_date',
                    'size',
                    'made_in_country',
                    'buy_from_country',
                    'make_date',
                    "company_id",
                ]));
        }

        $demand->update($request->only([
            'description',
            'want_till'
        ]));

        return $this->successResponse('ok');


    }

    public function delete(Request $request, $id)
    {

        $demand = Demand::where('company_id', $request->get('company_id'))
            ->where('id', $id)
            ->where('employee_id', $request->get('auth_employee_id'))
            ->first(['id', 'status', 'product_id']);

        if (!$demand)
            return $this->errorResponse(trans('response.demandNotFound'), 404);
        if ($demand->status != Demand::STATUS_WAIT)
            return $this->errorResponse(trans('response.demandNotFound'), 404);
        DB::beginTransaction();
        Product::where('id', $demand->product_id)->delete();
        $demand->delete();
        DB::commit();
        return $this->successResponse('ok');

    }
}
