<?php

namespace Modules\Hr\Http\Controllers;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Validation\ValidationException;
use Modules\Hr\Entities\CompanyEvent;

class CompanyEventController extends Controller
{
    use ApiResponse, ValidatesRequests;
    private $companyEvent;

    public function __construct(CompanyEvent $companyEvent)
    {
        $this->companyEvent = $companyEvent;
    }


    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request){
        $events = $this->companyEvent->where('company_id', $request->get('company_id'))->get();
        return $this->successResponse($events);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function create(Request $request): JsonResponse {
        $this->validate($request, $this->getEventRules());
        $this->saveEvent($request, $this->companyEvent);
        return $this->successResponse(trans('messages.saved'), 201);
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     * @throws ValidationException
     */
    public function update(Request $request, $id): JsonResponse {
        $this->validate($request, $this->getEventRules());
        $event = $this->companyEvent->where([
            'company_is' => $request->get('company_id'),
            'id' => $id
        ])
        ->firstOrFail(['id']);
        $this->saveEvent($request, $event);
        return $this->successResponse(trans('messages.saved'), 200);
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function destroy(Request $request, $id): JsonResponse {
        $event = $this->companyEvent->where([
            'company_is' => $request->get('company_id'),
            'id' => $id
        ])
        ->firstOrFail(['id']);
        $event->delete();
        return $this->successResponse(trans('messages.saved'), 200);
    }

    /**
     * @param Request $request
     * @param CompanyEvent $companyEvent
     * @return CompanyEvent
     */
    private function saveEvent(Request $request, CompanyEvent $companyEvent): CompanyEvent {
        $companyEvent->fill([
            'company_id' => $request->get('company_id'),
            'name' => $request->get('name'),
            'start_time' => $request->get('start_date'),
            'end_time' => $request->get('end_date'),
            'lunch_start_time' => $request->get('lunch_start_time'),
            'lunch_end_time' => $request->get('lunch_end_time'),
        ]);
        $companyEvent->save();
        return $companyEvent;
    }

    /**
     * @return array|string[]
     */
    private function getEventRules(): array {
        return [
            'name' => 'nullable|max:255|min:3',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'lunch_start_time' => 'required|date_format:H:i',
            'lunch_end_time' => 'required|date_format:H:i',
        ];
    }
}
