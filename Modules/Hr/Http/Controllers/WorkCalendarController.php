<?php

namespace Modules\Hr\Http\Controllers;

use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Modules\Hr\Entities\WorkCalendar;
use Modules\Hr\Entities\WorkCalendarDetail;

class WorkCalendarController extends Controller
{
    use ApiResponse, ValidatesRequests;

    private $calendar;

    public function __construct(WorkCalendar $calendar)
    {
        $this->calendar = $calendar;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function index(Request $request): JsonResponse {

        $this->validate($request, [
            'year' => 'required|numeric',
            'month' => 'required|between:1,12',
        ]);

        $startOfMonth = Carbon::create($request->get('year'), $request->get('month'))->startOfMonth();
        $endOfMonth = Carbon::create($request->get('year'), $request->get('month'))->endOfMonth();
        $calendar = $this->calendar
        ->companyId($request->get('company_id'))
        ->whereHas('details', function ($query) use ($request){
            $query->where('employee_id', $request->get('employee_id'));
        })
        ->whereBetween('date', [$startOfMonth, $endOfMonth])
        ->with([
            'details' => function ($query) use ($request){
                $query->where('employee_id', $request->get('employee_id'));
                $query->select(['id', 'work_calendar_id', 'employee_id', 'event']);
            }
        ])
        ->orderBy('date')
        ->get([
            'id',
            'date'
        ]);
        return $this->successResponse($calendar);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function create(Request $request): JsonResponse {

        $this->validate($request, [
            'date' => 'required|date',
            'event' => 'required|max:255',
            'employee_id' => [
                'nullable',
                Rule::exists('employees', 'id')->where('company_id', $request->get('company_id'))
            ]
        ]);

        return DB::transaction(function () use ($request){
            $dayOnCalendar = $this->firstOrCreate($request->get('date'));
            WorkCalendarDetail::create([
                'work_calendar_id' => $dayOnCalendar->getKey(),
                'employee_id' => $request->get('employee_id'),
                'event' => $request->get('event')
            ]);

            return $this->successResponse(trans('messages.saved'), 201);
        });

    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function remove(Request $request, $id): JsonResponse {
        $calendar = $calendar = $this->calendar
        ->companyId($request->get('company_id'))
        ->whereHas('details', function ($query) use ($id){
            $query->where('id', $id);
        })
        ->firstOrFail(['id']);
        return WorkCalendarDetail::where('id', $id)->delete()
            ? $this->successResponse(trans('messages.saved'), 200)
            : $this->errorResponse(trans('messages.not_saved'), 400);
    }

    private function firstOrCreate($date): WorkCalendar {
        $calendar = WorkCalendar::where('date', $date)->first(['id']);
        if (!$calendar){
            $calendar = WorkCalendar::create([
                'date' => \request()->get('date'),
                'company_id' => \request()->get('company_id')
            ]);
        }

        return $calendar;
    }
}
