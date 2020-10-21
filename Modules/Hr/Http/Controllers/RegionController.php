<?php


namespace Modules\Hr\Http\Controllers;


use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Hr\Entities\Region;
use App\Traits\ApiResponse;
use App\Traits\Query;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controller;

class RegionController extends Controller
{
    use ApiResponse , Query, ValidatesRequests;

    public function index(Request $request)
    {
        $this->validate($request, [
            'paginateCount' => ['sometimes', 'required', 'integer'],
            'city_id' => ['sometimes' , 'required' , 'integer'],
            'with_city' => ['nullable' , 'boolean']

        ]);

        $regions = Region::orderBy('id' , 'desc');
        if ($request->get('with_city'))$regions->with('city:id,name');
        if ($request->has('city_id'))
            $regions->where('city_id' , $request->get('city_id'));
        $regions = $regions->paginate($request->get('paginateCount'));

        return $this->dataResponse($regions);
    }

    public function create(Request $request)
    {
        $error = $this->validateRequest($request->all());
        if ($error)
            return $this->errorResponse($error, 422);

        try {
            DB::beginTransaction();
            $saved = true;
            Region::create([
                'name' => $request->get('name'),
                'city_id' => $request->get('city_id'),
                'is_active' => $request->get('is_active'),
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

    protected function validateRequest($input)
    {
        $validationArray = [
            'name' => 'required|max:256',
            'city_id' => 'required|numeric|exists:cities,id',
            'is_active' => 'boolean',
            'company_id' => ['required' , 'integer']
        ];
        $validator = \Validator::make($input, $validationArray);

        if ($validator->fails())
            return $validator->errors();
        return null;
    }

    public function update(Request $request, $id)
    {
        $error = $this->validateRequest($request->all());
        if ($error)
            return $this->errorResponse($error, 422);

        if ($ex = $this->companyInfo($request->get('company_id') , $request->only('city_id')))
            return $this->errorResponse($ex);
        try {
            DB::beginTransaction();
            $saved = true;
            $region = Region::where('id', $id)->where('company_id' , $request->get('company_id'))->exists();
            if (!$region)
                return $this->errorResponse(trans('messages.not_found'), 404);
            Region::where('id', $id)->update([
                'name' => $request->get('name'),
                'city_id' => $request->get('city_id'),
                'is_active' => $request->get('is_active'),
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

    public function destroy(Request $request , $id)
    {
        $this->validate($request , [
            'company_id' => ['required' , 'integer']
        ]);
        return Region::where('id', $id)->where('company_id' , $request->get('company_id'))->delete()
            ? $this->successResponse(trans('messages.saved'))
            : $this->errorResponse(trans('messages.not_saved'));
    }
}
