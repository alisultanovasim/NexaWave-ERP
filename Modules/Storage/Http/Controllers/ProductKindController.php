<?php

namespace Modules\Storage\Http\Controllers;

use App\Traits\ApiResponse;
use App\Traits\Query;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Storage\Entities\Product;
use Modules\Storage\Entities\ProductKind;

class ProductKindController extends Controller
{
    use  ApiResponse, ValidatesRequests, Query;

    public function index(Request $request)
    {
        $this->validate($request, [
            'is_filter' => ['sometimes', 'required', 'boolean'],
            'title_id' => ['required', 'integer'],
            "keyword" => ['sometimes', "required", "string", "min:1"]
        ]);


        $kinds = ProductKind::with(['unit'])
            ->withCount('products')
            ->company()
            ->where('title_id', $request->get('title_id'));


        if ($request->has("keyword"))
            $kinds = $kinds->where("name", "LIKE", "%" . $request->input("keyword") . "%");

        $kinds = $kinds->paginate($request->get('per_page'));

        return $this->dataResponse($kinds);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => ['required', 'string', 'max:255'],
            'title_id' => ['required', 'integer'],
            'unit_id' => ['required', 'integer', 'exists:units,id']
        ]);

        if ($notExists = $this->companyInfo($request->get('company_id'), $request->only('title_id')))
            return $this->errorResponse($notExists);

        ProductKind::create([
            'name' => $request->get('name'),
            'unit_id' => $request->get('unit_id'),
            'company_id' => $request->get('company_id'),
            'title_id' => $request->get('title_id')
        ]);


        return $this->successResponse('ok');
    }

    public function show(Request $request, $id)
    {
        $title = ProductKind::where([
            'id' => $id,
            'company_id' => $request->get('company_id')
        ])
            ->first();
        if (!$title) return $this->errorResponse(trans('response.titleNotFound'), Response::HTTP_NOT_FOUND);
        return $this->successResponse($title);
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => ['nullable', 'string', 'max:255'],
            'title_id' => ['nullable', 'integer']
        ]);
        $title = ProductKind::where([
            'id' => $id,
            'company_id' => $request->get('company_id')
        ])
            ->first(['id', 'title_id']);

        if (!$title) return $this->errorResponse(trans('response.titleNotFound'), Response::HTTP_NOT_FOUND);
        if ($title->title_id != $request->get('title_id'))
            if ($notExists = $this->companyInfo($request->get('company_id'), $request->only('title_id')))
                return $this->errorResponse($notExists);

        ProductKind::where('id', $id)->update($request->only('unit_id', 'name'));
        return $this->successResponse('ok');
    }

    public function delete(Request $request, $id)
    {

        $title = ProductKind::where([
            'id' => $id,
            'company_id' => $request->get('company_id')
        ])
            ->first(['id']);

        if (!$title) return $this->errorResponse(trans('response.titleNotFound'), Response::HTTP_NOT_FOUND);


        if (Product::where('kind_id', $id)->exists())
            return $this->errorResponse(trans('response.hasProductUnderTitle'));

        ProductKind::where('id', $id)->delete();

        return $this->successResponse('ok');
    }
}
