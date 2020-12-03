<?php

namespace Modules\Hr\Http\Controllers;

use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Hr\Entities\Organization;
use App\Traits\ApiResponse;
use App\Traits\Query;
use Doctrine\DBAL\Exception\TableNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Finder\Comparator\DateComparator;
use Illuminate\Routing\Controller;

/**
 * Class OrganizationController
 * @package Modules\Hr\Http\Controllers
 */
class OrganizationController extends Controller
{
    use ApiResponse, Query, ValidatesRequests;


    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $organizations = Organization::with([
            'assigned',
            'bankInformation',
            'city',
            'country',
            'organizationType',
            'region'
        ])
        ->where("company_id", "=", $request->input("company_id"))
        ->get();
        return $this->dataResponse($organizations);
    }


    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     * @throws TableNotFoundException
     */
    public function store(Request $request)
    {
        $this->validate($request, $this->getValidationRules());

        if ($notExists = $this->companyInfo($request->get('company_id'), $request->only([
            'organization_type_id',
            'city_id',
            'bank_information_id',
            'region_id',
            'organization_type_id',
            'assigned_to'
        ]))) return $this->errorMessage($notExists);

        $organization = new Organization();
        $organization->fill($request->only([
            'code',
            "name",
            "short_name",
            "organization_type_id",
            "is_head",
            "assigned_to",
            "phone",
            "fax",
            "address",
            "email",
            'website',
            "post_code",
            "country_id",
            "city_id",
            "region_id",
            "professions",
            "is_closed",
            "closed_date",
            "note",
            "bank_information_id",
            "index",
            'company_id'
        ]));

        $organization->save();

        return $this->successResponse(trans('responses.added.success'), 201);
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     * @throws ValidationException
     * @throws TableNotFoundException
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, $this->getValidationRules());

        if ($notExists = $this->companyInfo($request->get('company_id'), $request->only([
            'organization_type_id',
            'city_id',
            'bank_information_id',
            'region_id',
            'organization_type_id',
            'assigned_to'
        ]))) return $this->errorMessage($notExists);

        $organization = (new Organization())
            ->where("company_id", "=", $request->input("company_id"))
            ->findOrFail($id);
        $organization->fill($request->only([
            'code',
            "name",
            "short_name",
            "organization_type_id",
            "is_head",
            "assigned_to",
            "phone",
            "fax",
            "address",
            "email",
            'website',
            "post_code",
            "country_id",
            "city_id",
            "region_id",
            "professions",
            "is_closed",
            "closed_date",
            "note",
            "bank_information_id",
            "index"
        ]));

        $organization->save();
        return $this->successResponse(trans('responses.updated_success'));

    }


    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     * @throws \Exception
     */
    public function destroy(Request $request, $id)
    {
        $organization = (new Organization())->where("company_id", "=", $request->input("company_id"))
            ->findOrFail($id);
        $organization->delete();
        return $this->successResponse(trans("responses.delete_success"));
    }

    /**
     * @return array
     */
    public function getValidationRules()
    {
        return [
            'code' => "required|string|min:1|max:10",
            "name" => "required|string|min:1|max:255",
            "short_name" => "nullable|string|min:1|max:10",
            "organization_type_id" => "nullable|int",
            "is_head" => "nullable|boolean",
            "assigned_to" => "nullable|int",
            "phone" => "nullable|string|min:7|max:20",
            "fax" => "nullable|string|min:5|max:30",
            "address" => "nullable|string|min:1|max:255",
            "email" => 'nullable|email|min:5|max:50',
            'website' => 'nullable|url|min:2|max:50',
            "post_code" => "nullable|string|min:4|max:10",
            "country_id" => "required|int",
            "city_id" => "nullable|int",
            "region_id" => "nullable|int",
            "professions" => "nullable|string|min:1|max:255",
            "is_closed" => "nullable|boolean",
            "closed_date" => "nullable|date|date_format:Y-m-d",
            "note" => "nullable|string|min:1|max:255",
            "bank_information_id" => "nullable|int",
            "index" => "required|int",
            'company_id' => ['required' , 'integer'],
        ];
    }
}
