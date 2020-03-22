<?php


namespace Modules\Plaza\Http\Controllers;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Plaza\Entities\Specialization;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
class SpecializationController extends  Controller
{

    use  ApiResponse  , ValidatesRequests;

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(){
        try{
            $specs = Specialization::get(['id' , 'name']);
            return $this->successResponse($specs);
        }catch (\Exception $exception){
           return $this->errorResponse(trans('apiResponse') , Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request){
        $this->validate($request , [
            'name'=>'required|string|max:255'
        ]);
        try{
            Specialization::create($request->only('name'));
            return $this->successResponse('OK');
        }catch (\Exception $exception){
            return $this->errorResponse(trans('apiResponse') , Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request , $id){
        try{
            $spec = Specialization::where('id' , $id)
                ->first();
            if (!$spec) return $this->errorResponse('not found' , Response::HTTP_NOT_FOUND);
            return $this->successResponse($spec);
        }catch (\Exception $exception){
            return $this->errorResponse(trans('apiResponse') , Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request , $id){
        $this->validate($request , [
            'name'=>'required|string|max:255'
        ]);
        try{
            $check = Specialization::where('id' , $id)
                ->update($request->only('name'));
            if (!$check)
                return $this->errorResponse(trans('apiResponse.nothingToUpdate'));
            return $this->successResponse('OK');
        }catch (\Exception $exception){
            return $this->errorResponse(trans('apiResponse') , Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request ,$id){
        try{
            $check = Specialization::where('id' , $id)
                ->delete();
            if (!$check)
                return $this->errorResponse(trans('apiResponse.unProcess'));
            return $this->successResponse('OK');
        }catch (\Exception $exception){
            return $this->errorResponse(trans('apiResponse') , Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
