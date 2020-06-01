<?php

namespace Modules\Storage\Http\Controllers;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class ProductAssignmentController extends Controller
{
    use ValidatesRequests , ApiResponse;
    public function index(Request $request)
    {
        $this->validate($request , [
            'employee_id' => ['nullable'  , 'integer'],
        ]);

    }

    public function store(Request $request)
    {
    }

    public function show(Request $request , $id)
    {
    }

    public function update(Request $request, $id)
    {
    }
    public function delete(Request $request,$id)
    {
    }
}
