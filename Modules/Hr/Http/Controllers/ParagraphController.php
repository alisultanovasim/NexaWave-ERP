<?php

namespace Modules\Hr\Http\Controllers;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Hr\Entities\Paragraph;

class ParagraphController extends Controller
{
    use ValidatesRequests , ApiResponse;
    public function index(Request $request)
    {
        $paragraphs = Paragraph::all();
        return $this->successResponse($paragraphs);
    }

    public function show(Request $request , $id){
        return $this->successResponse(
            Paragraph::with('fields')
                ->findOrFail($id)
        );
    }
}
