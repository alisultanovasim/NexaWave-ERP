<?php


namespace Modules\Plaza\Http\Controllers;


use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Plaza\Entities\Attendance;
use Modules\Plaza\Entities\Card;
use Modules\Plaza\Entities\Worker;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controller;
class AttendanceController extends Controller
{
    use ApiResponse , ValidatesRequests;

    public function index(Request $request)
    {
        $this->oneValidator($request);
        try {

            $attendance = Attendance::where('company_id', $request->company_id);

            if ($request->has('card')) {
                $attendance->where('card_id', $request->card);
            }
            if ($request->has('date'))
                $attendance->where(DB::raw('DATE(timeStamp)'), $request->date);

            if ($request->has('worker_id')) {
                $worker = Worker::whereHas('office', function ($q) use ($request) {
                    $q->where('company_id', $request->company_id);
                })->where('id', $request->worker_id)->first(['id' ,'card']);
                if (!$worker) return  $this->errorResponse(trans('apiResponse.unProcess'));
                if ($worker->card === null) $this->successResponse("No data");

                $card = Card::where('id' , $worker->card)->first('alias');
                $attendance->where('card_id', $card->alias);
            }
            if ($request->has('card_id')) {
                $card = Card::where('id', $request->card_id)->first('alias');
                if (!$card) return  $this->errorResponse(trans('apiResponse.unProcess'));
                    $attendance->where('card_id', $card->alias);
            }

            if ($request->has('from'))
                $attendance->where('timeStamp', ">=", $request->from);

            if ($request->has('to'))
                $attendance->where('timeStamp', "<=", $request->to);

            if ($request->has('action_type')) {
                $this->getActionType($request->action_type , $attendance);
            }

            if ($request->has('office_id')){
                $attendance->whereHas('card.worker',function ($q) use ($request){
                    $q->where('office_id' , $request->office_id)
                        ->where('company_id' , $request->company_id);
                });
            }

            $attendance = $attendance->orderBy('timeStamp','desc')->paginate($request->per_page ?? 30);

            if ($request->has('with_workers')){
                $attendance->load([ 'card:id,alias','card.worker:id,name,card']);
            }

            return $this->successResponse($attendance);
        } catch (\Exception $exception) {
            return $this->errorResponse(trans('apiResponse.TryLater'));
        }
    }

    public function showByOffice(Request $request){
        $this->oneValidator($request);
        try{

            $workers = Worker::with(['card:id,alias' , 'card.attendance' => function($q) use ($request){
                if ($request->has('from'))
                    $q->where('timeStamp', ">=", $request->from);

                if ($request->has('to'))
                    $q->where('timeStamp', "<=", $request->to);

                if ($request->has('date'))
                    $q->where(DB::raw('DATE(timeStamp)'), $request->date);

                if ($request->has('action_type')) {
                    $this->getActionType($request->action_type , $q);
                }

                $q->select(['id' , 'timestamp' , 'action_type' , 'card_id']);

            }]);

            if($request->has('office_id')){
                $workers->whereHas('office' , function ($q) use ($request){
                    $q->where('id',$request->office_id)
                        ->where('company_id' , $request->company_id);
                });
            }else{
                $workers->with(['office:id,name']);
            }
            $workers = $workers->paginate($request->per_page??20 , ['id','name','card' , 'office_id']);
            return $this->successResponse($workers);
        }catch (\Exception $exception){
            return $this->errorResponse($exception->getMessage() );
        }
    }

    private  function oneValidator(Request $request){
        $this->validate($request, [

            'company_id' => ['required', 'integer'],
            'card_id' => ['sometimes', 'required'],
            'date' => ['sometimes', 'required', 'date', 'date_format:Y-m-d'],
            'from' => ['sometimes', 'required', 'date'],
            'to' => ['sometimes', 'required', 'date'],
            'action_type' => ['sometimes', 'required', 'array'],
            'action_type.*' => ['sometimes', 'required', 'integer'],
            'per_page' => ['sometimes', 'required', 'integer'],
            'office_id' => ['sometimes' , 'integer']
        ]);
    }
    public function store(Request $request)
    {

        $this->validate($request, [
            'events' => ['required', 'array'],
            'events.*.card_id' => ['required'],
            'events.*.incoming_id' => ['sometimes', 'required'],
            'events.*.timeStamp' => ['required', 'date'],
            'events.*.action_type' => ['required']
        ]);
        try {
            Attendance::insertOrIgnore($request->events);
            return $this->successResponse('ok', Response::HTTP_OK);
        } catch (\Exception $exception) {
            return $this->errorResponse($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function getActionType($actionType , $query){
        $query->where(function ($q)use ($actionType){
            foreach ($actionType as $type){
                switch ($type) {
                    case 0 :
                        $q->orWhere('action_type', config('plaza.action_type.out'));
                        break;
                    case 1 :
                        $q->orWhere('action_type', config('plaza.action_type.in'));
                        break;
                    case 2 :
                        $q->orWhereNotIn('action_type', [config('plaza.action_type.out'), config('plaza.action_type.in')]);
                        break;
                }
            }
        });

    }
}
