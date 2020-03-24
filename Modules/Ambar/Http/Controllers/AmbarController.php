<?php

namespace Modules\Ambar\Http\Controllers;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Ambar\Entities\Ambar;

class AmbarController extends Controller
{
    use ApiResponse , ValidatesRequests;

    public function index(Request $request){
        $this->validate($request , [
            'company_id' => ['required' , 'integer'],
        ]);

        $ambar = Ambar::where('company_id' , $request->get('company_id'));
        return $this->successResponse($ambar);
    }

    public function store(Request $request){
        $this->validate($request , [
            'name' => ['required' , 'integer'],
        ]);
    }

}
