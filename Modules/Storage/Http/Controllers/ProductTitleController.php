<?php

namespace Modules\Storage\Http\Controllers;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Storage\Entities\Product;
use Modules\Storage\Entities\ProductTitle;

class ProductTitleController extends Controller
{
    use  ApiResponse, ValidatesRequests;

    public function index(Request $request)
    {
        $this->validate($request, [
            'is_filter' => ['sometimes', 'required', 'boolean']
        ]);

        $titles = ProductTitle::query()
            ->where('company_id', $request->get('company_id'));
        if ($request->get('is_filter'))
            $titles = $titles->get(['id', 'name']);
        else
            $titles = $titles->withCount([
                'products',
                'kinds'
            ])
                ->orderBy('id', 'desc')
                ->paginate($request->get('per_page'));

        return $this->dataResponse($titles);
    }


    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => ['required', 'string', 'max:255']
        ]);
        ProductTitle::create([
            'name' => $request->get('name'),
            'company_id' => $request->get('company_id')
        ]);
        return $this->successResponse('ok');
    }

    public function show(Request $request, $id)
    {
        $title = ProductTitle::where([
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
            'name' => ['required', 'string', 'max:255']
        ]);
        $title = ProductTitle::where([
            'id' => $id,
            'company_id' => $request->get('company_id')
        ])
            ->first(['id']);
        if (!$title) return $this->errorResponse(trans('response.titleNotFound'), Response::HTTP_NOT_FOUND);

        ProductTitle::where('id', $id)->update([
            'name' => $request->get('name')
        ]);
        return $this->successResponse('ok');
    }

    public function delete(Request $request, $id)
    {

        $title = ProductTitle::where([
            'id' => $id,
            'company_id' => $request->get('company_id')
        ])
            ->first(['id']);

        if (!$title) return $this->errorResponse(trans('response.titleNotFound'), Response::HTTP_NOT_FOUND);


        if (Product::where('title_id', $id)->exists())
            return $this->errorResponse(trans('response.hasProductUnderTitle'));

        ProductTitle::where('id', $id)->delete();

        return $this->successResponse('ok');
    }
}
