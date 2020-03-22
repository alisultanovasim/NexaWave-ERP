<?php


namespace Modules\Esd\Http\Controllers;


use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Entities\AssignmentTemplate;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class AssignmentTemplates extends  Controller
{
    use  ApiResponse  ,ValidatesRequests;

    public function index(Request $request){
        $this->validate($request, [
            'company_id' => 'required|integer',
        ]);

        try{
            $companies = AssignmentTemplate::where('company_id' , $request->company_id)->get(['id', 'name']);
            return $this->successResponse($companies);
        }catch (\Exception $exception){
            return $this->errorResponse(trans('apiResponse.tryLater') , Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function store(Request $request){
        $this->validate($request, [
            'company_id' => 'required|integer',
            'name'=> 'required|min:2'
        ]);
        try{
            AssignmentTemplate::create([
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
            'assignment_template_id'=> 'required|integer',
            'name'=> 'required|min:2'
        ]);
        try{
            $check = AssignmentTemplate::where([
                'id'=> $request->assignment_template_id,
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
            'assignment_template_id'=> 'required|integer',
        ]);
        try{
            $check = AssignmentTemplate::where([
                'id'=> $request->assignment_template_id,
                'company_id' => $request->company_id
            ])->delete();
            if (!$check) return $this->errorResponse(trans('apiResponse.unProcess'));
            return $this->successResponse('OK');
        }catch (\Exception $exception){
            return $this->errorResponse(trans('apiResponse.tryLater') , Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}
