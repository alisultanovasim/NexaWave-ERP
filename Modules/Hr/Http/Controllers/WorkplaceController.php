<?php


namespace Modules\Hr\Http\Controllers;


use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Hr\Entities\Workplace;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controller;

class WorkplaceController extends Controller
{
    use ApiResponse, ValidatesRequests;

    public function index(Request $request)
    {
        $paginateCount = $request->filled('paginateCount')
            ? $request->get('paginateCount')
            : config('defaults.paginateCount');
        $result = Workplace::with([
            'country' => function ($query){
                $query->select(['id', 'name']);
            },
            'city' => function ($query){
                $query->select(['id', 'name']);
            },
            'region' => function ($query){
                $query->select(['id', 'name']);
            },
        ])->paginate($paginateCount);

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
            Workplace::create([
                'name' => $request->get('name'),
                'short_name' => $request->get('short_name'),
                'country_id' => $request->get('country_id'),
                'city_id' => $request->get('city_id'),
                'region_id' => $request->get('region_id'),
                'address' => $request->get('address'),
                'zip_code' => $request->get('zip_code'),
                'phone' => $request->get('phone'),
                'fax' => $request->get('fax'),
                'email' => $request->get('email'),
                'web_site' => $request->get('web_site'),
                'position' => $request->get('position')
            ]);
            DB::commit();
        }
        catch (\Exception $exception)
        {
            $saved = false;
            DB::rollBack();dd($exception->getMessage());
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
            $workplace = Workplace::where('id', $id)->first();
            if (!$workplace)
                return $this->errorResponse(trans('messages.not_found'), 404);
            $workplace->update([
                'name' => $request->get('name'),
                'short_name' => $request->get('short_name'),
                'country_id' => $request->get('country_id'),
                'city_id' => $request->get('city_id'),
                'region_id' => $request->get('region_id'),
                'address' => $request->get('address'),
                'zip_code' => $request->get('zip_code'),
                'phone' => $request->get('phone'),
                'fax' => $request->get('fax'),
                'email' => $request->get('email'),
                'web_site' => $request->get('web_site'),
                'position' => $request->get('position')
            ]);
            DB::commit();
        }
        catch (\Exception $e)
        {
            $saved = false;
            DB::rollBack();dd($e->getMessage());
        }
        return $saved
            ? $this->successResponse(trans('messages.saved'))
            : $this->errorResponse(trans('messages.not_saved'));
    }

    public function destroy($id)
    {
        return Workplace::where('id', $id)->delete()
            ? $this->successResponse(trans('messages.saved'))
            : $this->errorResponse(trans('messages.not_saved'));
    }

    protected function validateRequest($input){
        $validationArray = [
            'name' => 'required|max:250',
            'short_name' => 'required|max:50',
            'country_id' => 'required|numeric|exists:countries,id',
            'city_id' => 'required|numeric|exists:cities,id',
            'region_id' => 'numeric|exists:regions,id',
            'address' => 'max:500',
            'zip_code' => 'max:10',
            'phone' => 'max:50',
            'fax' => 'max:50',
            'email' => 'email',
            'web_site' => 'max:100',
            'position' => 'required|numeric'
        ];
        $validator = \Validator::make($input, $validationArray);

        if($validator->fails())
            return $validator->errors();
        return null;
    }
}
