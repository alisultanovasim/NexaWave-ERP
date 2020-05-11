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
use Modules\Storage\Entities\Income;
use Modules\Storage\Entities\Outcome;
use Modules\Storage\Entities\Product;
use Modules\Storage\Entities\ProductTitle;

class ProductController extends Controller
{
    use  ApiResponse, ValidatesRequests , Query;

    public function index(Request $request)
    {
        $this->validate($request, [
            'storage_id' => ['nullable', 'integer', 'min:1'],
            'title_id' => ['nullable', 'integer', 'min:1'],
            'name' => ['nullable', 'string', 'max:255'],
            'per_page' => ['nullable', 'integer', 'min:1']
        ]);

        // for product filter
        $filter = function ($q) use ($request) {
            $q->with("kind:id,name,unit_id" ,'kind.unit' );
            if ($request->get('storage_id')) $q->where('storage_id', $request->get('storage_id'));
        };

        $products = ProductTitle::with(['kinds' , 'kinds.products' => $filter])
            ->where('company_id', $request->get('company_id'));

        if ($request->get('name'))
            $products->where('name', 'like', $request->get('name') . '%');

        $products = $products->paginate($request->get('per_page'));

        return $this->successResponse($products);

    }

    public function show(Request $request, $id)
    {
        $product = ProductTitle::with(['products' , 'products.kind' ,  'products.kind.unit'])
            ->where('company_id', $request->get('company_id'))
            ->where('id', $id)
            ->first();
        if (!$product) return $this->errorResponse(trans('response.productNotFound'));
        return $this->successResponse($product);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'title_id' => ['required', 'integer', 'min:1'],
            'kind_id' => ['required', 'integer', 'min:1'],
            'description' => ['nullable', 'string'],
            'state' => ['required', 'integer'],
            'amount' => ['required', 'numeric'],
            'income_description' => ['nullable', 'string'],
            'storage_id' => ['required' , 'integer'],
            'unit_id' => ['required' , 'integer' , 'min:1'],
            'less_value' => ['required' , 'boolean'],
            'quickly_old' => ['required' , 'boolean'],
        ]);
        DB::beginTransaction();
        $product = Product::where([
            ['title_id', '=', $request->get('title_id')],
            ['kind_id', '=', $request->get('kind_id')],
            ['state', '=', $request->get('state')],
            ['company_id', '=', $request->get('company_id')],
        ])->first(['id']);
        if ($product) {
            Product::where('id', $product->id)
                ->increment('amount', $request->get('amount'));
        } else {
            if ($notExists = $this->companyInfo($request->get('company_id'), $request->only('storage_id' , 'title_id', 'kind_id')))
                return $this->errorResponse($notExists);
            $product = Product::create($request->only([
                'title_id',
                'kind_id',
                'description',
                'state',
                'amount',
                'unit_id',
                'less_value' ,
                'quickly_old',
                'description',
                'storage_id',
                'company_id'
            ]));
        }


        Income::create([
            'description' => $request->get('income_description'),
            'company_id' => $request->get('company_id'),
            'product_id' => $product->id,
            'amount' => $request->get('amount')
        ]);

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

        Income::create([
            'user_id' => Auth::id(),
            'product_id' => $id,
            'amount' => $request->get('amount'),
            'description' => $request->get('income_description'),
            'company_id' => $request->get('company_id')

        ]);
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

        Outcome::create([
            'user_id' => Auth::id(),
            'product_id' => $id,
            'amount' => $request->get('amount'),
            'description' => $request->get('outcome_description'),
            'company_id' => $request->get('company_id')
        ]);

        DB::commit();

        return $this->successResponse('ok');
    }

    public function delete(Request $request, $id){
        Product::where([
            ['id' , '=' , $id],
            ['company_id' , '=' , $request->get('company_id')],
        ])->delete();
        return $this->successResponse('ok');

    }

}
