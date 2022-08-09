<?php

namespace Modules\Storage\Http\Controllers;

use App\Traits\ApiResponse;
use App\Traits\DocumentUploader;
use App\Traits\Query;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Modules\Storage\Entities\Product;
use Modules\Storage\Entities\ProductAssignment;
use Modules\Storage\Entities\ProductDelete;
use Modules\Storage\Entities\ProductKind;
use Modules\Storage\Entities\ProductTitle;
use Modules\Storage\Entities\ProductUpdate;

class ProductController extends Controller
{
    use  ApiResponse, ValidatesRequests, Query, DocumentUploader;

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
            $products->where('status', "=", Product::STATUS_ACTIVE);

        if ($request->has('act_id'))
            $products->where('act_id', $request->get('act_id'));


        if ($request->get('show_updates_logs')) {
            $products->with([
                'updates_logs',
                'updates_logs.employee',
                'updates_logs.employee.user:id,name,surname'
            ]);
        }

        $products = $products
            ->orderBy('id', 'desc')
            ->where('kind_id', $request->get('kind_id'))
            ->paginate($request->get('per_page'));

        return $this->dataResponse($products);
    }

    public function firstPage(Request $request)
    {
        $this->validate($request, [
            'per_page' => ['nullable', 'integer', 'min:1'],
            'name' => ['nullable', 'string', 'max:255'],
            "category_id" => ['sometimes', 'required', "int"]
        ]);

        $title = ProductKind::query()
            ->with([
                'title',
                'unit',
            ])
            ->withCount(['products as product_amount' => function ($q) {
                $q->where('status', Product::STATUS_ACTIVE);
                $q->select(DB::raw("SUM(amount)"),'room','floor');
            }]);
//            ->company();

        if ($request->has("title_id"))
            $title = $title->where("title_id", "=", $request->input("title_id"));
        $title = $title->paginate($request->get('per_page'));

        return $this->dataResponse($title);
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function getTitles(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->validate($request,[
            'company_id'=>'required'
        ]);
        $per_page=$request->per_page ?? 10;

//        $titles=ProductTitle::select('product_titles.*',DB::raw('count(product_kinds.title_id) as titleCount'))
//            ->leftJoin('product_kinds','product_kinds.title_id','=','product_titles.id')
//            ->where('product_kinds.company_id',$request->company_id)
//            ->paginate($per_page);

        $titles=ProductTitle::query()
            ->withCount('kinds as kindCount')
            ->withCount('products as productCount')->paginate($per_page);
        return $this->dataResponse($titles,200);
    }

    public function show(Request $request, $id)
    {
        $this->validate($request, [
            'show_deletes_logs' => ['nullable', 'boolean'],
            'show_updates_logs' => ['nullable', 'boolean'],
        ]);
        $product = Product::with([
            'kind',
            'kind.unit',
            'deletes:id,product_id,amount',
            'model:id,name',
            'title:id,name',
            'state:id,name',
            'color:id,name',
            'storage:id,name',
            'buy_from_country:id,name:short_name',
            'made_in_country:id,name:short_name'
        ])
            ->where('company_id', $request->get('company_id'));


        if ($request->get('show_deletes_logs')) {
            $product->with([
                'deletes_logs',
                'deletes_logs.employee',
                'deletes_logs.employee.user:id,name,surname'
            ]);
        }
        if ($request->get('show_updates_logs')) {
            $product->with([
                'updates_logs',
                'updates_logs.employee',
                'updates_logs.employee.user:id,name,surname'
            ]);
        }


        $product = $product
            ->where('id', $id)
            ->first();
        $attach_count=ProductAssignment::query()
            ->where([
                'product_id'=>$id,
                'assignment_type'=>ProductAssignment::ATTACHMENT_TYPE
            ])
            ->get('amount');
        $operation_count=ProductAssignment::query()
            ->where([
                'product_id'=>$id,
                'assignment_type'=>ProductAssignment::OPERATION_TYPE
            ])
            ->get('amount');


        if (!$product)
            return $this->errorResponse(trans('response.ProductNotFound'));


        return $this->dataResponse([
            'product'=>$product,
            'attach_count'=>$attach_count,
            'operation_count'=>$operation_count],200);
    }

    public function showHistory(Request $request, $id)
    {
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
                'amount', 'id', 'initial_amount', 'kind_id', 'title_id', 'model_id', 'product_mark'
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
            $request->only('storage_id', 'title_id', 'state_id', 'sell_act_id')))
            return $this->errorResponse($notExists);
        $check = ProductKind::where([
            ['title_id', '=', $request->get('title_id')],
            ['company_id', '=', $request->get('company_id')],
            ['id', '=', $request->get('kind_id')],
        ])->exists();
        if (!$check) return $this->errorResponse(trans('response.productKindNotFoundOrNotBelongToTitle'), 400);
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
            'reasons' => ['required', 'array', 'min:1'],
            'reasons.*.reason' => ['required_with:reasons', 'string'],
        ]));

        DB::beginTransaction();


        $product = Product::where([
            ['company_id', '=', $request->get('company_id')],
            ['id', '=', $id],
        ])->first(Product::CAT_UPDATE);

        if (!$product)
            return $this->errorResponse(trans('response.productNotFound'), 422);

        $data = $request->only(Product::CAT_UPDATE);
        Product::where('id', $id)
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
        $this->validate($request, [
            'amount' => ['required', 'numeric'],
            'act' => ['nullable', 'mimes:png,jpg,pdf,doc,docx,xls,xlsx'],
        ]);
        $product = Product::where([
            ['id', '=', $id],
            ['company_id', '=', $request->get('company_id')],
        ])->first(['id', 'amount']);
        if (!$product) return $this->errorResponse(trans('response.productNotFound'), 404);

        $deleted = [
            'product_id' => $product->id,
            'employee_id' => Auth::user()->getEmployeeId($request->get('company_id')),
            'act' => $this->uploadFile($request->act, $request->get('company_id'), 'delete-acts'),
            'reason' => $request->get('reason')
        ];

        if ($request->has('amount')) {
            if ($product->amount < $request->get('amount'))
                return $this->errorResponse(trans('response.amountError'), 422);
            $product->decrement('amount', $request->get('amount'));
            $deleted['amount'] = $request->get('amount');
        } else {
            $product->update([
                'status' => Product::TOTAL_DELETED,
                'amount' => $product->amount
            ]);
            $deleted['amount'] = $product->amount;
        }

        ProductDelete::create($deleted);

        return $this->successResponse('ok');

    }

    public static function getValidationRules()
    {
        return [
            'floor'=>['nullable','integer',Rule::exists('floors','number')],
            'room'=>['nullable','integer'],
            'unit_id' => ['nullable', 'integer', 'min:1'],
            'less_value' => ['nullable', 'boolean'],
            'quickly_old' => ['nullable', 'boolean'],
            'title_id' => ['required', 'integer', 'min:1'],//
            'kind_id' => ['required', 'integer', 'min:1'],//
            'state_id' => ['required', 'integer'],
            'description' => ['nullable', 'string'],
            'amount' => ['nullable', 'numeric'],
            'storage_id' => ['required', 'integer'],//
//            'product_model' => ['nullable', 'string', 'max:255'],
            'product_mark' => ['nullable', 'string', 'max:255'],
            'color_id' => ['nullable', 'integer'],
            'main_funds' => ['nullable', 'boolean'],
            'inv_no' => ['nullable', 'string', 'max:255'],
            'exploitation_date' => ['nullable', 'date', 'date_format:Y-m-d'],
            'size' => ['nullable', 'numeric'],
            'made_in_country ' => ['nullable', 'integer', 'min:1'],
            'buy_from_country ' => ['nullable', 'integer', 'min:1'],
            'make_date' => ['nullable', 'date', 'date_format:Y-m-d'],
            'income_description' => ['nullable', 'string'],
            'model_id' => ['nullable', 'integer'],
            'sell_act_id' => ['nullable', 'integer'],
            'product_no' => ['nullable', 'max:255']
        ];
    }

    public static function getUpdateRules()
    {
        return [
            'product_no' => ['nullable', 'max:255'],
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

    public function getDeletes(Request $request)
    {
        $this->validate($request, [
            'product_id' => ['nullable', 'integer'],
            'employee_id' => ['nullable', 'integer'],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'per_page' => ['nullable', 'integer'],
            'to' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $deletes = ProductDelete::with([
            'employee',
            'employee.user',
            'product',
        ])->whereHas('product', function ($q) {
            $q->company();
        });

        if ($request->get('product_id'))
            $deletes->where('product_id', $request->get('product_id'));

        if ($request->get('employee_id'))
            $deletes->where('employee_id', $request->get('employee_id'));

        if ($request->get('from'))
            $deletes->where('created_at', '>=', $request->get('from'));

        if ($request->get('to'))
            $deletes->where('created_at', '<=', $request->get('to'));

        $deletes = $deletes->paginate($request->get('per_page'));

        return $this->successResponse($deletes);
    }

    public function filterProducts(Request $request)
    {

        $this->validate($request,[
            'company_id'=>'required',
            'title'=>'nullable|string',
            'title_id'=>'nullable|integer',
            'kind'=>'nullable|string',
            'amount'=>'nullable|integer',
            'floor'=>['nullable','integer',Rule::exists('floors','number')],
            'room'=>'nullable|integer',
            'per_page'=>'nullable|integer'
        ]);
        $per_page=$request->per_page ?? 10;
        $products=Product::query()
            ->select(['id','amount','room','floor','kind_id','state_id','model_id'])
            ->with([
                'kind:id,name,title_id',
                'state:id,name',
                'model:id,name',
            ]);

        if ($request->has('title'))
            $products->whereHas('title',function ($q) use ($request) {
                $q->where('name','like','%'.$request->get('title').'%');
            });
        if ($request->has('title_id'))
            $products->where('title_id',$request->get('title_id'));
        if ($request->has('kind'))
            $products->whereHas('kind',function ($q) use ($request) {
                $q->where('name','like','%'.$request->get('kind').'%');
            });
        if ($request->has('amount'))
            $products->where('products.amount',$request->get('amount'));
        if ($request->has('floor'))
            $products->where('products.floor',$request->get('floor'));
        if ($request->has('room'))
            $products->where('products.room',$request->get('room'));
        if (!$products->count()){
            return $this->errorResponse("There is no info!",404);
        }
        return $this->successResponse($products->paginate($per_page),200);


    }

    public function getKinds(Request $request)
    {
        $this->validate($request,[
            'company_id'=>'required',
            'title_id'=>'required|integer',
            'per_page'=>'nullable'
        ]);
        $per_page=$request->per_page ?? 10;

        $kinds=ProductKind::query()
            ->where('title_id',$request->title_id)
            ->withCount('products as productCount')
            ->paginate($per_page);

        return $this->dataResponse($kinds,200);
    }
}
