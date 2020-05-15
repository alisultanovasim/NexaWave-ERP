<?php

namespace Modules\Hr\Http\Controllers;

use App\Rules\IsValidEmployeeRule;
use App\Traits\ApiResponse;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Validation\ValidationException;
use Modules\Hr\Entities\Salary;
use Psy\Util\Json;

class SalaryController extends Controller
{
    use ApiResponse, ValidatesRequests;

    private $salary;

    /**
     * SalaryController constructor.
     * @param Salary $salary
     */
    public function __construct(Salary $salary)
    {
        $this->salary = $salary;
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

        $salaries = $this->salary
        ->with([
            'user:id,name,surname',
            'position:id,name',
            'salaryType:id,name'
        ])
        ->company()
        ->orderBy('id', 'desc')
        ->paginate($request->get('per_page'));

        $this->successResponse($salaries);
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse {
        $salary = $this->salary
            ->where('id', $id)
            ->with([
                'user:id,name,surname',
                'position:id,name',
                'salaryType:id,name'
            ])
            ->company()
            ->orderBy('id', 'desc')
            ->firstOrFail();

        $this->successResponse($salary);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function create(Request $request): JsonResponse {
        $this->validate($request, $this->getSalaryRules($request));
        $this->saveSalary($request, $this->salary);
        return $this->successResponse(trans('messages.saved'), 201);
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     * @throws ValidationException
     */
    public function update(Request $request, $id): JsonResponse {
        $this->validate($request, $this->getSalaryRules($request));
        $salary = $this->salary->where('id', $id)->company()->firstOrFail(['id']);
        $this->saveSalary($request, $salary);
        return $this->successResponse(trans('messages.saved'), 200);
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse {
        $salary = $this->salary->where('id', $id)->company()->firstOrFail(['id']);
        return $salary->delete()
            ? $this->successResponse(trans('messages.saved'), 200)
            : $this->errorResponse(trans('messages.not_saved'), 400);
    }

    /**
     * @param Request $request
     * @param Salary $salary
     */
    private function saveSalary(Request $request, Salary $salary): void {
        $salary->fill($request->only(
            array_keys($this->getSalaryRules($request))
        ))->save();
    }

    /**
     * @param Request $request
     * @return array
     */
    private function getSalaryRules(Request $request): array {
        return [
            'position_id' => 'required|exists:positions,id',
            'salary_type_id' => 'required|exists:supplement_salary_types,id',
            'currency_id' => 'required|exists:currency,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'with_percentage' => 'required|boolean',
            'note' => 'nullable|max:250|min:3'
        ];
    }
}
