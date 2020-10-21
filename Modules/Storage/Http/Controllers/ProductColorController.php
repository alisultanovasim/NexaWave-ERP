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
    public function index()
    {
        $data = ProductColor::all();
        return $this->successResponse($data);
    }
}
