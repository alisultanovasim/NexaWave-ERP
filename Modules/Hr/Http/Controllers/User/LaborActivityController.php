<?php

namespace Modules\Hr\Http\Controllers\User;

use App\Rules\IsValidEmployeeRule;
use App\Traits\ApiResponse;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\ValidationException;
use Modules\Hr\Entities\LaborActivity;

class LaborActivityController extends Controller
{
    use ApiResponse, ValidatesRequests;

    private $laborActivity;

    /**
     * LaborActivityController constructor.
     * @param LaborActivity $activity
     */
    public function __construct(LaborActivity $activity)
    {
        $this->laborActivity = $activity;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function index(Request $request): JsonResponse {
        $this->validate($request, [
            'per_page' => 'nullable|integer',
            'user_id' => 'nullable|numeric'
        ]);
        $laborActivities = $this->laborActivity
            ->with([
                'user:id,name,surname',
                'country:id,name',
                'city:id,name',
                'region:id,name'
            ])
            ->company()
            ->orderBy('id', 'desc');
        if ($request->get('user_id'))
            $laborActivities = $laborActivities->where('user_id', $request->get('user_id'));
        $laborActivities = $laborActivities->paginate($request->get('per_page'));
        return $this->successResponse($laborActivities);
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse {
        $laborActivities = $this->laborActivity
        ->where('id', $id)
        ->with([
            'user:id,name,surname',
            'country:id,name',
            'city:id,name',
            'region:id,name'
        ])
        ->company()
        ->orderBy('id', 'desc')
        ->firstOrFail();
    return $this->successResponse($laborActivities);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function create(Request $request): JsonResponse {
        $this->validate($request, $this->getWorkActivityRules($request));
        $this->saveWorkActivity($request, $this->laborActivity);
        return $this->successResponse(trans('messages.saved'), 201);
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     * @throws ValidationException
     */
    public function update(Request $request, $id): JsonResponse {
        $this->validate($request, $this->getWorkActivityRules($request));
        $activity = $this->laborActivity->where('id', $id)->company()->firstOrFail(['id']);
        $this->saveWorkActivity($request, $activity);
        return $this->successResponse(trans('messages.saved'), 200);
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse {
        $activity = $this->laborActivity->where('id', $id)->company()->firstOrFail(['id']);
        return $activity->delete()
            ? $this->successResponse(trans('messages.saved'))
            : $this->errorResponse(trans('messages.not_saved'), 400);
    }


    /**
     * @param Request $request
     * @param LaborActivity $laborActivity
     */
    private function saveWorkActivity(Request $request, LaborActivity $laborActivity): void {
        $laborActivity->fill([
            'user_id' => $request->get('user_id'),
            'country_id' => $request->get('country_id'),
            'city_id' => $request->get('city_id'),
            'region_id' => $request->get('region_id'),
            'company_name' => $request->get('company_name'),
            'structure' => $request->get('structure'),
            'sector' => $request->get('sector'),
            'position' => $request->get('position'),
            'start_date' => $request->get('start_date'),
            'end_date' => $request->get('end_date'),
            'labor_book_number' => $request->get('labor_book_number'),
            'labor_book_filling_date' => $request->get('labor_book_filling_date'),
            'labor_bool_stuffing_number' => $request->get('labor_bool_stuffing_number'),
            'company_id' => $request->get('in_this_company') ? $request->get('company_id') : null,
            'is_civil_service' => $request->get('is_civil_service'),
            'termination_reason' => $request->get('termination_reason'),
            'note' => $request->get('note'),
        ])->save();
    }

    /**
     * @param Request $request
     * @return array
     */
    private function getWorkActivityRules(Request $request): array {
        return [
            'user_id' => [
                'required',
                new IsValidEmployeeRule($request->get('company_id'))
            ],
            'country_id' => 'required|exists:countries,id',
            'city_id' => 'required|exists:cities,id',
            'region_id' => 'required|exists:regions,id',
            'company_name' => 'required|max:255|min:3',
            'structure' => 'required|min:3|max:255',
            'sector' => 'required|min:3|max:255',
            'position' => 'required|min:3|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'labor_book_number' => 'required',
            'labor_book_filling_date' => 'required|date',
            'labor_bool_stuffing_number' => 'required',
            'in_this_company' => 'nullable|boolean',
            'is_civil_service' => 'required|boolean',
            'termination_reason' => 'nullable|min:3|max:255',
            'note' => 'nullable|min:3|max:255',
        ];
    }
}
