<?php

namespace Modules\Storage\Http\Controllers;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Storage\Entities\Unit;

class UnitController extends Controller
{
    use ApiResponse, ValidatesRequests;

    public function index()
    {

        return $this->successResponse(
            Unit::all()
        );
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => ['required', 'string', 'max:255']
        ]);
        Unit::create([
            'name' => $request->get('name')
        ]);
        return $this->successResponse('ok');
    }

    public function show($id)
    {
        $unit = Unit::where(
            'id' ,  $id
        )->first();
        return $this->successResponse($unit);
    }

    public function update(Request $request , $id){
        $unit = Unit::where(
            'id' ,  $id
        )->update([
            'name' => $request->get('name')
        ]);
        return $this->successResponse($unit);
    }

    public function delete($id){
//        $unit = Unit::where(
//            'id' ,  $id
//        )->delete();
        return $this->successResponse('ok');
    }
}
