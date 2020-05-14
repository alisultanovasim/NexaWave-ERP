<?php

namespace Modules\Storage\Http\Controllers;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Storage\Entities\ProductState;

class ProductStateController extends Controller
{
    use ValidatesRequests, ApiResponse;

    public function index()
    {
        return $this->successResponse(
            ProductState::query()->company()->get()
        );
    }

    public function store(Request $request)
    {
        $this->validate($request , [
            'name'  => ['required' , 'string', 'max:255']
        ]);
        ProductState::create([
            'name' => $request->get('name'),
            'company_id' => $request->get('company_id'),
        ]);
        return $this->successResponse('ok');
    }

    public function show($id)
    {
        $data = ProductState::company()->where('id' , $id);
        if (!$data) return  $this->errorResponse(trans('response.statusNotFound') , 404);
        return $this->successResponse($data);

    }



    public function update(Request $request, $id)
    {
        ProductState::company()
            ->where('id' , $id)
            ->update($request->only('name'));
        return $this->successResponse('ok');
    }

    public function destroy($id)
    {
        ProductState::where('company_id' , \request('company_id'))
            ->where('id' ,$id)
            ->delete();
        return $this->successResponse('ok');
    }
}
