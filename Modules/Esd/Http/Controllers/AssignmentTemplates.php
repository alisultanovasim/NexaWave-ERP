<?php


namespace Modules\Esd\Http\Controllers;


use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Esd\Entities\AssignmentTemplate;
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

        $companies = AssignmentTemplate::where('company_id' , $request->company_id)->get(['id', 'name']);
        return $this->successResponse($companies);


    }

    public function store(Request $request){
        $this->validate($request, [
            'company_id' => 'required|integer',
            'name'=> 'required|min:2'
        ]);
        AssignmentTemplate::create([
            'name'=> $request->name,
            'company_id' => $request->company_id
        ]);
        return $this->successResponse('OK');

    }

    public function update(Request $request){
        $this->validate($request, [
            'company_id' => 'required|integer',
            'assignment_template_id'=> 'required|integer',
            'name'=> 'required|min:2'
        ]);
            $check = AssignmentTemplate::where([
                'id'=> $request->assignment_template_id,
                'company_id' => $request->company_id
            ])->update([
                'name' => $request->name
            ]);
            if (!$check) return $this->errorResponse(trans('apiResponse.unProcess'));
            return $this->successResponse('OK');

    }

    public function delete(Request $request){
        $this->validate($request, [
            'company_id' => 'required|integer',
            'assignment_template_id'=> 'required|integer',
        ]);
            $check = AssignmentTemplate::where([
                'id'=> $request->assignment_template_id,
                'company_id' => $request->company_id
            ])->delete();
            if (!$check) return $this->errorResponse(trans('apiResponse.unProcess'));
            return $this->successResponse('OK');

    }

}
