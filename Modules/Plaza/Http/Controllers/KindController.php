<?php


namespace Modules\Plaza\Http\Controllers;


use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Plaza\Entities\Kind;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
class KindController extends  Controller
{
    use ApiResponse  , ValidatesRequests;

    public function index(Request $request){
        $this->validate($request , [
            'company_id' => 'required|integer'
        ]);
        try{
            $kinds = Kind::all();
            return $this->successResponse($kinds);
        }catch (\Exception $e){
            return $this->errorResponse(trans('apiResponse.tryLater') , Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
