<?php

namespace Modules\Hr\Http\Controllers;

use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Hr\Entities\ProfessionLink;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Routing\Controller;

/**
 * Class ProfessionLinkController
 * @package Modules\Hr\Http\Controllers
 */
class ProfessionLinkController extends Controller
{
    use ApiResponse, ValidatesRequests;


    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $perPage = (int)$request->input("per_page", config("defaults.paginateCount"));
        $professionLinks = ProfessionLink::with([
            'organizationLink.department:id,organization_id,name',
            'organizationLink.sector:id,section_id,name',
            'organizationLink.section:id,department_id,name',
            'organizationLink.organization:id,name',
            'profession:id,name',
            'educationLevel:id,name'
        ])->simplePaginate($perPage);
        return $this->dataResponse($professionLinks, 200);

    }


    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'organization_link_id' => "required|int|min:1|exists:organization_links,id",
            "profession_id" => "required|int|min:1|exists:professions,id",
            "education_level_id" => "required|int|min:1|exists:education_levels,id",
            'profession_salary' => 'required|regex:/^\d+(\.\d{1,2})?$/',
            "vacancy_count" => "required|int|min:1",
            "generation" => 'nullable|int|min:1',
            "index" => "int",
            "company_id" => "int"
        ]);


        $professionLink = new ProfessionLink();
        $professionLink->organization_link_id = $request->input("organization_link_id");
        $professionLink->profession_id = $request->input("profession_id");
        $professionLink->education_level_id = $request->input("education_level_id");
        $professionLink->profession_salary = $request->input("profession_salary");
        $professionLink->vacancy_count = $request->input("vacancy_count");
        $professionLink->generation = $request->input("generation");
        $professionLink->index = $request->input("index");
        $professionLink->company_id = $request->input("company_id");
        $professionLink->save();

        return $this->successResponse(trans("responses.added.success"), 201);

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
            'organization_link_id' => "required|int|min:1|exists:organization_links,id",
            "profession_id" => "required|int|min:1|exists:professions,id",
            "education_level_id" => "required|int|min:1|exists:education_levels,id",
            'profession_salary' => 'required|regex:/^\d+(\.\d{1,2})?$/',
            "vacancy_count" => "required|int|min:1",
            "generation" => 'nullable|int|min:1',
            "index" => "int",
            "company_id" => "int"
        ]);

        $professionLink = (new ProfessionLink)->findOrFail($id);
        $professionLink->fill($request->only([
            'organization_link_id',
            "profession_id",
            "education_level_id",
            'profession_salary',
            "vacancy_count",
            "generation",
            "index",
            "company_id"
        ]));
        $professionLink->save();

        return $this->successResponse(trans('responses.updated_success'));
    }

    /**
     * @param $id
     * @return JsonResponse
     * @throws \Exception
     */
    public function destroy($id)
    {
        $professionLink = (new ProfessionLink())->findOrFail($id);
        $professionLink->delete();

        return $this->successResponse(trans('responses.delete_success'));

    }
}
