<?php


namespace Modules\Hr\Http\Controllers;


use App\Traits\ApiResponse;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Hr\Entities\Contract;

class ContractController extends Controller
{
    use ApiResponse, ValidatesRequests;

    public function getContactStatics()
    {
        $contracts=\Modules\Hr\Entities\Employee\Contract::where('employee_id',\Auth::id())->sum('salary');
        return $this->dataResponse([
            'count' => 200,
            'contracts'=>$contracts
        ]);
    }

    public function index(Request $request)
    {
        $paginateCount = $request->filled('paginateCount')
            ? $request->get('paginateCount')
            : config('defaults.paginateCount');
        $result = Contract::with('workShifts')->paginate($paginateCount);

        return $this->dataResponse($result);
    }

    public function create(Request $request)
    {
        $error = $this->validateRequest($request->all());
        if ($error)
            return $this->errorResponse($error, 422);
        try {
            DB::beginTransaction();
            $saved = true;
            Contract::create([
                'name' => $request->get('name'),
                'code' => $request->get('code'),
                'start_time' => $request->get('start_time'),
                'end_time' => $request->get('end_time'),
                'lunch_start_time' => $request->get('lunch_start_time'),
                'lunch_end_time' => $request->get('lunch_end_time'),
                'first_rest_day' => $request->get('first_rest_day'),
                'second_rest_day' => $request->get('second_rest_day'),
                'daily_norm_hour' => $request->get('daily_norm_hour'),
                'note' => $request->get('note'),
                'position' => $request->get('position'),
                'company_id' => $request->get('company_id')
            ]);
            DB::commit();
        } catch (\Exception $exception) {
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
        try {
            DB::beginTransaction();
            $saved = true;
            $contract = Contract::where('id', $id)->first();
            if (!$contract)
                return $this->errorResponse(trans('messages.not_found'), 404);
            $contract->update([
                'name' => $request->get('name'),
                'code' => $request->get('code'),
                'start_time' => $request->get('start_time'),
                'end_time' => $request->get('end_time'),
                'lunch_start_time' => $request->get('lunch_start_time'),
                'lunch_end_time' => $request->get('lunch_end_time'),
                'first_rest_day' => $request->get('first_rest_day'),
                'second_rest_day' => $request->get('second_rest_day'),
                'daily_norm_hour' => $request->get('daily_norm_hour'),
                'note' => $request->get('note'),
                'position' => $request->get('position')
            ]);
            DB::commit();
        } catch (\Exception $e) {
            $saved = false;
            DB::rollBack();
        }
        return $saved
            ? $this->successResponse(trans('messages.saved'))
            : $this->errorResponse(trans('messages.not_saved'));
    }

    public function destroy($id)
    {
        return Contract::where('id', $id)->delete()
            ? $this->successResponse(trans('messages.saved'))
            : $this->errorResponse(trans('messages.not_saved'));
    }

    protected function validateRequest($input)
    {
        $validationArray = [
            'name' => 'required|max:256',
            'code' => 'max:50',
            'start_time' => 'date_format:H:i',
            'end_time' => 'date_format:H:i',
            'lunch_start_time' => 'date_format:H:i',
            'lunch_end_time' => 'date_format:H:i',
            'first_rest_day' => 'numeric|in:1,2,3,4,5,6,7',
            'second_rest_day' => 'numeric|in:1,2,3,4,5,6,7',
            'daily_norm_hour' => 'required|max:24|numeric',
            'note' => 'max:1000',
            'position' => 'required|numeric'
        ];
        $validator = \Validator::make($input, $validationArray);

        if ($validator->fails())
            return $validator->errors();
        return null;
    }
}
