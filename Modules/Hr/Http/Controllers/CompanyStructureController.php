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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Modules\Hr\Entities\Department;
use Modules\Hr\Entities\Employee\Employee;
use Modules\Hr\Entities\Positions;
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
     * @param Company $company
     * @param Department $department
     * @param Section $section
     * @param Sector $sector
     */
    public function __construct(
        Company $company, Department $department,
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
            'structuredDepartments:id,name,is_closed,structable_id,structable_type',
            'structuredSections:id,name,is_closed,structable_id,structable_type',
            'structuredSectors:id,name,is_closed,structable_id,structable_type',
        ])
        ->first([
            'id',
            'name'
        ]);
        if ($request->get('with_nested_structure')){
            //when update company structure unset company-structure-comapnyId-* cache keys
            $cacheKey = 'company-structure-'. $request->get('company_id') . '-' . md5(serialize($structure));
            if (Cache::has($cacheKey)){
                $structure->children = Cache::get($cacheKey);
            }
            else {
                $children = $this->getNestedStructure($structure);
                Cache::put($cacheKey, $children, 24 * 60 * 60);
                $structure->children = $children;
            }
            unset($structure->structuredDepartments);
            unset($structure->structuredSections);
            unset($structure->structuredSectors);
        }
        return  $this->successResponse($structure);
    }


    private function getNestedStructure($structure = []){
        $formattedStructure = [];

        foreach ($structure['structuredDepartments'] ?? [] as $department){
            $formattedStructure[] = [
                'id' => $department['id'],
                'name' => $department['name'],
                'is_closed' => $department['is_closed'],
                'type' => 'department',
                'children' => $this->getNestedStructure($department)
            ];
        }

        foreach ($structure['structuredSections'] ?? [] as $section){
            $formattedStructure[] = [
                'id' => $section['id'],
                'name' => $section['name'],
                'is_closed' => $section['is_closed'],
                'type' => 'section',
                'children' => $this->getNestedStructure($section)
            ];
        }

        foreach ($structure['structuredSectors'] ?? [] as $sector){
            $formattedStructure[] = [
                'id' => $sector['id'],
                'name' => $sector['name'],
                'is_closed' => $sector['is_closed'],
                'type' => 'sector',
                'children' => $this->getNestedStructure($sector)
            ];
        }
        return $formattedStructure;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function getEmployees(Request $request): JsonResponse {
        $this->validate($request, [
            'structure_id' => 'nullable|numeric',
            'structure_type' => [
                'nullable',
                Rule::in([
                    'department', 'section', 'sector'
                ])
            ],
            'position_id' => 'nullable|numeric'
        ]);
        $employees = Employee::query()
        ->whereHas('contracts', function ($query) use ($request){
            if ($request->get('structure_id')){
                $query->where(['structure_id' => $request->get('structure_id')]);
            }
            if ($request->get('structure_type')){
                $query->where(['structure_type' => $request->get('structure_type')]);
            }
            if ($request->get('position_id')){
                $query->where(['position_id' => $request->get('position_id')]);
            }
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

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function companyCreateStructures(Request $request): JsonResponse {
        if (!$request->get('is_batch_create')){
            return $this->companyCreateStructure($request);
        }
        $structureModel = $this->getStructureModelByType($request->get('structure_type'));
        $this->validate($request, [
            'structure_type' => [
                'required',
                Rule::in(['department', 'section', 'sector'])
            ],
            'structures' => 'required|array',
            'structures.*.name' => 'required|min:2|max:255',
            'structures.*.code' => [
                'required',
                'min:3',
                'max:3',
                Rule::unique($structureModel->getTable(), 'code')->where('company_id', $request->get('company_id'))
            ]
        ]);
        $insertData = [];
        foreach ($request->get('structures') as $structure){
            $insertData[] = [
                'name' => $structure['name'],
                'code' => $structure['code'],
                'company_id' => $request->get('company_id'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ];
        }
        $structureModel->insert($insertData);
        return $this->successResponse(trans('messages.saved'), 200);
    }

    private function companyCreateStructure(Request $request): JsonResponse {
        $structureModel = $this->getStructureModelByType($request->get('structure_type'));
        $this->validate($request, [
            'structure_type' => [
                'required',
                Rule::in(['department', 'section', 'sector'])
            ],
            'name' => 'required|min:2|max:255',
            'code' => [
                'required',
                'min:3',
                'max:3',
                Rule::unique($structureModel->getTable(), 'code')->where('company_id', $request->get('company_id'))
            ]
        ]);
        $structureModel->fill([
            'name' => $request->get('name'),
            'code' => $request->get('code'),
            'company_id' => $request->get('company_id'),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
        $structureModel->save();
        return $this->successResponse(['id' => $structureModel->getKey()], 200);
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     * @throws ValidationException
     */
    public function companyUpdateStructure(Request $request, $id): JsonResponse {
        $structureModel = $this->getStructureModelByType($request->get('structure_type'));
        $this->validate($request, [
            'structure_type' => [
                'required',
                Rule::in(['department', 'section', 'sector'])
            ],
            'name' => 'required|min:2|max:255',
            'code' => [
                'required',
                'min:3',
                'max:3',
                Rule::unique($structureModel->getTable(), 'code')->where(function ($query) use ($request, $id){
                    $query->where('company_id', $request->get('company_id'));
                    $query->where('id', '!=', $id);
                })
            ]
        ]);
        $structureModel = $structureModel->where([
            'id' => $id,
            'company_id' => $request->get('company_id')
        ])->firstOrFail(['id']);
        $structureModel->update($request->only(['name', 'code', 'is_closed', 'closed_date']));
        return $this->successResponse(trans('messages.saved'), 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function setCompanyStructure(Request $request): JsonResponse {
        $this->validate($request, [
            'structure' => 'required|array',
            'structure.*.structure_id' => 'nullable|numeric',
            'structure.*.structure_type' => [
                'required',
                Rule::in(['department', 'section', 'sector'])
            ],
            'structure.*.link' => 'required|array',
            'structure.*.link.id' => 'nullable|numeric',
            'structure.*.link.type' => [
                'required',
                Rule::in(['company', 'department', 'section', 'sector'])
            ],
        ]);
        $links = [];
        $structuresToBeUnlinked = [
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
        $structureIdByTempId = [];
        foreach ($request->get('structure') as $structure){
            $link = $structure['link'];
            if (!$link['id'] and $link['type'] != 'company'){
                /*
                 * When structure already created
                 */
                if (isset($structureIdByTempId[$link['temp_id']])){
                    $link['id'] = $structureIdByTempId[$link['temp_id']];
                }

                /*
                 * Else create structure and link temp and real id
                 */
                else {
                    $link['id'] = $this->saveCompanyStructureAndGetId(
                        ['name' => $link['name'], 'code' => $link['code'] ?? null],
                        $request->get('company_id'),
                        $link['type']
                    );
                    $structureIdByTempId[$link['temp_id']] = $link['id'];
                }
            }
            if (!$structure['structure_id']){
                if (isset($structureIdByTempId[$structure['temp_id']])){
                    $structure['structure_id'] = $structureIdByTempId[$structure['temp_id']];
                }
                else {
                    $structure['structure_id'] = $this->saveCompanyStructureAndGetId(
                        ['name' => $structure['name'], 'code' => $structure['code'] ?? null],
                        $request->get('company_id'),
                        $structure['structure_type']
                    );
                    $structureIdByTempId[$structure['temp_id']] = $structure['structure_id'];
                }
            }
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
            $structuresToBeUnlinked[$structure['structure_type']]['ids'][] = $structure['structure_id'];
        }
        DB::transaction(function () use ($request, $links, $structuresToBeUnlinked){
            foreach ($links as $link){
                $link['model']->whereIn('id', $link['connectorsIds'])
                ->where('company_id', $request->get('company_id'))
                ->update([
                    'structable_id' => $link['structable_id'],
                    'structable_type' => $link['structable_type']
                ]);
            }
            foreach ($structuresToBeUnlinked as $link){
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

    private function saveCompanyStructureAndGetId($structure, $companyId, $structureType): int {
        $structureModel = $this->getStructureModelNewInstanceByType($structureType);
        $structureModel->fill([
            'name' => $structure['name'],
            'code' => $structure['code'] ?? null,
            'company_id' => $companyId
        ]);
        $structureModel->save();
        return $structureModel->getKey();
    }

    /**
     * @param $structureType
     * @param $linkStructureType
     */
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

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function getStructurePositions(Request $request): JsonResponse {
        $this->validate($request, [
            'structure_id' => 'nullable|numeric',
            'structure_type' => [
                'nullable',
                Rule::in(['company', 'department', 'section', 'sector'])
            ],
        ]);
        if (!$request->get('structure_id') and !$request->get('structure_type')){
            return $this->getPositionsWhichExistsInAnyStructure($request, $request->get('company_id'));
        }
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

    private function getPositionsWhichExistsInAnyStructure(Request $request, $companyId){
        $positions = Positions::where('company_id', $companyId)
            ->existsInStructure()
        ->get(['id', 'name']);
        return $this->successResponse($positions);
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
            Cache::forget('company-structure-'. $request->get('company_id') . '-' . '*');
           return $this->successResponse(trans('messages.saved'), 200);
        });
    }

    /**
     * @param string $type
     * @return Model
     */
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

    private function getStructureModelNewInstanceByType(string $type): Model {
        $structure = null;
        if ($type == 'department')
            $structure = new Department();
        if ($type == 'section')
            $structure = new Section();
        if ($type == 'sector')
            $structure = new Sector();
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
