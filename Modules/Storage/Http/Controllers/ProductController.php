<?php

namespace Modules\Storage\Http\Controllers;

use App\Models\User;
use App\Traits\ApiResponse;
use App\Traits\DocumentUploader;
use App\Traits\Query;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Storage\Entities\Product;
use Modules\Storage\Entities\ProductDelete;
use Modules\Storage\Entities\ProductKind;
use Modules\Storage\Entities\ProductTitle;
use Modules\Storage\Entities\ProductUpdate;
use function Deployer\get;

class ProductController extends Controller
{
    use  ApiResponse, ValidatesRequests, Query , DocumentUploader;

    public function index(Request $request)
    {
        $this->validate($request, [
            'storage_id' => ['nullable', 'integer', 'min:1'],
            'title_id' => ['required', 'integer', 'min:1'],
            'kind_id' => ['required', 'integer', 'min:1'],
            'name' => ['nullable', 'string', 'max:255'],
            'per_page' => ['nullable', 'integer', 'min:1'],
            'status' => ['nullable', 'integer', 'min:1']
        ]);

        $products = Product::with([
            'kind',
            'model:id,name',
            'kind.unit',
            'title:id,name',
            'state:id,name',
            'color:id,name',
            'storage:id,name',
            'kind.unit',
            'buy_from_country:id,name:short_name',
            'made_in_country:id,name:short_name'
        ])->where('company_id', $request->get('company_id'));

        if ($request->has('status'))
            $products->where('status', $request->get('status'));
        else
            $products->where('status', "=" , Product::STATUS_ACTIVE);

        if ($request->has('act_id'))
            $products->where('act_id' , $request->get('act_id'));

        $products = $products
            ->orderBy('id' , 'desc')
            ->where('kind_id', $request->get('kind_id'))
            ->paginate($request->get('per_page'));

//        [
//            'title' => $title   ,
//            'products' => $products
//        ]
            return $this->dataResponse($products);
    }

    public function firstPage(Request $request)
    {
        $this->validate($request, [
            'per_page' => ['nullable', 'integer', 'min:1'],
            'name' => ['nullable', 'string', 'max:255'],
        ]);

        $title = ProductKind::with(['title' , 'unit'])
            ->withCount(['products as product_amount' => function($q){
                $q->where('status' , Product::STATUS_ACTIVE);
                $q->select(DB::raw("SUM(amount)"));
            }])
            ->company()
            ->paginate($request->get('per_page'));


        return $this->dataResponse($title);
    }

    public function show(Request $request, $id)
    {
        $this->validate($request , [
            'show_deletes_logs' => ['nullable' , 'boolean' ],
            'show_updates_logs' => ['nullable' , 'boolean' ],
        ]);
        $product = Product::with([
            'kind',
            'kind.unit',
            'model:id,name',
            'title:id,name',
            'state:id,name',
            'color:id,name',
            'storage:id,name',
            'buy_from_country:id,name:short_name',
            'made_in_country:id,name:short_name'
        ])
            ->where('company_id', $request->get('company_id'));



        if ($request->get('show_deletes_logs')){
            $product->with([
                'deletes_logs',
                'deletes_logs.employee',
                'deletes_logs.employee.user:id,name,surname'
            ]);
        }
        if ($request->get('show_updates_logs')){
            $product->with([
                'updates_logs',
                'updates_logs.employee',
                'updates_logs.employee.user:id,name,surname'
            ]);
        }


        $product = $product->where('id', $id)
            ->first();

        if (!$product)
            return $this->errorResponse(trans('response.ProductNotFound'));


        return $this->successResponse($product);
    }

    public function showHistory(Request $request , $id){
        $product = Product::with([
            'kind',
            'kind.unit',
            'title:id,name',
            'model:id,name',
        ])
            ->where('company_id', $request->get('company_id'))
            ->where('id', $id)
            ->with([
               'deletes_logs',
                'deletes_logs.employee',
                'deletes_logs.employee.user:id,name,surname',
                'updates_logs',
                'updates_logs.employee',
                'updates_logs.employee.user:id,name,surname'
            ])->first([
                'amount' , 'id' , 'initial_amount','kind_id' , 'title_id' , 'model_id' , 'product_mark'
            ]);

        if (!$product)
            return $this->errorResponse(trans('response.ProductNotFound'));



        return $this->successResponse($product);
    }

    public function store(Request $request)
    {
        $this->validate($request, self::getValidationRules());
        DB::beginTransaction();
        if ($notExists = $this->companyInfo(
            $request->get('company_id'),
            $request->only( 'storage_id', 'title_id', 'state_id' , 'sell_act_id' )))
            return $this->errorResponse($notExists);
        $check = ProductKind::where([
            ['title_id' , '=' ,   $request->get('title_id')],
            ['company_id', '=', $request->get('company_id')],
            ['id', '=', $request->get('kind_id')],
        ])->exists();
        if (!$check) return $this->errorResponse(trans('response.productKindNotFoundOrNotBelongToTitle'),400);
        $product = new Product();
        $product
            ->fill(array_merge($request->all(), [
                'status' => Product::STATUS_ACTIVE,
                'initial_amount' => $request->get('amount')
            ]))
            ->save();
        DB::commit();
        return $this->successResponse('ok');
    }

    public function increase(Request $request, $id)
    {
        $this->validate($request, [
            'amount' => ['required', 'numeric'],
            'income_description' => ['nullable', 'string'],
        ]);
        DB::beginTransaction();

        $prod = Product::where([
            ['id', '=', $id],
            ['company_id', '=', $request->get('company_id')],
        ])->first(['id', 'amount']);

        if (!$prod) return $this->errorResponse(trans('response.productNotFound'), 404);

        Product::where([
            ['id', '=', $id],
            ['company_id', '=', $request->get('company_id')],
        ])->increment('amount', $request->get('amount'));

        DB::commit();

        return $this->successResponse('ok');
    }

