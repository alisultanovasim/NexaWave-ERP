<?php

namespace Modules\Hr\Http\Controllers;

use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Hr\Entities\Organization;
use Modules\Hr\Entities\OrganizationLink;
use App\Traits\ApiResponder;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Routing\Controller;

/**
 * Class OrganizationLinkController
 * @package Modules\Hr\Http\Controllers
 */
class OrganizationLinkController extends Controller
{
    use ApiResponse, ValidatesRequests;

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request){
        $paginateCount = $request->filled('paginateCount')
            ? $request->get('paginateCount')
            : config('defaults.paginateCount');
        $result = Organization::query()
        ->join('organization_links', 'organization_links.organization_id', 'organizations.id')
        ->join('departments', 'organization_links.department_id', 'departments.id')
        ->join('sectors', 'organization_links.sector_id', 'sectors.id')
        ->join('sections', 'organization_links.section_id', 'sections.id')
        ->select([
            'organization_links.*',
            'organizations.name as organization_name',
            'departments.name as department_name',
            'sectors.name as sector_name',
            'sections.name as section_name',
        ])
        ->orderBy('organizations.index', 'asc')
        ->orderBy('organization_links.position', 'asc')
        ->paginate($paginateCount);
        return $this->dataResponse($result);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function create(Request $request){
        $this->validate($request, $this->getRequestRules());
        try {
            $saved = OrganizationLink::create([
                'organization_id' => $request->get('organization_id'),
                'department_id' => $request->get('department_id'),
                'sector_id' => $request->get('sector_id'),
                'section_id' => $request->get('section_id'),
                'position' => $request->get('position'),
            ]);
        } catch (\Exception $e){
            $saved = false;
        }
        return $saved
            ? $this->successResponse(trans('messages.saved'), 201)
            : $this->errorResponse(trans('messages.not_saved'));
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     * @throws ValidationException
     */
    public function update(Request $request, $id){
        $this->validate($request, $this->getRequestRules($id));
        try {
            $link = OrganizationLink::where('id', $id)->first();
            if (!$link)
                return $this->errorResponse(trans('messages.not_found'), 404);
            $saved = $link->update([
                'organization_id' => $request->get('organization_id'),
                'department_id' => $request->get('department_id'),
                'sector_id' => $request->get('sector_id'),
                'section_id' => $request->get('section_id'),
                'position' => $request->get('position'),
            ]);
        } catch (\Exception $e){
            $saved = false;
        }
        return $saved
            ? $this->successResponse(trans('messages.saved'))
            : $this->errorResponse(trans('messages.not_saved'));
    }

    /**
     * @param $id
     * @return JsonResponse
     * @throws \Exception
     */
    public function destroy($id){
        $link = OrganizationLink::where('id', $id)->first();
        if (!$link)
            return $this->errorResponse(trans('messages.not_found'), 404);
        return $link->delete()
            ? $this->successResponse(trans('messages.saved'))
            : $this->errorResponse(trans('messages.not_saved'));
    }


    /**
     * @param null $id
     * @return array
     */
    private function getRequestRules($id = null){
        return [
            'organization_id' => 'required|exists:organizations,id',
            'department_id' => 'required|exists:departments,id',
            'sector_id' => 'required|exists:sectors,id',
            'section_id' => 'required|exists:sections,id',
            'position' => 'nullable|numeric'
        ];
    }
}
