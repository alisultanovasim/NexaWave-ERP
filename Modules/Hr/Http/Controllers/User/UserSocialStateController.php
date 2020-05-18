<?php

namespace Modules\Hr\Http\Controllers\User;

use App\Rules\IsValidEmployeeRule;
use App\Traits\ApiResponse;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\ValidationException;
use Modules\Hr\Entities\UserSocialState;

class UserSocialStateController extends Controller
{
    use ApiResponse, ValidatesRequests;

    private $socialState;

    /**
     * UserSocialStateController constructor.
     * @param UserSocialState $socialState
     */
    public function __construct(UserSocialState $socialState)
    {
        $this->socialState = $socialState;
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

        $states = $this->socialState
        ->with([
            'user:id,name,surname',
            'currency:id,name',
            'stateType:id,name'
        ])
        ->company()
        ->orderBy('id', 'desc')
        ->paginate($request->get('per_page'));
        return $this->successResponse($states);
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse{
        $state = $this->socialState
        ->where('id', $id)
        ->with([
            'user:id,name,surname',
            'currency:id,name',
            'stateType:id,name'
        ])
        ->company()
        ->orderBy('id', 'desc')
        ->firstOrFail();
        return  $this->successResponse($state);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function create(Request $request): JsonResponse {
        $this->validate($request, $this->getStateRules());
        $this->saveState($request, $this->socialState);
        return $this->successResponse(trans('messages.saved'), 201);
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     * @throws ValidationException
     */
    public function update(Request $request, $id): JsonResponse {
        $this->validate($request, $this->getStateRules());
        $state = $this->socialState->where('id', $id)->company()->firstOrFail(['id']);
        $this->saveState($request, $state);
        return $this->successResponse(trans('messages.saved'), 200);
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse {
        $state = $this->socialState->where('id', $id)->company()->firstOrFail(['id']);
        return $state->delete()
            ? $this->successResponse(trans('messages.saved'))
            : $this->errorResponse(trans('message.not_saved'), 400);
    }

    /**
     * @param Request $request
     * @param UserSocialState $socialState
     */
    private function saveState(Request $request, UserSocialState $socialState): void {
        $socialState->fill($request->only(
            array_keys($this->getStateRules())
        ))->save();
    }

    /**
     * @return array
     */
    private function getStateRules(): array {
        return [
            'user_id' => [
                'required',
                new IsValidEmployeeRule(\request()->get('company_id'))
            ],
            'currency_id' => 'required|exists:currency,id',
            'social_state_type_id' => 'required|exists:social_states,id',
            'document_name' => 'required|max:255',
            'document_number' => 'required|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'amount' => 'required|numeric',
            'note' => 'nullable|max:255'
        ];
    }

}
