<?php

namespace Modules\Hr\Http\Controllers;

use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Hr\Entities\OrganizationType;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Routing\Controller;

/**
 * Class OrganizationTypeController
 * @package Modules\Hr\Http\Controllers
 */
class OrganizationTypeController extends Controller
{
    use ApiResponse, ValidatesRequests;

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function index(Request $request)
    {
        $this->validate($request , [
            'paginateCount' =>  ['sometimes' , 'required' , 'integer'],
            'company_id' =>  ['required' , 'integer'],
        ]);

        $organizationTypes = OrganizationType::where('company_id' , $request->company_id)->paginate($request->get('company_id'));
        return $this->dataResponse($organizationTypes);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => "required|min:2|string",
            'suffix' => "required|string|min:1|max:30",
            'index' => "required|int|unique:organization_types,index",
            'company_id' => "required|int"
        ]);
        $organizationType = new OrganizationType();
        $organizationType->name = $request->input("name");
        $organizationType->suffix = $request->input("suffix");
        $organizationType->index = $request->input("index");
        $organizationType->company_id = $request->input("company_id");
        $organizationType->save();
        return $this->successResponse(trans('responses.added.success'),201);
    }


    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     * @throws \Exception
     */
    public function destroy(Request $request, $id)
    {
       $deleted = OrganizationType::where('id' , $id)->where("company_id", "=", $request->input("company_id"))
           ->delete();
       if (!$deleted) return  $this->errorMessage(trans('response.organizationTypeNotFound'));
        return $this->successResponse(trans("responses.deleted"));
    }


    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     * @throws ValidationException
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => "required|min:2|string",
            'suffix' => "required|string|min:1|max:30",
            'index' => "required|int",
            'company_id' => ['required' , 'integer'],
        ]);
        $organizationType = (new OrganizationType())
            ->where("company_id", "=", $request->input("company_id"))
            ->findOrFail($id , ['id']);
        if (!$organizationType) return $this->errorMessage(trans('response.organizationTypeNotFound'));

        $organizationType->fill($request->only([
            'name',
            'suffix',
            'index'
        ]));
        $organizationType->save();
        return $this->successResponse(trans("responses.updated"));
    }
}
