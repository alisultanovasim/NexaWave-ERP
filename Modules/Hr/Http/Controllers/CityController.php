<?php


namespace Modules\Hr\Http\Controllers;


use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Hr\Entities\City;
use App\Traits\ApiResponse;
use App\Traits\Query;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
//todo adding mysql relation error excepstion handler
use Illuminate\Routing\Controller;

class CityController extends Controller
{
    use ApiResponse , Query ,ValidatesRequests;

    public function index(Request $request)
    {
        $this->validate($request , [
            'country_id' => ['sometimes' , 'required' , 'integer'],
            'paginateCount' => ['sometimes' , 'required' , 'integer'],
            'with_country' => ['nullable' , 'boolean']
        ]);


        $cities = City::orderBy('id' , 'desc');

        if ($request->get('with_country')) $cities->with(['country:id,name']);

        if ($request->has('country_id'))
            $cities->where('country_id' , $request->get('country_id'));

        $cities = $cities->paginate($request->get('paginateCount'));

        return $this->dataResponse($cities);
    }

    public function create(Request $request)
    {
        $error = $this->validateRequest($request->all());
        if ($error)
            return $this->errorResponse($error, 422);
        try
        {
            $saved = true;
            City::create([
                'name' => $request->get('name'),
                'country_id' => $request->get('country_id'),
                'is_active' => $request->get('is_active'),
                'position' => $request->get('position'),
                'phone_code' => $request->get('phone_code'),
            ]);
        }
        catch (\Exception $exception)
        {
            $saved = false;
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
            $saved = true;
            $city = City::where('id', $id)->exists();
            if (!$city)
                return $this->errorResponse(trans('messages.not_found'), 404);
            City::where('id', $id)->update([
                'name' => $request->get('name'),
                'country_id' => $request->get('country_id'),
                'is_active' => $request->get('is_active'),
                'position' => $request->get('position'),
                'phone_code' => $request->get('phone_code'),
            ]);
        }
        catch (\Exception $e)
        {
            $saved = false;
        }
        return $saved
            ? $this->successResponse(trans('messages.saved'))
            : $this->errorResponse(trans('messages.not_saved'));
    }

    public function destroy(Request $request, $id)
    {
        return City::where('id', $id)->delete()
            ? $this->successResponse(trans('messages.saved'))
            : $this->errorResponse(trans('messages.not_saved'));
    }

    protected function validateRequest($input){
        $validationArray = [
            'name' => 'required|max:256',
            'country_id' => 'required|numeric|exists:countries,id',
            'is_active' => 'boolean',
            'phone_code' => 'sometimes|required|max:5',
            'position' => 'nullable|numeric'
        ];
        $validator = \Validator::make($input, $validationArray);

        if($validator->fails())
            return $validator->errors();
        return null;
    }

}
