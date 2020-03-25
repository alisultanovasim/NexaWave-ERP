<?php


namespace Modules\Esd\Http\Controllers;


use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Esd\Entities\Adjustment;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\Console\Exception\CommandNotFoundException;
use Illuminate\Routing\Controller;

class  AdjustmentController extends Controller
{
    use ApiResponse ,ValidatesRequests;

    public function index(Request $request){
        $this->validate($request , [

            'company_id' => 'required|integer',
            'section_type' => 'required|integer',
        ]);
        try{
            $adjustments = Adjustment::with([])->where('user_id' ,$request->user_id)->where('section_type' , $request->section_type)->get([
                'name',
                'field',
                'is_active',
                'position' ,
                'type'
            ]);
            if ($adjustments->count() == 0){
                $arr = config("esd.document.adjustments.initial_rules.coulmns{$request->section_type}");
                if($arr == null) return $this->successResponse([]);
                $sendArr = [];
                foreach ($arr  as $k => $v) {
                    $arr[$k]['type'] = (int)$arr[$k]['type'];
                    $arr[$k]['is_active'] = (int)$arr[$k]['is_active'];
                    $arr[$k]['position'] = (int)$arr[$k]['position'];

                    $sendArr[$k] = $arr[$k];
                    $arr[$k]['user_id'] = $request->user_id;
                    $arr[$k]['section_type'] = $request->section_type;
                }

                Adjustment::insert($arr);
                unset($arr);
                $adjustments = $sendArr;
            }
            return $this->successResponse($adjustments);
        }catch (\Exception $exception){
            return $this->errorResponse(trans('apiResponse.tryLater') , Response::HTTP_INTERNAL_SERVER_ERROR);

        }
    }

    public function update(Request $request){
        $this->validate($request , [

            'company_id' => 'required|integer',
            'data'=> 'required|array',
            'data.*.name' => 'required|string|max:255',
            'data.*.is_active'  => 'required|boolean' ,
            'data.*.position' => 'required|integer',
            'data.*.type' => 'required|in:1,2',
            'data.*.field' => 'required|string|max:255',
            'section_type' => 'sometimes|integer'
        ]);
        try{
            Adjustment::where('user_id' , $request->user_id)->where('section_type' , $request->section_type)->delete();
            $arr = $request->data;
            foreach ($arr  as $k => $v) {
                $arr[$k]['user_id'] = $request->user_id;
                $arr[$k]['section_type'] = $request->section_type;
            }
            Adjustment::insert($arr);
            return $this->successResponse("OK");
        }catch (\Exception $exception){
            return $this->errorResponse(trans('apiResponse.tryLater') , Response::HTTP_INTERNAL_SERVER_ERROR);

        }
    }
}
