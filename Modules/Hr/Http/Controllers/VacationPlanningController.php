<?php

namespace Modules\Hr\Http\Controllers;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Modules\Hr\Entities\VacationPlanning;

class VacationPlanningController extends Controller
{
    use ApiResponse, ValidatesRequests;

    private $vacationPlanning;

    /**
     * VacationPlanningController constructor.
     * @param VacationPlanning $vacationPlanning
     */
    public function __construct(VacationPlanning $vacationPlanning)
    {
        $this->vacationPlanning = $vacationPlanning;
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

        $plans = $this->vacationPlanning
        ->isBelongsToCompany($request->get('company_id'))
        ->with([
            'employee:id,user_id',
            'employee.user:id,name,surname'
        ])
        ->paginate($request->get('per_page'));

        return $this->successResponse($plans);
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function show(Request $request, $id): JsonResponse {
        $plan = $this->vacationPlanning
        ->isBelongsToCompany($request->get('company_id'))
        ->where('id', $id)
        ->with([
            'employee:id,user_id',
            'employee.user:id,name,surname'
        ])
        ->firstOrFail();

        return $this->successResponse($plan);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function create(Request $request): JsonResponse {
        $this->validate($request, $this->getPlanningRules());
        $this->save($request, $this->vacationPlanning);
        return $this->successResponse(trans('messages.saved'), 201);
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     * @throws ValidationException
     */
    public function update(Request $request, $id): JsonResponse {
        $this->validate($request, $this->getPlanningRules());
        $plan = $this->vacationPlanning->isBelongsToCompany($request->get('company_id'))->where('id', $id)->firstOrFail(['id']);
        $this->save($request, $plan);
        return $this->successResponse(trans('messages.saved'), 200);
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function destroy(Request $request, $id): JsonResponse {
        $plan = $this->vacationPlanning->isBelongsToCompany($request->get('company_id'))->where('id', $id)->firstOrFail(['id']);
        return $plan->delete()
            ? $this->successResponse(trans('messages.saved'), 200)
            : $this->successResponse(trans('messages.not_saved'), 400);
    }

    /**
     * @param Request $request
     * @param VacationPlanning $planning
     */
    private function save(Request $request, VacationPlanning $planning): void {
        $planning->fill($request->only([
            'employee_id',
            'start_date',
            'end_date',
            'beginning_of_work_year',
            'end_of_work_year',
            'day',
            'note'
        ]))->save();
    }

    /**
     * @return array|string[]
     */
    private function getPlanningRules(): array {
        return [
            'employee_id' => [
                'required',
                Rule::exists('employees', 'id')->where('company_id', \request()->get('company_id'))
            ],
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'beginning_of_work_year' => 'required|numeric|min:2020',
            'end_of_work_year' => 'required|numeric|min:2020',
            'day' => 'required|numeric',
            'note' => 'nullable|max:255'
        ];
    }


}
