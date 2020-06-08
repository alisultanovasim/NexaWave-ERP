<?php

namespace Modules\Hr\Http\Controllers;

use App\Models\Company;
use App\Traits\ApiResponse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Modules\Hr\Entities\Department;
use Modules\Hr\Entities\Section;
use Modules\Hr\Entities\Sector;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class CompanyStructureController extends Controller
{
    use ApiResponse, ValidatesRequests;

    private $company;
    private $department;
    private $section;
    private $sector;

    /**
     * CompanyStructureController constructor.
     * @param Request $request
     * @param Company $company
     * @param Department $department
     * @param Section $section
     * @param Sector $sector
     */
    public function __construct(
        Request $request, Company $company, Department $department,
        Section $section, Sector $sector
    ){
        $this->company = $company;
        $this->department = $department;
        $this->section = $section;
        $this->sector = $sector;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse {
        $structure = $this->company->where('id', $request->get('company_id'))
        ->with([
            'structuredDepartments:id,name,structable_id,structable_type',
            'structuredSections:id,name,structable_id,structable_type',
            'structuredSectors:id,name,structable_id,structable_type',
        ])
        ->first([
            'id',
            'name'
        ]);
        return  $this->successResponse($structure);
    }


    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function addStructureLink(Request $request): JsonResponse {
        $this->validate($request, $this->getStructureRules());
        $requestedLink = $request->get('link');
        $structure = null;
        $link = null;
        if ($request->get('structure_type') == 'department')
            $structure = $this->department;
        if ($request->get('structure_type') == 'section')
            $structure = $this->section;
        if ($request->get('structure_type') == 'sector')
            $structure = $this->sector;
        if ($requestedLink['type'] == 'company')
            $linkToCompanyId = $request->get('company_id');
        if ($requestedLink['type'] == 'department')
            $link = $this->department;
        if ($requestedLink['type'] == 'section')
            $link = $this->section;
        if ($requestedLink['type'] == 'sector')
            $link = $this->sector;

        /*
         * Check if structure exists
         */
        $structure = $structure->where('id', $request->get('structure_id'))->firstOrFail(['id']);

        /*
         * If links not direct to company check if link structure is exists
         */
        if (!isset($linkToCompanyId))
            $link = $link->where('id', $requestedLink['id'])->firstOrFail(['id']);

        /*
         * If Structure has sub structures cant change link before relink sub structures
         */
        $this->ifStructureHasSubStructuresThrowException($structure->getKey(), $request->get('structure_type'));

        $this->saveStructure(
            $structure,
            $linkToCompanyId ?? $link->getKey(),
            $requestedLink['type']
        );
        return $this->successResponse(trans('messages.saved'), 200);
    }

    /**
     * @param $structureId
     * @param $structureType
     */
    private function ifStructureHasSubStructuresThrowException($structureId, $structureType){
        $departments = $this->department->where([
            'structable_id' => $structureId,
            'structable_type' => $structureType
        ])->select(['id']);
        $sections = $this->section->where([
            'structable_id' => $structureId,
            'structable_type' => $structureType
        ])->select(['id']);
        $hasSubStructures = $this->sector->where([
            'structable_id' => $structureId,
            'structable_type' => $structureType
        ])->select(['id'])
        ->union($departments)
        ->union($sections)
        ->count();
        if ($hasSubStructures)
            throw new BadRequestHttpException(trans('messages.remove_sub_structures_before_update_parent_structure'));
    }


    /**
     * @param Model $model
     * @param $linkId
     * @param $linkTYpe
     */
    private function saveStructure(Model $model, $linkId, $linkTYpe): void {
        $model->fill([
            'structable_id' => $linkId,
            'structable_type' => $linkTYpe
        ])->save();
    }

    /**
     * @return array
     */
    private function getStructureRules(): array {
        $rules = [
            'structure_id' => 'required|numeric',
            'structure_type' => [
                'required',
                Rule::in(['department', 'section', 'sector'])
            ],
            'link' => 'required|array',
            'link.id' => 'required_unless:link.type,company|numeric'
        ];
        if (\request()->get('structure_type') == 'department'){
            $rules['link.type'] = [
                'required',
                Rule::in(['company'])
            ];
        }
        if (\request()->get('structure_type') == 'section'){
            $rules['link.type'] = [
                'required',
                Rule::in(['company', 'department'])
            ];
        }
        if (\request()->get('structure_type') == 'sector'){
            $rules['link.type'] = [
                'required',
                Rule::in(['company', 'department', 'section'])
            ];
        }
        return $rules;
    }

}
