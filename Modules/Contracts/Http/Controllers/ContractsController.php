<?php

namespace Modules\Contracts\Http\Controllers;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Modules\Contracts\Entities\CompanyContract;
use Modules\Contracts\Entities\CompanyContractChange;
use Modules\Contracts\Entities\CompanyContractFile;

class ContractsController extends Controller
{
    use ApiResponse, ValidatesRequests;

    public function getCount(Request $request): JsonResponse
    {
        $count = CompanyContract::where([
            'company_id' => $request->get('company_id'),
            'parent_id' => null
        ])->count();
        return $this->successResponse(['count' => $count]);
    }

    public function index(Request $request): JsonResponse
    {
        $this->validate($request, [
            'per_page' => 'nullable|integer'
        ]);

        $contracts = CompanyContract::where([
            'company_id' => $request->get('company_id'),
            'parent_id' => null
        ])->with([
            'details',
            'files'
        ])->paginate($request->get('per_page'));

        return $this->successResponse($contracts);
    }

    public function create(Request $request): JsonResponse
    {

    }

    public function addChange(Request $request, $contractId): JsonResponse
    {
        $this->validate($request, [

        ]);
        $contract = CompanyContract::where([
            'id' => $contractId,
            'company_id' => $request->get('company_id')
        ])->firstOrFail(['id']);
        CompanyContractChange::create([
            'company_contract_id' => $contract->getKey(),
            ''
        ]);
        return $this->successResponse(trans('messages.saved'), 201);
    }

    private function saveContract(Request $request, CompanyContract $companyContract): CompanyContract
    {
        $companyContract->fill([

        ]);
        $companyContract->save();
        return $companyContract;
    }

    private function saveContractDetails(Request $request, CompanyContract $companyContract): void
    {

    }

    private function saveContractFiles(Request $request, CompanyContract $companyContract): void
    {

    }

    public function deleteContractFileById(Request $request, $id): JsonResponse
    {
        $file = CompanyContractFile::where('idd', $id)
        ->whereHas('contract', function ($query) use ($request){
            $query->where('company_id', $request->get('company_id'));
        })
        ->firstOrFail(['id']);
        $file->delete();
        return $this->successResponse(trans('messages.saved'));
    }

    public function destroy(Request $request, $id): JsonResponse
    {
        $contract = CompanyContract::where([
            'id' => $id,
            'company_id' => $request->get('company_id')
        ])->firstOrFail(['id']);
        $contract->delete();
        return $this->successResponse(trans('messages.saved'));
    }

    private function getContractRules(Request $request): array
    {
        return [
            'parent_id' => [
                'nullable',
                Rule::exists('company_contracts')->where('company_id', $request->get('company_id'))
            ],
            ''
        ];
    }
}
