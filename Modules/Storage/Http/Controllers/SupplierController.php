<?php

namespace Modules\Storage\Http\Controllers;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Storage\Entities\Supplier;

class SupplierController extends Controller
{
    use ValidatesRequests, ApiResponse;

    public function index()
    {
        $data = Supplier::company()->get();
        return $this->successResponse($data);
    }


    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => ['required', 'string', 'max:255'],
        ]);

        Supplier::create([
            'name' => $request->get('name'),
            'company_id' => $request->get('company_id'),
        ]);


        return $this->successResponse('ok');

    }

    public function show($id)
    {
        $data = Supplier::company()->where('id', $id)
            ->first();

        return $this->successResponse($data);
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => ['required', 'integer']
        ]);
        $data = Supplier::company()->where('id', $id)
            ->first(['id']);

        if (!$data) return $this->errorResponse(trans('response.supplierNotFound'));
        $data->update([
            'name' => $request->get('name')
        ]);
        return $this->successResponse('ok');
    }

    public function destroy($id)
    {
        $data = Supplier::company()
            ->where('id', $id)
            ->first(['id']);

        if (!$data) return $this->errorResponse(trans('response.supplierNotFound'));
        $data->delete();

        return $this->successResponse('ok');
    }
}
