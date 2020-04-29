<?php

namespace Modules\Hr\Http\Controllers;

use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Hr\Entities\Country;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Class CountryController
 * @package Modules\Hr\Http\Controllers
 */
use Illuminate\Routing\Controller;

class CountryController extends Controller
{
    use ApiResponse,ValidatesRequests;


    public function index(Request $request)
    {
        $this->validate($request, [
            'paginateCount' => ['sometimes', 'required', 'integer'],
//            'company_id' => ['required', 'integer'],
            'name' => ['sometimes' ,'required', 'string', 'max:255']
        ]);
        $countries = Country::orderBy('id' , 'desc');
        if ($request->has('name'))
            $countries->where('name', $request->get('name'));

        $countries = $countries->paginate($request->get('paginateCount'));
        return $this->dataResponse($countries);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function store(Request $request)
    {
        $this->validate($request, $this->getValidationData());
        $country = new Country();
        $country->fill($request->only([
            'name',
            "short_name",
            "code",
            "iso",
            "iso3",
            'phone_code',
            "currency",
            "index",
            "is_active",
        ]));
        $country->save();
        return $this->successResponse(trans("responses.added.success"), 201);
    }

    /**
     * @return array
     */
    public function getValidationData()
    {
        return [
            'name' => "required|string|min:2|max:255",
            "short_name" => "required|string|min:1|max:20",
            "code" => "nullable|string|min:1|max:10",
            "iso" => "nullable|string|size:2",
            "iso3" => "nullable|string|size:3",
            'phone_code' => "nullable|string|min:1|max:5",
            "currency" => "nullable|string|min:1|max:4",
            "index" => "required|int|min:1",
            "is_active" => "nullable|boolean",
        ];
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     * @throws ValidationException
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, $this->getValidationData());
        $country = Country::where('id', $id)->exists();
        if (!$country) return $this->errorResponse(trans('response.countyNotFound'));
        Country::where('id',$id)->update($request->only([
            'name',
            "short_name",
            "code",
            "iso",
            "iso3",
            'phone_code',
            "currency",
            "index",
            "is_active",
        ]));
        return $this->successResponse(trans("responses.updated_success"));
    }

    /**
     * @param $id
     * @return JsonResponse
     * @throws \Exception
     */
    public function destroy(Request $request, $id)
    {
        $country = Country::where('id', $id)
            ->delete();

        if (!$country) return $this->errorResponse(trans('response.countyNotFound'));

        return $this->successResponse(trans('responses.delete_success'));
    }
}
