<?php

namespace Modules\Hr\Http\Controllers\Employee;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Modules\Hr\Entities\Punishment;
use Modules\Hr\Entities\Reward;

class PunishmentController extends Controller
{
    use ApiResponse, ValidatesRequests;

    private $punishment;

    /**
     * RewardController constructor.
     * @param Punishment $punishment
     */
    public function __construct(Punishment $punishment)
    {
        $this->punishment = $punishment;
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

        $punishments = $this->punishment
        ->whereBelongsToCompany($request->get('company_id'))
        ->with([
            'currency:id,name',
            'punishmentType:id,name',
            'employee:id,user_id',
            'employee.user:id,name'
        ])
        ->orderBy('id', 'desc');
        if ($request->get('user_id'))
            $punishments = $punishments->where('user_id', $request->get('user_id'));
        $punishments = $punishments->paginate($request->get('per_page'));
        return  $this->successResponse($punishments);
    }


    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function show(Request $request, $id): JsonResponse {
        $punishment = $this->punishment
        ->where('id', $id)
        ->whereBelongsToCompany($request->get('company_id'))
        ->with([
            'currency:id,name',
            'punishmentType:id,name',
            'employee:id,user_id',
            'employee.user:id,name'
        ])
        ->orderBy('id', 'desc')
        ->firstOrFail();
        return  $this->successResponse($punishment);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function create(Request $request): JsonResponse {
        $this->validate($request, $this->getPunishmentRules($request));
        $this->savePunishment($request, $this->punishment);
        return $this->successResponse(trans('messages.saved'), 201);
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     * @throws ValidationException
     */
    public function update(Request $request, $id): JsonResponse {
        $this->validate($request, $this->getPunishmentRules($request));
        $punishment = $this->punishment->where('id', $id)->whereBelongsToCompany($request->get('company_id'))->firstOrFail();
        $this->savePunishment($request, $punishment);
        return $this->successResponse(trans('messages.saved'), 200);
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function destroy(Request $request, $id): JsonResponse {
        $punishment = $this->punishment->where('id', $id)->whereBelongsToCompany($request->get('company_id'))->firstOrFail();
        return $punishment->delete()
            ? $this->successResponse(trans('messages.saved'), 200)
            : $this->errorResponse(trans('messages.not_saved'), 400);
    }

    /**
     * @param Request $request
     * @param Punishment $punishment
     */
    private function savePunishment(Request $request, Punishment $punishment): void {
        $punishment->fill($request->only(
            array_keys($this->getPunishmentRules($request))
        ))->save();
    }

    /**
     * @param Request $request
     * @return array
     */
    private function getPunishmentRules(Request $request): array {
        return [
            'employee_id' => [
                'required',
                Rule::exists('employees', 'id')->where('company_id', $request->get('company_id'))
            ],
            'currency_id' => 'required|exists:currency,id',
            'punishment_type_id' => 'required|exists:punishment_types,id',
            'amount' => 'required|numeric',
            'date_of_issue' => 'required|date',
            'expire_date' => 'required|date',
            'reason' => 'nullable|max:255',
            'note' => 'nullable|max:255',
        ];
    }
}
