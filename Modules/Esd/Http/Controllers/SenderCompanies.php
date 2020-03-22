<?php


namespace Modules\Esd\Http\Controllers;


use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Entities\senderCompany;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controller;


class SenderCompanies extends  Controller
{
    use  ApiResponse  ,ValidatesRequests;

    public function index(Request $request){
        $this->validate($request, [
            'company_id' => 'required|integer',
            'name' => 'sometimes|required|max:255'
        ]);

        try{
            $companies = senderCompany::where('company_id' , $request->company_id);
            if ($request->has('name')) $companies->where('name' , 'like' , $request->name . "%");
            $companies = $companies->get(['id', 'name']);
            return $this->successResponse($companies);
        }catch (\Exception $exception){
            return $this->errorResponse(trans('apiResponse.tryLater') , Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }
    public function store(Request $request){
        $this->validate($request, [
            'company_id' => 'required|integer',
            'name'=> 'required|min:2|max:255'
        ]);
        try{
            senderCompany::create([
                'name'=> $request->name,
                'company_id' => $request->company_id
            ]);
            return $this->successResponse('OK');
        }catch (\Exception $exception){
            return $this->errorResponse(trans('apiResponse.tryLater') , Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function update(Request $request){
        $this->validate($request, [
            'company_id' => 'required|integer',
            'sender_company_id'=> 'required|integer',
            'name'=> 'required|min:2|max:255'
        ]);
        try{
            $check = senderCompany::where([
                'id'=> $request->sender_company_id,
                'company_id' => $request->company_id
            ])->update([
                'name' => $request->name
            ]);
            if (!$check) return $this->errorResponse(trans('apiResponse.unProcess'));
            return $this->successResponse('OK');
        }catch (\Exception $exception){
            return $this->errorResponse(trans('apiResponse.tryLater') , Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function delete(Request $request){
        $this->validate($request, [
            'company_id' => 'required|integer',
            'sender_company_id'=> 'required|integer',
        ]);
        try{
            $check = senderCompany::where([
                'id'=> $request->sender_company_id,
                'company_id' => $request->company_id
            ])->delete();
            if (!$check) return $this->errorResponse(trans('apiResponse.unProcess'));
            return $this->successResponse('OK');
        }catch (\Exception $exception){
            return $this->errorResponse(trans('apiResponse.tryLater') , Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    public function  getAllRegions(Request $request){
        $regions = DB::table('regions')->get();
        return $this->successResponse($regions);
    }
}
