<?php


namespace Modules\Hr\Http\Controllers;


use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Hr\Entities\EducationLevel;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controller;

class EducationLevelController extends Controller
{
    use ApiResponse , ValidatesRequests;

    public function index(Request $request)
    {
        $this->validate($request , [
            'paginateCount' => ['sometimes','required' , 'integer'],
        ]);
        $educationLevels = EducationLevel::paginate($request->get('per_page',200));
        return $this->dataResponse($educationLevels);
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
            EducationLevel::create([
                'name' => $request->get('name'),
                'code' => $request->get('code'),
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
            $educationLevel = EducationLevel::where('id', $id)->first(['id']);
            if (!$educationLevel)
                return $this->errorResponse(trans('messages.not_found'), 404);
            $educationLevel->update([
                'name' => $request->get('name'),
                'code' => $request->get('code'),
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
        return EducationLevel::where('id', $id)->delete()
            ? $this->successResponse(trans('messages.saved'))
            : $this->errorResponse(trans('messages.not_saved'));
    }

    protected function validateRequest($input){
        $validationArray = [
            'name' => 'required|max:256',
            'code' => 'required|max:50',
        ];
        $validator = \Validator::make($input, $validationArray);

        if($validator->fails())
            return $validator->errors();
        return null;
    }
}
