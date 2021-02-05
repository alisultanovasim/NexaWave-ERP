<?php

namespace Modules\Hr\Http\Controllers;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
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
            'uniformType:id,name',
            'employee:id,user_id',
            'employee.user:id,name,surname'
        ])
        ->orderBy('id', 'desc');
        if ($request->get('user_id')){
            $uniforms = $uniforms->whereHas('employee.user', function ($query) use ($request){
                $query->where('id', $request->get('user_id'));
            });
        }
        $uniforms = $uniforms->paginate($request->get('per_page'));

        return $this->dataResponse($uniforms);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function create(Request $request): JsonResponse {
        $this->validate($request, $this->getUniformRules($request));
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
        $this->validate($request, $this->getUniformRules($request));
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
            'employee_id',
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
     * @param Request $request
     * @return array|string[]
     */
    private function getUniformRules(Request $request): array {
        return [
            'uniform_type_id' => [
                'required',
                Rule::exists('uniform_types', 'id')->where('company_id', $request->get('company_id'))
            ],
            'employee_id' => [
                'required',
                Rule::exists('employees', 'id')->where('company_id', $request->get('company_id'))
            ],
            'size' => 'required|max:50',
            'note' => 'nullable|max:255|min:2'
        ];
    }

}
