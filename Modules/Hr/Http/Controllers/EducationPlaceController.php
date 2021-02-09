<?php


namespace Modules\Hr\Http\Controllers;


use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Hr\Entities\EducationPlace;
use App\Traits\ApiResponse;
use App\Traits\Query;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controller;

class EducationPlaceController extends Controller
{
    use ApiResponse ,Query, ValidatesRequests;

    public function index(Request $request)
    {
        $this->validate($request , [
            'paginateCount' => ['sometimes','required' , 'integer'],
            'country_id' => ['sometimes', 'required' , 'integer'],
            'cit_id' => ['sometimes'  , 'required' , 'integer'],
            'region_id' => ['sometimes'  , 'required' , 'integer'],
        ]);
        $result = EducationPlace::with(['country:id,name,short_name' , 'city:id,name' , 'region:id,name']);

        if ($request->has('country_id')) $result->where('country_id' , $request->get('country_id'));
        if ($request->has('cit_id')) $result->where('cit_id' , $request->get('cit_id'));
        if ($request->has('region_id')) $result->where('region_id' , $request->get('region_id'));

        $result = $result->paginate($request->input('paginateCount',200));
        return $this->dataResponse($result);

    }

    public function create(Request $request)
    {
        $error = $this->validateRequest($request->all());
        if ($error)
            return $this->errorResponse($error, 422);

        EducationPlace::create([
            'name' => $request->get('name'),
            'code' => $request->get('code'),
            'note' => $request->get('note'),
            'country_id' => $request->get('country_id'),
            'city_id' => $request->get('city_id'),
            'region_id' => $request->get('region_id'),
        ]);

        return $this->successResponse(trans('messages.saved'));
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
            $educationPlace = EducationPlace::where('id', $id)->first(['id']);
            if (!$educationPlace)
                return $this->errorResponse(trans('messages.not_found'), 404);
            $educationPlace->update([
                'name' => $request->get('name'),
                'code' => $request->get('code'),
                'note' => $request->get('note'),
                'country_id' => $request->get('country_id'),
                'city_id' => $request->get('city_id'),
                'region_id' => $request->get('region_id')
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
        return EducationPlace::where('id', $id)->delete()
            ? $this->successResponse(trans('messages.saved'))
            : $this->errorResponse(trans('messages.not_saved'));
    }

    protected function validateRequest($input){
        $validationArray = [
            'name' => 'required|max:256',
            'code' => 'required|max:50',
            'country_id' => ['required' , 'integer'],
            'cit_id' => ['sometimes'  , 'required' , 'integer'],
            'region_id' => ['sometimes'  , 'required' , 'integer'],
        ];
        $validator = \Validator::make($input, $validationArray);

        if($validator->fails())
            return $validator->errors();
        return null;
    }
}
