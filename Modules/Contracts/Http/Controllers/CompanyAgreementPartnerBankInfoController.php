<?php

namespace Modules\Contracts\Http\Controllers;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Modules\Contracts\Entities\CompanyAgreementPartnerBankInfo;
use Modules\Hr\Entities\BankInformation;

class CompanyAgreementPartnerBankInfoController extends Controller
{
    use ApiResponse, ValidatesRequests;

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $records = CompanyAgreementPartnerBankInfo::where('company_id', $request->get('company_id'))
        ->with('currency:id,name')
        ->get();

        return $this->successResponse($records);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function create(Request $request): JsonResponse
    {
        $this->validate($request, $this->getBankInfoRules());

        $this->save($request, new CompanyAgreementPartnerBankInfo());

        return $this->successResponse(trans('messages.saved'), 201);
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     * @throws ValidationException
     */
    public function update(Request $request, $id): JsonResponse
    {
        $this->validate($request, $this->getBankInfoRules());

        $info = CompanyAgreementPartnerBankInfo::where([
            'company_id' => $request->get('company_id'),
            'id' => $id
        ])->firstOrFail();
        $this->save($request, $info);

        return $this->successResponse(trans('messages.saved'));
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        CompanyAgreementPartnerBankInfo::where([
            'company_id' => $request->get('company_id'),
            'id' => $id
        ])->delete();

        return $this->successResponse(trans('messages.saved'));
    }

    /**
     * @param Request $request
     * @param CompanyAgreementPartnerBankInfo $agreementPartnerBankInfo
     * @return CompanyAgreementPartnerBankInfo
     */
    private function save(Request $request, CompanyAgreementPartnerBankInfo $agreementPartnerBankInfo): CompanyAgreementPartnerBankInfo
    {
        $agreementPartnerBankInfo->fill($request->all())->save();
        return $agreementPartnerBankInfo;
    }

    /**
     * @return array
     */
    private function getBankInfoRules(): array
    {
        return [
            'company_name' => 'required|min:2|max:500',
            'bank_name' => 'required|min:2|max:500',
            'account_number' => 'required|numeric',
            'bank_code' => 'required',
            'swift' => 'required',
            'currency_id' => [
                'required',
                Rule::exists('currency', 'id')
            ]
        ];
    }
}
