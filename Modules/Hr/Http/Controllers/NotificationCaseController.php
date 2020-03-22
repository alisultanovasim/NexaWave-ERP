<?php

namespace Modules\Hr\Http\Controllers;

use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Hr\Entities\NotificationCase;
use App\Traits\ApiResponder;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use League\Flysystem\Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controller;

class NotificationCaseController extends Controller
{
    use ApiResponse, ValidatesRequests;

    public function index(Request $request){
        $this->validate($request , [
            'company_id' => ['required' , 'integer'],
            'paginateCount' => ['sometimes' , 'integer']
        ]);
        $result = NotificationCase::paginate($request->get('paginateCount'));
        return $this->dataResponse($result);
    }

    public function create(Request $request){
        $error = $this->validateRequest($request->all());
        if ($error) return $this->errorResponse($error, 422);
        try{
            DB::beginTransaction();
            $saved = true;
            NotificationCase::create([
                'type_id' => $request->get('type_id'),
                'notify_period' => $request->get('notify_period'),
                'notify_admin' => $request->get('notify_admin'),
                'notify_user' => $request->get('notify_user'),
            ]);
            DB::commit();
        }catch (\Exception $e){
            $saved = false;
            DB::rollBack();
        }
        return $saved
            ? $this->successResponse(trans('messages.saved'), 201)
            : $this->errorResponse(trans('messages.not_saved'));
    }


    public function update(Request $request, $id){
        $error = $this->validateRequest($request->all());
        if ($error) return $this->errorResponse($error, 422);
        try{
            DB::beginTransaction();
            $saved = true;
            $notificationCase = NotificationCase::where('id', $id)->where('company_id' , $request->get('company_id'))->exists();
            if (!$notificationCase)
                return $this->errorResponse(trans('messages.not_found'), 404);
            NotificationCase::where('id', $id)->update([
                'type_id' => $request->get('type_id'),
                'notify_period' => $request->get('notify_period'),
                'notify_admin' => $request->get('notify_admin'),
                'notify_user' => $request->get('notify_user'),
            ]);
            DB::commit();
        }catch (\Exception $e){
            $saved = false;
            DB::rollBack();
        }
        return $saved
            ? $this->successResponse(trans('messages.saved'))
            : $this->errorResponse(trans('messages.not_saved'));
    }

    public function destroy(Request $request, $id){
        $this->validate($request , [
            'company_id' => ['required' , 'integer']
        ]);
        return NotificationCase::where('id', $id)->where('company_id' , $request->get('company_id'))->delete()
            ? $this->successResponse(trans('messages.saved'))
            : $this->errorResponse(trans('messages.not_saved'));
    }

    protected function validateRequest($input){
        $validationArray = [
            'type_id' => 'required|exists:notification_types,id',
            'notify_period' => 'required|integer',
            'notify_admin' => 'required|boolean',
            'notify_user' => 'required|boolean',
        ];
        $validator = \Validator::make($input, $validationArray);

        if($validator->fails())
            return $validator->errors();
        return null;
    }
}
