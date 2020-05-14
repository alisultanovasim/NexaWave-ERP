<?php

namespace Modules\Storage\Http\Controllers;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Storage\Entities\ProductColor;
use Modules\Storage\Entities\ProductKind;

class ProductColorController extends Controller
{
    use  ApiResponse, ValidatesRequests;
    public function index(Request $request)
    {
        $data = ProductColor::where('company_id' , $request->get('company_id'))->get();
        return $this->successResponse($data);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => ['required', 'string', 'max:255'],
            'hex' => ['nullable' , 'string' , 'max:255']
        ]);

        ProductColor::create($request->only('name' , 'hex' , 'company_id'));

        return $this->successResponse('ok');
    }

    public function show(Request $request, $id)
    {
        $this->validate($request, [
            'name' => ['required', 'string', 'max:255'],
        ]);

        $data = ProductColor::where('id' , $id)->where('company_id' , $request->get('company_id'))->first();


        return $this->successResponse($data);
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => ['required', 'string', 'max:255'],
            'hex' => ['nullable' , 'string' , 'max:255']
        ]);

        ProductColor::where('id' , $id)
            ->where('company_id' , $request->get('company_id'))
            ->update($request->only('hex' , 'name'));

        return $this->successResponse('ok');
    }

    public function delete(Request $request, $id)
    {
        ProductColor::where('company_id' , $request->get('company_id'))
            ->where('id', $id)
            ->delete();

        return $this->successResponse('ok');
    }
}
