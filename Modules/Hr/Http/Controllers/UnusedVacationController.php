<?php

namespace Modules\Hr\Http\Controllers;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Validation\ValidationException;
use Modules\Hr\Entities\UnusedVacation;

class UnusedVacationController extends Controller
{
    use ApiResponse, ValidatesRequests;

    private $unusedVacation;

    /**
     * UnusedVacationController constructor.
     * @param UnusedVacation $unusedVacation
     */
    public function __construct(UnusedVacation $unusedVacation)
    {
        $this->unusedVacation = $unusedVacation;
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

        $vacations = $this->unusedVacation
        ->companyId($request->get('company_id'))
        ->orderBy('id', 'desc')
        ->paginate($request->get('per_page'));

        return $this->successResponse($vacations);
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function show(Request $request, $id): JsonResponse {
        return $this->successResponse(
            $this->unusedVacation->companyId($request->get('company_id'))->where('id', $id)->firstOrFail()
        );
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function create(Request $request): JsonResponse {
        $this->validate($request, $this->getVacationRules());
        $this->saveVacation($request, $this->unusedVacation);
        return $this->successResponse(trans('messages.saved'), 201);
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     * @throws ValidationException
     */
    public function update(Request $request, $id): JsonResponse {
        $this->validate($request, $this->getVacationRules());
        $vacation = $this->unusedVacation->companyId($request->get('company_id'))->where('id', $id)->firstOrFail(['id']);
        $this->saveVacation($request, $vacation);
        return $this->successResponse(trans('messages.saved'), 200);
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function destroy(Request $request, $id): JsonResponse {
        $vacation = $this->unusedVacation->companyId($request->get('company_id'))->where('id', $id)->firstOrFail(['id']);
        return $vacation->delete()
            ? $this->successResponse(trans('messages.saved'), 200)
            : $this->errorResponse(trans('messages.not_saved'), 400);
    }

    /**
     * @param Request $request
     * @param UnusedVacation $unusedVacation
     */
    private function saveVacation(Request $request, UnusedVacation $unusedVacation): void {
        $this->unusedVacation->fill($request->only(
            array_keys($this->getVacationRules())
        ))->save();
    }

    /**
     * @return array
     */
    private function getVacationRules(): array {
        return [

        ];
    }

}