    public function reduce(Request $request, $id)
    {
        $this->validate($request, [
            'amount' => ['required', 'numeric'],
            'outcome_description' => ['nullable', 'string'],
        ]);
        DB::beginTransaction();

        $prod = Product::where([
            ['id', '=', $id],
            ['company_id', '=', $request->get('company_id')],
        ])->first(['id', 'amount']);

        if (!$prod) return $this->errorResponse(trans('response.productNotFound'), 404);

        if ($prod->amount < $request->get('amount'))
            return $this->errorResponse(trans('response.amountUnexpected'), 422);

        Product::where([
            ['id', '=', $id],
        ])->decrement('amount', $request->get('amount'));


        DB::commit();

        return $this->successResponse('ok');
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, array_merge(self::getUpdateRules(), [
            'reasons' => ['required', 'array','min:1'],
        ]));

        DB::beginTransaction();


        $product = Product::where([
            ['company_id', '=', $request->get('company_id')],
            ['id', '=', $id],
        ])->first(Product::CAT_UPDATE);

        if (!$product)
            return $this->errorResponse(trans('response.productNotFound'), 422);

        $data = $request->only(Product::CAT_UPDATE);
        Product::where('id' , $id)
            ->update($data);


        ProductUpdate::create([
            'employee_id' => Auth::user()->getEmployeeId($request->get('company_id')),
            'product_id' => $id,
            'updates' => [
                'from' => $product,
                'to' => $data,
                'reasons' => $request->get('reasons')
            ]
        ]);

        DB::commit();
        return $this->successResponse('ok');

    }

    public function delete(Request $request, $id)
    {
        $this->validate($request , [
            'amount' => ['required' , 'numeric'],
            'act' => ['required' , 'mimes:png,jpg,pdf,doc,docx,xls,xlsx'],
        ]);
        $product = Product::where([
            ['id', '=', $id],
            ['company_id', '=', $request->get('company_id')],
        ])->first(['id' , 'amount']);
        if (!$product) return $this->errorResponse(trans('response.productNotFound') , 404);

        $deleted = [
            'product_id' => $product->id,
            'employee_id' => Auth::user()->getEmployeeId($request->get('company_id')),
            'act' => $this->uploadFile($request->act,$request->get('company_id') ,'delete-acts'),
            'reason' => $request->get('reason')
        ];

        if ($request->has('amount')){
            if ($product->amount < $request->get('amount'))
                return $this->errorResponse(trans('response.amountError') , 422);
            $product->decrement('amount' , $request->get('amount'));
            $deleted['amount'] = $request->get('amount');
        }else{
            $product->update([
                'status' => Product::TOTAL_DELETED,
                'amount' => $product->amount
            ]);
            $deleted['amount'] =  $product->amount;
        }

        ProductDelete::create($deleted);

        return $this->successResponse('ok');

    }

    public static function getValidationRules()
    {
        return [
            'unit_id' => ['required', 'integer', 'min:1'],
            'less_value' => ['required', 'boolean'],
            'quickly_old' => ['required', 'boolean'],
            'title_id' => ['required', 'integer', 'min:1'],//
            'kind_id' => ['required', 'integer', 'min:1'],//
            'state_id' => ['required', 'integer'],
            'description' => ['nullable', 'string'],
            'amount' => ['required', 'numeric'],
            'storage_id' => ['required', 'integer'],
//            'product_model' => ['nullable', 'string', 'max:255'],
            'product_mark' => ['nullable', 'string', 'max:255'],
            'color_id' => ['nullable', 'integer'],//
            'main_funds' => ['nullable', 'boolean'],
            'inv_no' => ['nullable', 'string', 'max:255'],
            'exploitation_date' => ['nullable', 'date', 'date_format:Y-m-d'],
            'size' => ['nullable', 'numeric'],
            'made_in_country ' => ['nullable', 'integer', 'min:1'],//
            'buy_from_country ' => ['nullable', 'integer', 'min:1'],//
            'make_date' => ['nullable', 'date', 'date_format:Y-m-d'],
            'income_description' => ['nullable', 'string'],
            'model_id' => ['nullable' , 'integer'],
            'sell_act_id' => ['nullable' , 'integer'],
            'product_no' => ['nullable' , 'max:255']
        ];
    }

    public static function getUpdateRules()
    {
        return [
            'product_no' => ['nullable' , 'max:255'],
            'unit_id' => ['nullable', 'integer', 'min:1'],
            'less_value' => ['nullable', 'boolean'],
            'quickly_old' => ['nullable', 'boolean'],
            'title_id' => ['nullable', 'integer', 'min:1'],//
            'kind_id' => ['nullable', 'integer', 'min:1'],//
            'state_id' => ['nullable', 'integer'],
            'description' => ['nullable', 'string'],
            'amount' => ['nullable', 'numeric'],
            'storage_id' => ['nullable', 'integer'],
            'product_model' => ['nullable', 'string', 'max:255'],
            'product_mark' => ['nullable', 'string', 'max:255'],
            'color_id' => ['nullable', 'integer'],//
            'main_funds' => ['nullable', 'boolean'],
            'inv_no' => ['nullable', 'string', 'max:255'],
            'exploitation_date' => ['nullable', 'date', 'date_format:Y-m-d'],
            'size' => ['nullable', 'numeric'],
            'made_in_country ' => ['nullable', 'integer', 'min:1'],//
            'buy_from_country ' => ['nullable', 'integer', 'min:1'],//
            'make_date' => ['nullable', 'date', 'date_format:Y-m-d'],
            'income_description' => ['nullable', 'string'],
        ];
    }
}
