<?php


namespace Modules\Hr\Http\Controllers;


use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Hr\Entities\WorkShift;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controller;

class WorkShiftController extends Controller
{
    use ApiResponse, ValidatesRequests;

    public function index(Request $request)
    {
        $this->validate($request , [
            'company_id' => ['required' , 'integer'],
            'paginateCount' => ['sometimes','required' , 'integer'],
        ]);
        $result = WorkShift::where('company_id' , $request->get('company_id'))->with([
            'contract' => function ($query){
                $query->select(['id', 'name']);
            }
        ])->paginate($request->get('company_id'));


        return $this->dataResponse($result);
    }

    public function create(Request $request)
    {
        $error = $this->validateRequest($request->all());
        if ($error)
            return $this->errorResponse($error, 422);
        try
        {
            DB::beginTransaction();
            $saved = true;
            WorkShift::create([
                'contract_id' => $request->get('contract_id'),
                'shift_number' => $request->get('shift_number'),
                'start_time' => $request->get('start_time'),
                'end_time' => $request->get('end_time'),
                'position' => $request->get('position'),
                'company_id' => $request->get('company_id')
            ]);
            DB::commit();
        }
        catch (\Exception $exception)
        {
            $saved = false;
            DB::rollBack();
        }
        return $saved
            ? $this->successResponse(trans('messages.saved'), 201)
            : $this->errorResponse(trans('messages.not_saved'));
    }

    public function update(Request $request, $id)
    {
        $error = $this->validateRequest($request->all());
        if ($error)
            return $this->errorResponse($error, 422);
        try
        {
            DB::beginTransaction();
            $saved = true;
            $workShift = WorkShift::where('id', $id)->where('company_id' , $request->get('company_id'))->first(['id']);
            if (!$workShift)
                return $this->errorResponse(trans('messages.not_found'), 404);
            $workShift->update([
                'contract_id' => $request->get('contract_id'),
                'shift_number' => $request->get('shift_number'),
                'start_time' => $request->get('start_time'),
                'end_time' => $request->get('end_time'),
                'position' => $request->get('position')
            ]);
            DB::commit();
        }
        catch (\Exception $e)
        {
            $saved = false;
            DB::rollBack();
        }
        return $saved
            ? $this->successResponse(trans('messages.saved'))
            : $this->errorResponse(trans('messages.not_saved'));
    }

    public function destroy(Request $request ,  $id)
    {
        return WorkShift::where('id', $id)->where('company_id', $request->get('company_id'))->delete()
            ? $this->successResponse(trans('messages.saved'))
            : $this->errorResponse(trans('messages.not_saved'));
    }

    protected function validateRequest($input){
        $validationArray = [
            'contract_id' => 'numeric|exists:contracts,id',
            'shift_number' => 'required|numeric|min:1|max:5',
            'start_time' => 'date_format:H:i',
            'end_time' => 'date_format:H:i',
            'position' => 'required|numeric',
            'company_id' => ['required' , 'integer'],
        ];
        $validator = \Validator::make($input, $validationArray);

        if($validator->fails())
            return $validator->errors();
        return null;
    }
}
