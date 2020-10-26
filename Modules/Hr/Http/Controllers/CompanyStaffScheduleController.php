<?php

namespace Modules\Hr\Http\Controllers;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Hr\Entities\Employee\Contract;
use Modules\Hr\Entities\StructurePosition;
use Modules\Hr\Services\CompanyStructureService;
use Illuminate\Database\Eloquent\Builder;

class CompanyStaffScheduleController extends Controller
{
    use ApiResponse, ValidatesRequests;

    private $companyStructureService;

    public function __construct(CompanyStructureService $companyStructureService)
    {
        $this->companyStructureService = $companyStructureService;
    }

    public function index(Request $request): JsonResponse
    {
        $companyStructure = $this->companyStructureService->getStructure($request->get('company_id'), false);

        return $this->successResponse($this->getStaffByStructure($companyStructure));
    }

    private function getStaffByStructure($structure, $departmentId = null, $sectionId = null, $sectorId = null): array
    {
        $staff = [];

        foreach ($structure['structuredDepartments'] ?? [] as $department) {
            $staff[] = [
                'id' => $department['id'],
                'name' => $department['name'],
                'type' => 'department',
                'positions' => $this->getStructurePositions($department['id'], $sectionId, $sectorId, $department['id'], 'department'),
                'children' => $this->getStaffByStructure($department, $department['id'])
            ];
        }

        foreach ($structure['structuredSections'] ?? [] as $section) {
            $staff[] = [
                'id' => $section['id'],
                'name' => $section['name'],
                'type' => 'section',
                'positions' => $this->getStructurePositions($departmentId, $section['id'], $sectorId, $section['id'], 'section'),
                'children' => $this->getStaffByStructure($section, $departmentId, $section['id'], $sectorId)
            ];
        }

        foreach ($structure['structuredSectors'] ?? [] as $sector) {
            $staff[] = [
                'id' => $sector['id'],
                'name' => $sector['name'],
                'type' => 'sector',
                'positions' => $this->getStructurePositions($departmentId, $sectionId, $sector['id'], $sector['id'], 'sector'),
                'children' => $this->getStaffByStructure($sector, $departmentId, $sectorId, $sector['id'])
            ];
        }

        return $staff;
    }

    public function getStructurePositions(
        $departmentId, $sectionId, $sectorId, $structureId, $structureType
    ){

        $positions = StructurePosition::where([
            'structure_positions.structure_id' => $structureId,
            'structure_positions.structure_type' => $structureType,
        ])
        ->join('positions', 'positions.id', 'structure_positions.position_id')
        ->leftJoin('employee_contracts', function ($query) use ($departmentId, $sectionId, $sectorId) {
            $query->on('employee_contracts.position_id', 'positions.id');
            $query->where([
                'employee_contracts.department_id' => $departmentId,
                'employee_contracts.section_id' => $sectionId,
                'employee_contracts.sector_id' => $sectorId,
                'employee_contracts.is_active' => 1,
                'employee_contracts.is_terminated' => 0,
            ]);
        })
        ->select([
            'positions.id',
            'positions.name',
            'structure_positions.quantity as allocated_staff_count',
            DB::raw('count(employee_contracts.id) as current_stuff_count')
        ])
        ->groupBy('positions.id')
        ->get();

        return $positions;
    }

}
