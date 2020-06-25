<?php

namespace Modules\Hr\Http\Controllers;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Modules\Hr\Entities\WorkSkip;

class WorkSkipsController extends Controller
{
    use ApiResponse, ValidatesRequests;

    private $workSkip;


    /**
     * WorkSkipsController constructor.
     * @param WorkSkip $workSkip
     */
    public function __construct(WorkSkip $workSkip)
    {
        $this->workSkip = $workSkip;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function index(Request $request): JsonResponse {
        $this->validate($request, [
            'per_page' => 'nullable|integer',
            'reason_type' => [
                'required',
                Rule::in($this->workSkip->getReasonTypes())
            ]
        ]);

        $workSkips = $this->workSkip
        ->companyId($request->get('company_id'))
        ->with([
            'employee:id,user_id',
            'employee.user:id,name,surname',
            'confirmedEmployee:id,user_id',
            'confirmedEmployee.user:id,name,surname',
        ])
        ->orderBy('id', 'desc');
        if ($request->get('reason_type'))
            $workSkips = $workSkips->where('reason_type', $request->get('reason_type'));
        if ($request->get('employee_id'))
            $workSkips = $workSkips->where('employee_id', $request->get('employee_id'));
        $workSkips = $workSkips->paginate($request->get('per_page'));

        return $this->successResponse($workSkips);
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function show(Request $request, $id): JsonResponse {
        $log = $this->workSkip
            ->companyId($request->get('company_id'))
            ->where('id', $id)
            ->with([
                'employee:id,user_id',
                'employee.user:id,name,surname',
                'confirmedEmployee:id,user_id',
                'confirmedEmployee.user:id,name,surname',
            ])
            ->firstOrFail();
        return  $this->successResponse($log);
    }


    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function create(Request $request): JsonResponse {
        $this->validate($request, $this->getWorkSkipRules());
        $this->saveWorkSkipDocument($request, $this->workSkip);
        return $this->successResponse(trans('messages.saved'), 201);
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     * @throws ValidationException
     */
    public function update(Request $request, $id): JsonResponse {
        $this->validate($request, $this->getWorkSkipRules());
        $log = $this->workSkip->companyId($request->get('company_id'))->where('id', $id)->firstOrFail(['id']);
        $this->saveWorkSkipDocument($request, $log);
        return $this->successResponse(trans('messages.saved'), 200);
    }


    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function destroy(Request $request, $id): JsonResponse {
        $log = $this->workSkip->companyId($request->get('company_id'))->where('id', $id)->firstOrFail(['id']);
        return $log->delete()
            ? $this->successResponse(trans('messages.saved'), 200)
            : $this->errorResponse(trans('messages.not_saved'), 400);
    }

    /**
     * @param Request $request
     * @param WorkSkip $workSkip
     */
    private function saveWorkSkipDocument(Request $request, WorkSkip $workSkip): void {
        $workSkip->fill($request->only([
            'company_id',
            'employee_id',
            'document_number',
            'reason_type',
            'day',
            'date_of_presentation',
            'start_date',
            'end_date',
            'is_confirmed',
            'confirmed_employee_id',
            'work_start_date',
            'note'
        ]))->save();
    }

    /**
     * @return array
     */
    private function getWorkSkipRules(): array  {
        return  [
            'employee_id' => [
                'required',
                Rule::exists('employees', 'id')->where('company_id', \request()->get('company_id'))
            ],
            'document_number' => 'required_if:reason_type,1|max:255',
            'reason_type' => [
                'required',
                Rule::in($this->workSkip->getReasonTypes())
            ],
            'day' => 'required|integer',
            'date_of_presentation' => 'required_if:reason_type,1|date',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'is_confirmed' => 'required|boolean',
            'confirmed_employee_id' => [
                'required_if:is_confirmed,1',
                Rule::exists('employees', 'id')->where('company_id', \request()->get('company_id'))
            ],
            'work_start_date' => 'required|date',
            'note' => 'nullable|max:255'
        ];
    }


}
