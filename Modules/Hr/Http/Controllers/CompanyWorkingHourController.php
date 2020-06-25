<?php

namespace Modules\Hr\Http\Controllers;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Modules\Hr\Entities\CompanyWorkingHour;

class CompanyWorkingHourController extends Controller
{
    use ApiResponse, ValidatesRequests;

    private $workingHour;

    public function __construct(CompanyWorkingHour $workHour)
    {
        $this->workingHour = $workHour;
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

        $workHours = $this->workingHour
        ->where('company_id', $request->get('company_id'))
        ->with([
            'contract:id,name'
        ])
        ->orderBy('id', 'desc')
        ->paginate($request->get('per_page'));

        return $this->successResponse($workHours);
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function show(Request $request, $id): JsonResponse {

        $workHour = $this->workingHour
            ->where('company_id', $request->get('company_id'))
            ->with([
                'contract:id,name'
            ])
            ->orderBy('id', 'desc')
            ->firstOrFail();

        return $this->successResponse($workHour);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function create(Request $request): JsonResponse {
        $this->validate($request, $this->getWorkingHourRules());
        $this->saveWorkingHour($request, $this->workingHour);
        return $this->successResponse(trans('messages.saved'), 201);
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     * @throws ValidationException
     */
    public function update(Request $request, $id): JsonResponse {
        $this->validate($request, $this->getWorkingHourRules());
        $workHours = $this->workingHour->where(['id' => $id, 'company_id' => $request->get('company_id')])->firstOrFail(['id']);
        $this->saveWorkingHour($request, $workHours);
        return $this->successResponse(trans('messages.saved'), 200);
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function destroy(Request $request, $id): JsonResponse {
        $workHours = $this->workingHour->where(['id' => $id, 'company_id' => $request->get('company_id')])->firstOrFail(['id']);
        return $workHours->delete()
            ? $this->successResponse(trans('messages.saved'), 200)
            : $this->errorResponse(trans('messages.not_saved'), 400);
    }

    /**
     * @param Request $request
     * @param CompanyWorkingHour $workingHour
     */
    private function saveWorkingHour(Request $request, CompanyWorkingHour $workingHour): void {
        $workingHour->fill([
            'company_id' => $request->get('company_id'),
            'contract_id' => $request->get('contract_id'),
            'count_of_work_days' => $request->get('count_of_work_days'),
            'count_of_day_offs' => $request->get('count_of_day_offs'),
            'year' => $request->get('year'),
            'month' => $request->get('month'),
            'monthly_working_hour_norms' => $request->get('monthly_working_hour_norms'),
        ])->save();
    }

    /**
     * @return array
     */
    private function getWorkingHourRules(): array {
        return [
            'contract_id' => [
                'required',
                Rule::exists('contracts', 'id')->where('company_id', \request()->get('company_id'))
            ],
            'count_of_work_days' => 'required|numeric',
            'count_of_day_offs' => 'required|numeric',
            'year' => 'required|numeric',
            'month' => 'required|numeric',
            'monthly_working_hour_norms' => 'required|numeric'
        ];
    }

}
