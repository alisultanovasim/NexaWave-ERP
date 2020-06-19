<?php

namespace Modules\Hr\Http\Controllers;

use App\Models\Company;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Modules\Hr\Entities\Department;
use Modules\Hr\Entities\Employee\Employee;
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

    public function getEmployees(Request $request): JsonResponse {
        $this->validate($request, [
            'structure_id' => 'nullable|numeric',
            'structure_type' => [
                'nullable',
                Rule::in([
                    'department', 'section', 'sector'
                ])
            ],
            'position_id' => 'required|numeric'
        ]);
        $employees = Employee::query()
        ->whereHas('contracts', function ($query) use ($request){
            $query->where([
                'structure_id' => $request->get('structure_id'),
                'structure_type' => $request->get('structure_type')
            ]);
            $query->where(function ($query){
                $query->where('end_date', '>', Carbon::now());
                $query->orWhere('end_date', null);
            });
        })
        ->with('user:id,name,surname')
        ->where('company_id', $request->get('company_id'))
        ->get(['id', 'user_id']);
        return $this->successResponse($employees);
    }

    public function createCompanyStructure(Request $request): JsonResponse {
        $this->validate($request, [
            'structure' => 'required|array',
            'structure.*.structure_id' => 'required|numeric',
            'structure.*.structure_type' => [
                'required',
                Rule::in(['department', 'section', 'sector'])
            ],
            'structure.*.link' => 'required|array',
            'structure.*.link.id' => 'required|numeric',
            'structure.*.link.type' => [
                'required',
                Rule::in(['company', 'department', 'section', 'sector'])
            ],
        ]);
        $links = [];
        $linkedStructures = [
            'department' => [
                'model' => $this->getStructureModelByType('department'),
                'ids' => []
            ],
            'section' => [
                'model' => $this->getStructureModelByType('section'),
                'ids' => []
            ],
            'sector' => [
                'model' => $this->getStructureModelByType('sector'),
                'ids' => []
            ]
        ];
        foreach ($request->get('structure') as $structure){
            $link = $structure['link'];
            $this->ifStructureTriesLinkSmallerStructureThrowException($structure['structure_type'], $link['type']);
            $structableId = $link['type'] == 'company' ? $request->get('company_id') : $link['id'];
            $key = $structableId . '-' . $structure['structure_type'] . '-' . $link['type'];
            /*
             * Group structure links to  mysql queries
             */
            if (!isset($links[$key])){
                $links[$key] = [
                    'model' => $this->getStructureModelByType($structure['structure_type']),
                    'structure_type' => $structure['structure_type'],
                    'structable_id' => $structableId,
                    'structable_type' => $link['type'],
                    'connectorsIds' => []
                ];
            }
            $links[$key]['connectorsIds'][] = $structure['structure_id'];

            /*
             * Group linked structure ids by structure type (later to remove from company structure which is not in array)
             */
            $linkedStructures[$structure['structure_type']]['ids'][] = $structure['structure_id'];
        }
        DB::transaction(function () use ($request, $links, $linkedStructures){
            foreach ($links as $link){
                $link['model']->whereIn('id', $link['connectorsIds'])
                ->where('company_id', $request->get('company_id'))
                ->update([
                    'structable_id' => $link['structable_id'],
                    'structable_type' => $link['structable_type']
                ]);
            }
            foreach ($linkedStructures as $link){
                $link['model']->whereNotIn('id', $link['ids'])
                ->where('company_id', $request->get('company_id'))
                ->update([
                    'structable_id' => null,
                    'structable_type' => null
                ]);
            }
        });
        return $this->successResponse(trans('messages.saved'), 200);
    }

    private function ifStructureTriesLinkSmallerStructureThrowException($structureType, $linkStructureType){
        $throw = false;
        if ($structureType == 'department' and $linkStructureType != 'company')
            $throw = true;
        if ($structureType == 'section' and  !in_array($linkStructureType, ['company', 'department']))
            $throw = true;
        if ($structureType == 'sector' and  !in_array($linkStructureType, ['company', 'department', 'section']))
            $throw = true;
        if ($throw)
            throw new BadRequestHttpException(trans('messages.remove_sub_structures_before_update_parent_structure'));
    }


    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function addStructureLink(Request $request): JsonResponse {
        $this->validate($request, $this->getStructureRules());
        $requestedLink = $request->get('link');
        $structure = $this->getStructureModelByType($request->get('structure_type'));
        $link = null;
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

    public function getStructurePositions(Request $request): JsonResponse {
        $this->validate($request, [
            'structure_id' => 'nullable|numeric',
            'structure_type' => [
                Rule::in(['company', 'department', 'section', 'sector'])
            ],
        ]);
        if ($request->get('structure_type')){
            $structure = $this->getStructureModelByType($request->get('structure_type'));
            $structure = $structure->where('id', $request->get('structure_id'));
        }
        else{
            $structure = $this->company->where('id', $request->get('company_id'));
        }
        $structure = $structure
        ->with([
            'positions:positions.id,positions.name'
        ])
        ->first(['id']);
        $response = [];
        foreach ($structure->positions as $position){
            $response[] = [
                'id'  => $position['id'],
                'name'  => $position['name'],
                'quantity'  => $position['pivot']['quantity'],
            ];
        }
        return $this->successResponse($response);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function setStructurePositions(Request $request): JsonResponse {
        $this->validate($request, [
            'structure_id' => 'required_unless:structure_type,company',
            'structure_type' => [
                'required',
                Rule::in(['company', 'department', 'section', 'sector'])
            ],
            'positions' => 'required|array|min:1',
            'positions.*.id' => [
                'required',
//                Rule::exists('positions', 'id')->where('company_id', $request->get('company_id'))
            ],
            'positions.*.quantity' => 'required|numeric',
        ]);
        if ($request->get('structure_type') != 'company'){
            $structure = $this->getStructureModelByType($request->get('structure_type'));
            /*
             * Check if structure exists
             */
            $structure->where([
                'company_id' => $request->get('company_id'),
                'id' => $request->get('structure_id')
            ])->firstOrFail(['id']);
        }
        $positions = [];
        $structureId = $request->get('structure_type') == 'company'
            ? $request->get('company_id')
            : $request->get('structure_id');
        foreach ($request->get('positions') as $position){
            $positions[] = [
                'structure_id' => $structureId,
                'structure_type' => $request->get('structure_type'),
                'position_id' => $position['id'],
                'quantity' => $position['quantity'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ];
        }

        return DB::transaction(function () use ($request, $positions, $structureId){
            DB::table('structure_positions')->where([
                'structure_id' => $structureId,
                'structure_type' => $request->get('structure_type'),
            ])->delete();
            DB::table('structure_positions')->insert($positions);
           return $this->successResponse(trans('messages.saved'), 200);
        });
    }

    private function getStructureModelByType(string $type): Model {
        $structure = null;
        if ($type == 'department')
            $structure = $this->department;
        if ($type == 'section')
            $structure = $this->section;
        if ($type == 'sector')
            $structure = $this->sector;
        if ($type == 'company')
            $structure = $this->company;
        return $structure;
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