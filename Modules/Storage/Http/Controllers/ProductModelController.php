<?php

namespace Modules\Storage\Http\Controllers;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Storage\Entities\ProductKind;
use Modules\Storage\Entities\ProductModel;

class ProductModelController extends Controller
{
    use ValidatesRequests, ApiResponse;

    public function index(Request $request)
    {
        $this->validate($request, [
            'kind_id' => ['required', 'integer']
        ]);
        $models = ProductKind::with('models')->company()->where('id', request('company_id'))
            ->first();
        return $this->successResponse($models);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'kind_id' => ['required', 'integer'],
            'name' => ['required', 'string', 'max:255'],
        ]);

        $check = ProductKind::company()->where('id', $request->get('company_id'))->exists();
        if (!$check) return $this->errorResponse(trans('response.kindNotFound'), 404);
        ProductModel::create([
            'name' => $request->get('name'),
            'kind_id' => $request->get('kind_id')
        ]);

        return $this->successResponse('ok');
    }

    public function show(Request $request, $id)
    {
        $this->validate($request, [
            'kind_id' => ['required', 'integer'],
            'name' => ['required', 'string', 'max:255'],
        ]);

        $data = ProductModel::company()->where('id', $id)->first();

        return $this->successResponse($data);
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'kind_id' => ['required', 'integer'],
            'name' => ['required', 'string', 'max:255'],
        ]);
        if ($request->has('kind_id'))
            if (!ProductKind::company()->where('id', $request->get('kind_id'))->exists())
                return $this->errorResponse(trans('response.kindNofFound'));

        ProductModel::company()
            ->where('id', $id)
            ->update($request->only('name', 'kind_id'));

        return $this->successResponse('ok');
    }

    public function delete(Request $request, $id)
    {
        ProductModel::company()
            ->where('id', $id)
            ->delete();

        return $this->successResponse('ok');
    }
}
