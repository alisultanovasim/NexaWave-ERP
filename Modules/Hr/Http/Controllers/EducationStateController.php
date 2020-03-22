<?php


namespace Modules\Hr\Http\Controllers;

use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Hr\Entities\EducationState;
use App\Traits\ApiResponse;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controller;

class EducationStateController extends Controller
{
    use ApiResponse , ValidatesRequests;

    public function index(Request $request)
    {
        $this->validate($request , [
            'company_id' => ['required' , 'integer'],
            'paginateCount' => ['sometimes','required' , 'integer'],
        ]);


        $result = EducationState::paginate($request->get('paginateCount'));
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
            EducationState::create([
                'name' => $request->get('name'),
                'code' => $request->get('code'),
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
            $educationState = EducationState::where('id', $id)
                ->where('company_id' , $request->get('company_id'))->first(['id']);
            if (!$educationState)
                return $this->errorResponse(trans('messages.not_found'), 404);
            $educationState->update([
                'name' => $request->get('name'),
                'code' => $request->get('code'),
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

    public function destroy(Request $request , $id)
    {
        return EducationState::where('id', $id)->where('company_id', $request->get('company_id'))->delete()
            ? $this->successResponse(trans('messages.saved'))
            : $this->errorResponse(trans('messages.not_saved'));
    }


    protected function validateRequest($input){
        $validationArray = [
            'name' => 'required|min:1|max:255',
            'code' => 'required|min:1|max:15',
            'position' => 'nullable'
        ];
        $validator = \Validator::make($input, $validationArray);

        if($validator->fails())
            return $validator->errors();
        return null;
    }
}
