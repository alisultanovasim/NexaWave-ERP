<?php

namespace Modules\Hr\Http\Controllers\User;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\ValidationException;
use Modules\Hr\Entities\WorkActivity;

class WorkActivityController extends Controller
{
    use ApiResponse, ValidatesRequests;

    private $workActivity;

    public function __construct(WorkActivity $activity)
    {
        $this->workActivity = $activity;
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
        $workActivities = $this->workActivity
            ->with([

            ])
            ->company()
            ->orderBy('id', 'desc')
            ->paginate($request->get('per_page'));
        return $this->successResponse($workActivities);
    }

    public function show($id): JsonResponse {
        
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function create(Request $request): JsonResponse {
        $this->validate($request, $this->getWorkActivityRules($request));
        $this->saveWorkActivity($request, $this->workActivity);
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
        $activity = $this->workActivity->where('id', $id)->company()->firstOrFail(['id']);
        $this->saveWorkActivity($request, $activity);
        return $this->successResponse(trans('messages.saved'), 200);
    }

    public function destroy($id): JsonResponse {
        $activity = $this->workActivity->where('id', $id)->company()->firstOrFail(['id']);
        return $activity->delete()
            ? $this->successResponse(trans('messages.saved'))
            : $this->errorResponse(trans('messages.not_saved'), 400);
    }


    /**
     * @param Request $request
     * @param WorkActivity $workActivity
     */
    private function saveWorkActivity(Request $request, WorkActivity $workActivity): void {
        $workActivity->fill($request->only(array_keys($this->getWorkActivityRules($request))))->save();
    }

    /**
     * @param Request $request
     * @return array
     */
    private function getWorkActivityRules(Request $request): array {
        return [

        ];
    }
}
