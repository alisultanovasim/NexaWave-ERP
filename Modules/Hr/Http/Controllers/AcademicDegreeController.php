<?php


namespace Modules\Hr\Http\Controllers;


use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Hr\Entities\AcademicDegree;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controller;

class AcademicDegreeController extends Controller
{
    use ApiResponse ,ValidatesRequests;

    public function index(Request $request)
    {
        $paginateCount = $request->filled('paginateCount')
            ? $request->get('paginateCount')
            : config('defaults.paginateCount');
        $result = AcademicDegree::paginate($paginateCount);

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
            AcademicDegree::create([
                'name' => $request->get('name'),
                'code' => $request->get('code'),
                'position' => $request->get('position'),
                'company_id' => $request->get('company_id'),
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
            $academicDegree = AcademicDegree::where('id', $id)->first();
            if (!$academicDegree)
                return $this->errorResponse(trans('messages.not_found'), 404);
            $academicDegree->update([
                'name' => $request->get('name'),
                'code' => $request->get('code'),
                'position' => $request->get('position'),
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

    public function destroy($id)
    {
        return AcademicDegree::where('id', $id)->delete()
            ? $this->successResponse(trans('messages.saved'))
            : $this->errorResponse(trans('messages.not_saved'));
    }

    protected function validateRequest($input){
        $validationArray = [
            'name' => 'required|max:256',
            'code' => 'required|max:50',
            'position' => 'required|numeric'
        ];
        $validator = \Validator::make($input, $validationArray);

        if($validator->fails())
            return $validator->errors();
        return null;
    }

}
