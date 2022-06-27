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
use Modules\Hr\Services\CompanyStructureService;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class CompanyStructureController extends Controller
{
    use ApiResponse, ValidatesRequests;

    private $company;
    private $department;
    private $section;
    private $sector;
    private $companyStructureService;


    public function __construct(
        Company $company, Department $department,
        Section $section, Sector $sector,
        CompanyStructureService $companyStructureService
    )
    {
        $this->company = $company;
        $this->department = $department;
        $this->section = $section;
        $this->sector = $sector;
        $this->companyStructureService = $companyStructureService;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $structure = $this->companyStructureService->getStructure($request->get('company_id'), $request->get('with_nested_structure'));

        return $this->successResponse($structure);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function setCuratorToStructure(Request $request): JsonResponse
    {
        $this->validate($request, [
            'structure_id' => 'required|integer',
            'structure_type' => [
                'required',
                Rule::in(['department', 'section', 'sector'])
            ],
            'curator_id' => [
                'required',
                Rule::exists('employees', 'id')->where(function ($query) use ($request) {
                    $query->where('company_id', $request->get('company_id'));
                })
            ]
        ]);

        $structure = $this->getStructureModelByType($request->get('structure_type'));
        $structure = $structure->where([
            'company_id' => $request->get('company_id'),
            'id' => $request->get('structure_id')
        ])->firstOrFail(['id']);
        $structure->update([
            'curator_id' => $request->get('curator_id')
        ]);

        return $this->successResponse(trans('messages.saved'));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function getEmployees(Request $request): JsonResponse
    {

        $this->validate($request, [
            'position_id' => 'nullable|numeric'
        ]);


        $employees = Employee::with('user:id,name,surname')
            ->where(function ($q) use ($request) {
                    $q->orWhereHas('contracts', function ($query) use ($request) {
                        if ($request->get('department_id')) {
                            $query->where('department_id', $request->get('department_id'));
                        }
                        if ($request->get('sector_id')) {
                            $query->where('sector_id', $request->get('sector_id'));
                        }
                        if ($request->get('section_id')) {
                            $query->where('section_id', $request->get('section_id'));
                        }
                        if ($request->get('position_id')) {
                            $query->where(['position_id' => $request->get('position_id')]);
                        }
                        $query->where(function ($query) {
                            $query->where('end_date', '>', Carbon::now());
                            $query->orWhere('end_date', null);
                        });
                    });
            })
            ->where('company_id', $request->input('company_id'))
            ->get(['id', 'user_id']);

        return $this->successResponse($employees);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function companyCreateStructures(Request $request): JsonResponse
    {

        if (!$request->get('is_batch_create')) {
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

        foreach ($request->get('structures') as $structure) {
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

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    private function companyCreateStructure(Request $request): JsonResponse
    {
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
    public function companyUpdateStructure(Request $request, $id): JsonResponse
    {
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
                Rule::unique($structureModel->getTable(), 'code')->where(function ($query) use ($request, $id) {
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
    public function setCompanyStructure(Request $request): JsonResponse
    {
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
        foreach ($request->get('structure') as $structure) {
            $link = $structure['link'];
            if (!$link['id'] and $link['type'] != 'company') {
                /*
                 * When structure already created
                 */
                if (isset($structureIdByTempId[$link['temp_id']])) {
                    $link['id'] = $structureIdByTempId[$link['temp_id']];
                } /*
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
            if (!$structure['structure_id']) {
                if (isset($structureIdByTempId[$structure['temp_id']])) {
                    $structure['structure_id'] = $structureIdByTempId[$structure['temp_id']];
                } else {
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
            if (!isset($links[$key])) {
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
        DB::transaction(function () use ($request, $links, $structuresToBeUnlinked) {
            foreach ($links as $link) {
                $link['model']->whereIn('id', $link['connectorsIds'])
                    ->where('company_id', $request->get('company_id'))
                    ->update([
                        'structable_id' => $link['structable_id'],
                        'structable_type' => $link['structable_type']
                    ]);
            }
            foreach ($structuresToBeUnlinked as $link) {
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

    /**
     * @param $structure
     * @param $companyId
     * @param $structureType
     * @return int
     */
    private function saveCompanyStructureAndGetId($structure, $companyId, $structureType): int
    {
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
    private function ifStructureTriesLinkSmallerStructureThrowException($structureType, $linkStructureType)
    {
        $throw = false;
        if ($structureType == 'department' and $linkStructureType != 'company')
            $throw = true;
        if ($structureType == 'section' and !in_array($linkStructureType, ['company', 'department']))
            $throw = true;
        if ($structureType == 'sector' and !in_array($linkStructureType, ['company', 'department', 'section']))
            $throw = true;
        if ($throw)
            throw new BadRequestHttpException(trans('messages.remove_sub_structures_before_update_parent_structure'));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function addStructureLink(Request $request): JsonResponse
    {
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
    public function getStructurePositions(Request $request): JsonResponse
    {
        $this->validate($request, [
            'structure_id' => 'nullable|numeric',
            'structure_type' => [
                'nullable',
                Rule::in(['company', 'department', 'section', 'sector'])
            ],
        ]);
        if (!$request->get('structure_id') and !$request->get('structure_type')) {
            return $this->getPositionsWhichExistsInAnyStructure($request, $request->get('company_id'));
        }
        if ($request->get('structure_type')) {
            $structure = $this->getStructureModelByType($request->get('structure_type'));
            $structure = $structure->where('id', $request->get('structure_id'));
        } else {
            $structure = $this->company->where('id', $request->get('company_id'));
        }
        $structure = $structure
            ->with([
                'positions:positions.id,positions.name'
            ])
            ->first(['id']);
        $response = [];
        foreach ($structure->positions as $position) {
            $response[] = [
                'id' => $position['id'],
                'name' => $position['name'],
                'quantity' => $position['pivot']['quantity'],
            ];
        }
        return $this->successResponse($response);
    }

    /**
     * @param Request $request
     * @param $companyId
     * @return JsonResponse
     */
    private function getPositionsWhichExistsInAnyStructure(Request $request, $companyId)
    {
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
    public function setStructurePositions(Request $request): JsonResponse
    {
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
        if ($request->get('structure_type') != 'company') {
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
        foreach ($request->get('positions') as $position) {
            $positions[] = [
                'structure_id' => $structureId,
                'structure_type' => $request->get('structure_type'),
                'position_id' => $position['id'],
                'quantity' => $position['quantity'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ];
        }

        return DB::transaction(function () use ($request, $positions, $structureId) {
            DB::table('structure_positions')->where([
                'structure_id' => $structureId,
                'structure_type' => $request->get('structure_type'),
            ])->delete();
            DB::table('structure_positions')->insert($positions);
            Cache::forget('company-structure-' . $request->get('company_id') . '-' . '*');
            return $this->successResponse(trans('messages.saved'), 200);
        });
    }

    /**
     * @param string $type
     * @return Model
     */
    private function getStructureModelByType(string $type): Model
    {
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
     * @param string $type
     * @return Model
     */
    private function getStructureModelNewInstanceByType(string $type): Model
    {
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
    private function ifStructureHasSubStructuresThrowException($structureId, $structureType)
    {
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
    private function saveStructure(Model $model, $linkId, $linkTYpe): void
    {
        $model->fill([
            'structable_id' => $linkId,
            'structable_type' => $linkTYpe
        ])->save();
    }

    /**
     * @return array
     */
    private function getStructureRules(): array
    {
        $rules = [
            'structure_id' => 'required|numeric',
            'structure_type' => [
                'required',
                Rule::in(['department', 'section', 'sector'])
            ],
            'link' => 'required|array',
            'link.id' => 'required_unless:link.type,company|numeric'
        ];
        if (\request()->get('structure_type') == 'department') {
            $rules['link.type'] = [
                'required',
                Rule::in(['company'])
            ];
        }
        if (\request()->get('structure_type') == 'section') {
            $rules['link.type'] = [
                'required',
                Rule::in(['company', 'department'])
            ];
        }
        if (\request()->get('structure_type') == 'sector') {
            $rules['link.type'] = [
                'required',
                Rule::in(['company', 'department', 'section'])
            ];
        }
        return $rules;
    }

}
