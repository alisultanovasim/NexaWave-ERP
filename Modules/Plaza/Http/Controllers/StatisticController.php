<?php


namespace Modules\Plaza\Http\Controllers;


use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Plaza\Entities\Dialog;
use Modules\Plaza\Entities\Floor;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controller;
class StatisticController extends Controller
{

    use ApiResponse  , ValidatesRequests;

    public function floorsStatistic(Request $request){
        $this->validate($request , [
            'user_id' => 'sometimes|integer',
            'company_id' => 'required|integer'
        ]);
        try{
            $statistics = Floor::where('company_id' , $request->company_id)->first(DB::raw('sum(common_size) as common , sum(sold_size) as sold_size'));
            return $this->successResponse($statistics);
        }catch (\Exception $exception){
            return $this->errorResponse(trans('apiResponse.tryLater') , Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }
    public function dialogsStatistic(Request $request){
        $this->validate($request , [
            'user_id' => 'sometimes|integer',
            'company_id' => 'required|integer'
        ]);
        try{
            $statistics = Dialog::where('company_id' , $request->company_id)->count();
            return $this->successResponse([
                'count' => $statistics
            ]);
        }catch (\Exception $e){
            return $this->errorResponse(trans('apiResponse.tryLater') , Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
