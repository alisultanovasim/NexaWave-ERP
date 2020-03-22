<?php


namespace Modules\Plaza\Http\Controllers;


use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Plaza\Entities\Guest;
use Modules\Plaza\Entities\Office;
use Modules\Plaza\Entities\Worker;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
class GuestController extends Controller
{
    use ApiResponse  , ValidatesRequests;

    public  function index(Request $request){
        $this->validate($request , [
            'company_id' => 'required|integer',
            'user_id' => 'required|integer',
            'from' => 'sometimes|required|date|date_format:Y-m-d H:i:s',
            'to' => 'sometimes|required|date|date_format:Y-m-d H:i:s',
            'per_page' => 'sometimes|integer',
            'office_id'=>'sometimes|required|integer',
        ]);

        $per_page = $request->per_page??10;

        try{
            $quests = Guest::with(['office:id,name' , 'worker:id,name'])->where('company_id' , $request->company_id);

            if ($request->has('office_id')){
                if ($request->office_id == 0 )  $quests->whereNull('office_id' );
                else
                    $quests->where('office_id' , $request->office_id);
            }

            if ($request->has('from'))
                $quests->where('come_at'  , ">=", $request->from);

            if ($request->has('to'))
                $quests->where('come_at'  , "<=", $request->to);

            $quests = $quests->paginate($per_page);

            return $this->successResponse($quests);

        }catch (\Exception $e){
              return   $this->errorResponse(trans('apiResponse.tryLater') , Response::HTTP_INTERNAL_SERVER_ERROR);
        }


    }

    public  function show(Request $request , $id){
        $this->validate($request , [
            'company_id' => 'required|integer',
            'user_id' => 'required|integer',
        ]);
        try{
            $quests = Guest::where('id' , $id)->where('company_id' , $request->company_id)->first();
            if (!$quests) return $this->errorResponse(trans('apiResponse.unProcess'));


            return $this->successResponse($quests);

        }catch (\Exception $e){
            $this->errorResponse(trans('apiResponse.tryLater') , Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(Request $request){
        $this->validate($request , [
            'name' => 'required|min:2|max:255',
            'come_at' => 'required|date|date_format:Y-m-d H:i:s',
            'description' => 'sometimes|required',
            'user_id' => 'required|integer',
            'office_id' => 'sometimes|required|integer',
            'company_id' => 'required|integer',
            'worker_id' => ['sometimes' , 'required' , 'integer']
        ]);
        $now = Carbon::now()->timezone('Asia/Baku')->toDateTimeString();
        if ($request->come_at < $now ) return $this->errorResponse(trans('apiResponse.TimeError'));

        try{
            if ($request->has('office_id')){
                $check = Office::where('id' , $request->office_id)
                    ->where('company_id' ,  $request->company_id)->exists();
                if (!$check) return $this->errorResponse(trans('apiResponse.officeNotFound'));
                if ($request->has('worker_id')){
                    $check = Worker::where('office_id' , $request->office_id)->where('id' , $request->worker_id)->exists();
                    if (!$check) return $this->errorResponse(trans('apiResponse.workerNotExists'));
                }
            }


            Guest::create($request->only('name','come_at' , 'description' , 'office_id' , 'company_id' , 'worker_id'));

            return $this->successResponse('OK');
        }catch (\Exception $exception){
            dd($exception);
            return $this->errorResponse(trans('apiResponse.tryLater') , Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(Request $request , $id){
        $this->validate($request , [
            'name' => 'sometimes|required|min:2|max:255',
            'come_at' => 'sometimes|required|date|date_format:Y-m-d H:i:s',
            'description' => 'sometimes|sometimes|required',
            'user_id' => 'required|integer',
            'office_id' => 'sometimes|required|integer',
            'company_id' => 'required|integer',
            'status'=>'sometimes|required|in:1,0'
        ]);
        try{
//            $check = Office::where('id' , $request->office_id)
//                ->where('company_id' ,  $request->company_id)->exists();
//            if (!$check) return $this->errorResponse(trans('apiResponse.unProcess'));

            $check = Guest::where([
                ['id' , $id],
                ['company_id' , $request->company_id]
            ])->first();
            if (!$check) return $this->errorResponse(trans('apiResponse.unProcess'));
            if ($request->has('office_id') and $check->office_id != $request->office_id) return $this->errorResponse(trans('apiResponse.notYourGuest'));

            if ($request->has('come_at')){
                $now = Carbon::now()->timezone('Asia/Baku')->toDateTimeString();
                if ($request->come_at < $now ) return $this->errorResponse(trans('apiResponse.TimeError'));
            }
            Guest::where('id' , $id)
                ->update($request->only('come_at' , 'description' , 'name' , 'status'));
//            if (!$check) return $this->errorResponse(trans('apiResponse.unProcess'));

            return $this->successResponse('OK');
        }catch (\Exception $e){

            return $this->errorResponse(trans('apiResponse.tryLater') , Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function delete(Request $request , $id){
        $this->validate($request , [
            'office_id' => 'required|integer',
            'company_id' => 'required|integer'
        ]);
        try{
            $check = Guest::where([
                ['id' , $id],
                ['office_id' , $request->office_id],
                ['company_id' , $request->company_id]
            ])->delete();
            if (!$check) return $this->errorResponse(trans('apiResponse.unProcess'));
            return $this->successResponse('OK');

        }catch (\Exception $exception){
            return $this->errorResponse(trans('apiResponse.tryLater') , Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

