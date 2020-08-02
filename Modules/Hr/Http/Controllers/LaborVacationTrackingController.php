<?php

namespace Modules\Hr\Http\Controllers;

use App\Filters\CompanyOrderFilters;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Modules\Hr\Entities\Employee\Contract;
use Modules\Hr\Entities\EmployeeOrders\Order;
use Modules\Hr\Entities\PastUnusedVacation;

class LaborVacationTrackingController extends Controller
{
    use ApiResponse, ValidatesRequests;

    private $orderFilters;
    private $companyOrder;

    public function __construct(CompanyOrderFilters $filters, Order $companyOrder)
    {
        $this->orderFilters = $filters;
        $this->companyOrder = $companyOrder;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function index(Request $request): JsonResponse
    {
        $this->validate($request, [
            'employee_id' => [
                'required',
                Rule::exists('employees', 'id')->where('company_id', $request->get('company_id'))
            ]
        ]);

        $laborTracking = [];
        $employeeContracts = $this->getEmployeeContracts($request->get('employee_id'));
        $employeePastUnusedVacations = $this->getPastUnusedVacations($request->get('employee_id'));
        $employeeLaborVacationOrders = $this->getLaborVacationOrders($request->get('employee_id'));

        $employeeContracts->map(function ($contract) use (&$laborTracking, $employeePastUnusedVacations, $employeeLaborVacationOrders){
            $beginningOfWorkYear = (int) Carbon::create($contract->start_date)->format('Y');
            $endOfWorkYear = (int) ($contract->end_date ?? Carbon::now()->format('Y'));
            if ($endOfWorkYear > (int) Carbon::now()->format('Y')){
                $endOfWorkYear = (int) Carbon::now()->format('Y');
            }
            while ($endOfWorkYear - $beginningOfWorkYear >= 1){
                /**
                 * Add unused vacations to contract vacation rights
                 */
                $pastUnusedVacations = $this->filterPastUnusedVacationsByDate($employeePastUnusedVacations, $beginningOfWorkYear);
                $vacationRightPrimary = $contract->vacation_main;
                $vacationRightAdditional = $contract->vacation_work_insurance + $contract->vacation_work_envs + $contract->vacation_for_child + $contract->vacation_collective_contract;
                $pastUnusedVacations->map(function ($vacation) use (&$vacationRightPrimary, &$vacationRightAdditional){
                    $vacationRightPrimary += $vacation->primary;
                    $vacationRightAdditional += $vacation->additional;
                });

                /**
                 * Add labor vacation orders to used vacations
                 */
                $usedVacations = $this->calculateUsedVacationByWorkYear($employeeLaborVacationOrders, $beginningOfWorkYear);
                $primaryVacationUsed = $usedVacations['primaryVacationUsed'];
                $additionalVacationUsed = $usedVacations['additionalVacationUsed'];

                $vacationRightSum = $vacationRightPrimary + $vacationRightAdditional;
                $usedVacationSum = $primaryVacationUsed + $additionalVacationUsed;
                $laborTracking[] = [
                    'beginning_of_work_year' => $beginningOfWorkYear,
                    'end_of_work_year' => $beginningOfWorkYear + 1,
                    'vacation_right' => [
                        'primary' => $vacationRightPrimary,
                        'additional' => $vacationRightAdditional,
                        'sum' => $vacationRightSum
                    ],
                    'used_vacation' => [
                        'primary' => $primaryVacationUsed,
                        'additional' => $additionalVacationUsed,
                        'sum' => $usedVacationSum,
                    ],
                    'remains' => $vacationRightSum - $usedVacationSum
                ];
                $beginningOfWorkYear++;
            }
        });

        $laborTracking = collect($laborTracking)->sortBy('beginning_of_work_year');

        return $this->successResponse($laborTracking);
    }

    /**
     * @param Collection $vacations
     * @param $beginningOfWorkYear
     * @return Collection
     */
    private function filterPastUnusedVacationsByDate(Collection $vacations, $beginningOfWorkYear): Collection
    {
        return $vacations->where('start_of_work_year', '=', $beginningOfWorkYear)
            ->where('end_of_work_year', '=', $beginningOfWorkYear + 1);
    }

    /**
     * @param Collection $vacationOrder
     * @param int $beginningOfWorkYear
     * @return array
     */
    private function calculateUsedVacationByWorkYear(Collection $vacationOrder, int $beginningOfWorkYear): array
    {
        $primaryVacationUsed = 0;
        $additionalVacationUsed = 0;
        $vacationOrder->map(function ($vacationOrder) use (&$primaryVacationUsed, &$additionalVacationUsed, $beginningOfWorkYear){
            foreach ($vacationOrder->employees as $employee){
                foreach ($employee->vacation_details as $detail){
                    if ($detail['beginning_of_work_year'] == $beginningOfWorkYear and $detail['end_of_work_year'] == $beginningOfWorkYear +1){
                        if ($detail['part_of_vacation'] == 'primary')
                            $primaryVacationUsed += $detail['day'];
                        if ($detail['part_of_vacation'] == 'additional')
                            $primaryVacationUsed += $detail['day'];
                    }
                }
            }
        });

        return [
            'primaryVacationUsed' => $primaryVacationUsed,
            'additionalVacationUsed' => $additionalVacationUsed
        ];
    }

    /**
     * @param $employeeId
     * @return Collection
     */
    private function getEmployeeContracts($employeeId): Collection
    {
        return Contract::where('employee_id', $employeeId)
        ->get([
            'id',
            'start_date',
            'end_date',
            'vacation_main',
            'vacation_work_insurance',
            'vacation_work_envs',
            'vacation_for_child',
            'vacation_collective_contract'
        ]);
    }

    /**
     * @param $employeeId
     * @return Collection
     */
    private function getPastUnusedVacations($employeeId): Collection
    {
        return PastUnusedVacation::where([
            'employee_id' => $employeeId
        ])
        ->get();
    }

    /**
     * @param $employeeId
     * @return Collection
     */
    private function getLaborVacationOrders($employeeId): Collection
    {
        $this->orderFilters->addFilter('has_employee_id', $employeeId);
        $this->orderFilters->addFilter('type', $this->companyOrder->getLaborVacationTypeId());
        $this->orderFilters->addFilter('is_confirmed', 1);

        return $this->companyOrder::with([
            'employees' => function ($query) {
                $query->select([
                    'id',
                    'order_id',
                    'details->employee_id as employee_id',
                    'details->vacation_start_date as vacation_start_date',
                    'details->vacation_end_date as vacation_end_date',
                    'details->vacation_details as vacation_details',
                ]);
            }
        ])
        ->filter($this->orderFilters)
        ->get(['id']);
    }
}
