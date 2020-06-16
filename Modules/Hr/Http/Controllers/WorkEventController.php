<?php

namespace Modules\Hr\Http\Controllers;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\ValidationException;
use Modules\Hr\Entities\WorkEvent;

class WorkEventController extends Controller
{
    use ApiResponse, ValidatesRequests;

    private $event;

    public function __construct(WorkEvent $event)
    {
        $this->event = $event;
    }

    public function index(Request $request) : JsonResponse {
        $this->validate($request, [
            'per_page' => 'nullable|integer'
        ]);

        $events = $this->event->companyId($request->get('company_id'))
        ->orderBy('id', 'desc')
        ->paginate($request->get('per_page'));
        return $this->successResponse($events);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function create(Request $request): JsonResponse {
        $this->validate($request, $this->getEventRules());
        $this->saveEvent($request, $this->event);
        return  $this->successResponse(trans('messages.saved'), 201);
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     * @throws ValidationException
     */
    public function update(Request $request, $id): JsonResponse {
        $this->validate($request, $this->getEventRules());
        $event = $this->event->companyId($request->get('company_id'))->firstOfFail(['id']);
        $this->saveEvent($request, $event);
        return  $this->successResponse(trans('messages.saved'), 200);
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function destroy(Request $request, $id): JsonResponse {
        $event = $this->event->companyId($request->get('company_id'))->firstOfFail(['id']);
        return $event->delete()
            ? $this->successResponse(trans('messages.saved'))
            : $this->errorResponse(trans('messages.not_saved'), 400);
    }


    /**
     * @param Request $request
     * @param WorkEvent $event
     */
    public function saveEvent(Request $request, WorkEvent $event): void {
        $event->fill($request->only([
            'type',
            'start_time',
            'end_time',
            'lunch_start_time',
            'lunch_end_time',
            'company_id'
        ]))->save();
    }

    /**
     * @return array
     */
    private function getEventRules(): array {
        return  [
            'type' => 'required|max:255',
            'start_time' => 'required|time',
            'end_time' => 'required|time',
            'lunch_start_time' => 'required|time',
            'lunch_end_time' => 'required|time',
        ];
    }
}
