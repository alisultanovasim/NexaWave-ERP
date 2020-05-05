<?php

namespace Modules\Hr\Http\Controllers;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Validation\ValidationException;
use Modules\Hr\Entities\Inventory;
use Modules\Hr\Entities\Uniform;

class UniformController extends Controller
{
    use ApiResponse, ValidatesRequests;

    private $uniform;

    public function __construct(Uniform $uniform)
    {
        $this->uniform = $uniform;
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

        $uniforms = $this->uniform
        ->companyId($request->get('company_id'))
        ->with([
            'uniformType:id,name'
        ])
        ->orderBy('id', 'desc')
        ->paginate($request->get('per_page'));

        return $this->dataResponse($uniforms);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function create(Request $request): JsonResponse {
        $this->validate($request, $this->getUniformRules());
        $this->saveUniform($request, $this->uniform);
        return  $this->successResponse(trans('messages.saved'), 201);
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     * @throws ValidationException
     */
    public function update(Request $request, $id): JsonResponse {
        $this->validate($request, $this->getUniformRules());
        $uniform = $this->findOrFailUniform($request, $id);
        $this->saveUniform($request, $uniform);
        return  $this->successResponse(trans('messages.saved'), 200);
    }

    /**
     * @param Request $request
     * @param Uniform $uniform
     */
    private function saveUniform(Request $request, Uniform $uniform): void {
        $uniform->fill($request->only([
            'uniform_type_id',
            'size',
            'note'
        ]))->save();
    }


    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function destroy(Request $request, $id){
        $uniform = $this->findOrFailUniform($request, $id);
        $uniform->delete();
        return  $this->successResponse(trans('messages.saved'), 200);
    }

    private function findOrFailUniform(Request $request, $id){
        return $uniform = $this->uniform
            ->companyId($request->get('company_id'))
            ->firstOrFail(['id']);
    }

    /**
     * @return array
     */
    private function getUniformRules(): array {
        return [
            'uniform_type_id' => 'required|exists:uniform_types,id',
            'size' => 'required|max:50',
            'note' => 'nullable|max:255|min:2'
        ];
    }

}
