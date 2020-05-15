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
use Modules\Hr\Entities\Reward;

class RewardController extends Controller
{
    use ApiResponse, ValidatesRequests;

    private $reward;

    /**
     * RewardController constructor.
     * @param Reward $reward
     */
    public function __construct(Reward $reward)
    {
        $this->reward = $reward;
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

        $rewards = $this->reward
        ->whereBelongsToCompany($request->get('company_id'))
        ->with([
            'currency:id,name',
            'rewardType:id,name',
            'employee:id,user_id',
            'employee.user:id,name'
        ])
        ->orderBy('id', 'desc')
        ->paginate($request->get('per_page'));
        return  $this->successResponse($rewards);
    }


    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function show(Request $request, $id): JsonResponse {
        $reward = $this->reward
        ->whereBelongsToCompany($request->get('company_id'))
        ->with([
            'currency:id,name',
            'rewardType:id,name',
            'employee:id,user_id',
            'employee.user:id,name'
        ])
        ->orderBy('id', 'desc')
        ->firstOrFail();
        return  $this->successResponse($reward);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function create(Request $request): JsonResponse {
        $this->validate($request, $this->getRewardRules($request));
        $this->saveReward($request, $this->reward);
        return $this->successResponse(trans('messages.saved'), 201);
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     * @throws ValidationException
     */
    public function update(Request $request, $id): JsonResponse {
        $this->validate($request, $this->getRewardRules($request));
        $reward = $this->reward->where('id', $id)->whereBelongsToCompany($request->get('company_id'))->firstOrFail();
        $this->saveReward($request, $reward);
        return $this->successResponse(trans('messages.saved'), 200);
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function destroy(Request $request, $id): JsonResponse {
        $reward = $this->reward->where('id', $id)->whereBelongsToCompany($request->get('company_id'))->firstOrFail();
        return $reward->delete()
            ? $this->successResponse(trans('messages.saved'), 200)
            : $this->errorResponse(trans('messages.not_saved'), 400);
    }

    /**
     * @param Request $request
     * @param Reward $reward
     */
    private function saveReward(Request $request, Reward $reward): void {
        $reward->fill($request->only(
            array_keys($this->getRewardRules($request))
        ))->save();
    }

    /**
     * @param Request $request
     * @return array
     */
    private function getRewardRules(Request $request): array {
        return [
            'employee_id' => [
                'required',
                Rule::exists('employees', 'id')->where('company_id', $request->get('company_id'))
            ],
            'currency_id' => 'required|exists:currency,id',
            'reward_type_id' => 'required|exists:reward_types,id',
            'amount' => 'required|numeric',
            'date_of_issue' => 'required|date',
            'expire_date' => 'required|date',
        ];
    }
}
