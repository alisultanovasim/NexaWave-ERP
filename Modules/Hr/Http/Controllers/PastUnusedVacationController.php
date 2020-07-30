<?php

namespace Modules\Hr\Http\Controllers;

use App\Traits\ApiResponse;
use Exception;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Modules\Hr\Entities\PastUnusedVacation;

class PastUnusedVacationController extends Controller
{
    use ApiResponse, ValidatesRequests;

    private $pastUnusedVacation;

    public function __construct(PastUnusedVacation $pastUnusedVacation)
    {
        $this->pastUnusedVacation = $pastUnusedVacation;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function index(Request $request): JsonResponse {
        $this->validate($request, [
            'per_page' => 'nullable|integer'
        ]);

        $vacations = $this->pastUnusedVacation
        ->belongsToCompany($request->get('company_id'))
        ->with([
            'employee:id,user_id',
            'employee.user:id,name,surname'
        ])
        ->orderBy('id', 'desc')
        ->paginate($request->get('per_page'));

        return $this->successResponse($vacations);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function create(Request $request): JsonResponse {
        $this->validate($request, $this->getVacationRules($request));
        $this->saveVacation($request, $this->pastUnusedVacation);
        return $this->successResponse(trans('messages.saved'), 201);
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     * @throws ValidationException
     */
    public function update(Request $request, $id): JsonResponse {
        $this->validate($request, $this->getVacationRules($request));
        $vacation = $this->getCompanyVacationOrFail($request->get('company_id'), $id);
        $this->saveVacation($request, $vacation);
        return $this->successResponse(trans('messages.saved'), 200);
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     * @throws Exception
     */
    public function destroy(Request $request, $id): JsonResponse {
        $vacation = $this->getCompanyVacationOrFail($request->get('company_id'), $id);
        $vacation->delete();
        return $this->successResponse(trans('messages.saved'), 200);
    }

    /**
     * @param $companyId
     * @param $id
     * @return PastUnusedVacation
     */
    private function getCompanyVacationOrFail($companyId, $id): PastUnusedVacation {
        return $this->pastUnusedVacation->belongsToCompany($companyId)
            ->where('id', $id)->firstOrFail(['id']);
    }

    /**
     * @param Request $request
     * @param PastUnusedVacation $pastUnusedVacation
     * @return PastUnusedVacation
     */
    private function saveVacation(Request $request, PastUnusedVacation $pastUnusedVacation): PastUnusedVacation {
        $pastUnusedVacation->fill($request->only(
            array_keys($this->getVacationRules($request))
        ));
        $pastUnusedVacation->save();
        return $pastUnusedVacation;
    }

    /**
     * @param Request $request
     * @return array|array[]
     */
    private function getVacationRules(Request $request): array {
        return [
            'employee_id' => [
                'required',
                Rule::exists('employees', 'id')->where('company_id', $request->get('company_id'))
            ],
            'start_of_work_year' => 'required|date|date_format:Y-m-d',
            'end_of_work_year' => 'required|date|date_format:Y-m-d',
            'primary' => 'required|numeric',
            'additional' => 'required|numeric',
            'note' => 'nullable|max:255|min:3',
        ];
    }
}
