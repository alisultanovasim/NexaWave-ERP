<?php

namespace Modules\Storage\Http\Controllers;

use App\Traits\ApiResponse;
use App\Traits\Query;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Storage\Entities\Product;
use Modules\Storage\Entities\ProductKind;

class ProductController extends Controller
{
    use  ApiResponse, ValidatesRequests , Query;

    public function index(Request $request)
    {
        $this->validate($request, [
            'storage_id' => ['nullable', 'integer', 'min:1'],
            'title_id' => ['nullable', 'integer', 'min:1'],
            'kind_id' => ['nullable' , 'integer' , 'min:1'],
            'name' => ['nullable', 'string', 'max:255'],
            'per_page' => ['nullable', 'integer', 'min:1']
        ]);

        $products = Product::with([
            'kind',
            'title',
            'state',
            'color',
        ])
        ->where('company_id', $request->get('company_id'));

        if ($request->get('name'))
            $products->where('name', 'like', $request->get('name') . '%');

        if ($request->get('kind_id'))
            $products->where('kind_id', $request->get('kind_id'));

        if ($request->get('title_id'))
            $products->where('title_id', $request->get('title_id'));

        $products = $products->orderBy('id' , 'desc')->paginate($request->get('per_page'));

        return $this->successResponse($products);

    }

    public function show(Request $request, $id)
    {
        $product = Product::with([
            'kind',
            'title',
            'state',
            'color',
        ])
            ->where('company_id', $request->get('company_id'))
            ->where('id', $id)
            ->first();

        if (!$product)
            return $this->errorResponse(trans('response.ProductNotFound'));

        return $this->successResponse($product);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'unit_id' => ['required' , 'integer' , 'min:1'],
            'less_value' => ['required' , 'boolean'],
            'quickly_old' => ['required' , 'boolean'],
            'title_id' => ['required', 'integer', 'min:1'],//
            'kind_id' => ['required', 'integer', 'min:1'],//
            'state_id' => ['required', 'integer'],
            'description' => ['nullable', 'string'],
            'amount' => ['required', 'numeric'],
            'storage_id' => ['required' , 'integer'],
            'product_model' => ['nullable' , 'string' , 'max:255'],
            'product_mark' => ['nullable' , 'string' , 'max:255'],
            'color_id' => ['nullable' , 'integer'],//
            'main_funds' => ['nullable' , 'boolean'],
            'inv_no' => ['nullable' , 'string' , 'max:255'],
            'exploitation_date' => ['nullable' , 'date' ,'date_format:Y-m-d'],
            'size' => ['nullable' , 'numeric'],
            'made_in_country ' => ['nullable' , 'integer' , 'min:1'],//
            'buy_from_country ' => ['nullable' , 'integer' , 'min:1'],//
            'make_date' =>  ['nullable' , 'date' ,'date_format:Y-m-d'],
            'income_description' => ['nullable', 'string'],
        ]);
        DB::beginTransaction();
        $product = Product::where([
            ['company_id', '=', $request->get('company_id')],
            ['title_id', '=', $request->get('title_id')],
            ['kind_id', '=', $request->get('kind_id')],
            ['state_id', '=', $request->get('state_id')],
            ['inv_no' , '=' , $request->get('inv_no')],
            ['unit_id' , '=' , $request->get('unit_id')],
            ['color_id' , '=' , $request->get('color_id')],
            ['buy_from_country' , '=' , $request->get('buy_from_country')],
            ['made_in_country' , '=' , $request->get('made_in_country')],
            ['make_date' , '=' , $request->get('make_date')],
            ['size' , '=' , $request->get('size')],
            ['product_model' , '=' , $request->get('product_model')],
            ['product_mark' , '=' , $request->get('product_mark')],
            ['product_no' , '=' , $request->get('product_no')],
            ['exploitation_date' , '=' , $request->get('exploitation_date')],
            ['main_funds'  , '='  ,  $request->get('main_funds')]
        ])->first(['id']);
        if ($product) {
            Product::where('id', $product->id)
                ->increment('amount', $request->get('amount'));
        } else {
            if ($notExists = $this->companyInfo(
                    $request->get('company_id'),
                    $request->only('storage_id' , 'title_id', 'state_id')))
                return $this->errorResponse($notExists);

            $check = ProductKind::where([
                ['company_id' , '=' , $request->get('company_id')],
                ['id' , '=' , $request->get('kind_id')],
            ])->exists();
            if (!$check) return $this->errorResponse(trans('response.fieldIsNotFindInDatabase'));

            Product::create($request->all());
        }
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

    public function update(Request $request , $id){
        $this->validate($request, [
            'unit_id' => ['nullable' , 'integer' , 'min:1'],
            'less_value' => ['nullable' , 'boolean'],
            'quickly_old' => ['nullable' , 'boolean'],
            'title_id' => ['nullable', 'integer', 'min:1'],//
            'kind_id' => ['nullable', 'integer', 'min:1'],//
            'state_id' => ['nullable', 'integer'],
            'description' => ['nullable', 'string'],
            'amount' => ['nullable', 'numeric'],
            'storage_id' => ['nullable' , 'integer'],
            'product_model' => ['nullable' , 'string' , 'max:255'],
            'product_mark' => ['nullable' , 'string' , 'max:255'],
            'color_id' => ['nullable' , 'integer'],//
            'main_funds' => ['nullable' , 'boolean'],
            'inv_no' => ['nullable' , 'string' , 'max:255'],
            'exploitation_date' => ['nullable' , 'date' ,'date_format:Y-m-d'],
            'size' => ['nullable' , 'numeric'],
            'made_in_country ' => ['nullable' , 'integer' , 'min:1'],//
            'buy_from_country ' => ['nullable' , 'integer' , 'min:1'],//
            'make_date' =>  ['nullable' , 'date' ,'date_format:Y-m-d'],
            'income_description' => ['nullable', 'string'],
        ]);
        $product = Product::where([
            ['company_id', '=', $request->get('company_id')],
            ['id', '=',$id],
        ])->exists();

        if (!$product)
            return $this->errorResponse(trans('response.productNotFound'), 422);

        if ($notExists = $this->companyInfo(
            $request->get('company_id'),
            $request->only('storage_id' , 'title_id', 'state_id')))
            return $this->errorResponse($notExists);

        if ($request->has('kind_id')){
            $check = ProductKind::where([
                ['company_id' , '=' , $request->get('company_id')],
                ['id' , '=' , $request->get('kind_id')],
            ])->exists();
            if (!$check) return $this->errorResponse(trans('response.fieldIsNotFindInDatabase'));
        }

        Product::where('id' , $id)
            ->update($request->all());

    }

    public function delete(Request $request, $id){
        Product::where([
            ['id' , '=' , $id],
            ['company_id' , '=' , $request->get('company_id')],
        ])->delete();
        return $this->successResponse('ok');

    }

}
