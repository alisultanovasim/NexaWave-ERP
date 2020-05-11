<?php

namespace Modules\Storage\Http\Controllers;

use App\Traits\ApiResponse;
use App\Traits\Query;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Hr\Traits\DocumentUploader;
use Modules\Storage\Entities\Product;
use Modules\Storage\Entities\ProductKind;
use Modules\Storage\Entities\ProductTitle;

class ProductKindController extends Controller
{
    use  ApiResponse, ValidatesRequests ,Query;

    public function index(Request $request)
    {
        $this->validate($request , [
            'is_filter' => ['sometimes' , 'required' , 'boolean']
        ]);
        $titles = ProductKind::query();
        if (!$request->get('is_filter'))
            $titles->with(['title']);
        $titles = $titles->where('company_id', $request->get('company_id'))->get();
        return $this->successResponse($titles);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => ['required', 'string', 'max:255'],
            'title_id' => ['required' , 'integer'],
            'unit_id' => ['required' , 'integer' , 'exists:units,id']
        ]);

        if ($notExists = $this->companyInfo($request->get('company_id') , $request->only('title_id')))
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
            'name' => ['required', 'string', 'max:255'],
            'title_id' => ['required' , 'integer']
        ]);
        $title = ProductKind::where([
            'id' => $id,
            'company_id' => $request->get('company_id')
        ])
            ->first(['id']);
        if ($notExists = $this->companyInfo($request->get('company_id') , $request->only('title_id')))
            return $this->errorResponse($notExists);
        if (!$title) return $this->errorResponse(trans('response.titleNotFound'), Response::HTTP_NOT_FOUND);

        ProductKind::where('id', $id)->update([
            'name' => $request->get('update')
        ]);
        return $this->successResponse('ok');
    }

    public function destroy(Request $request, $id)
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
