<?php

namespace Modules\Contracts\Http\Controllers;

use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Modules\Contracts\Entities\CompanyAgreement;
use Modules\Contracts\Entities\CompanyAgreementAddition;
use Modules\Contracts\Entities\CompanyAgreementContractType;
use Modules\Contracts\Entities\CompanyAgreementFile;
use Modules\Contracts\Entities\CompanyAgreementParticipant;
use Modules\Contracts\Entities\CompanyAgreementTermination;

class AgreementsController extends Controller
{
    use ApiResponse, ValidatesRequests;

    public function getContractTypes(Request $request): JsonResponse
    {
        $types = CompanyAgreementContractType::active()->get(['id', 'name']);
        return $this->successResponse($types);
    }

    public function getContractsCount(Request $request): JsonResponse
    {
    }

    public function getContracts(Request $request): JsonResponse
    {

    }

    public function createAgreement(Request $request): JsonResponse
    {
        $this->validate($request, $this->getAgreementRules($request));
        DB::transaction(function () use($request) {
            $contract = $this->saveAgreement($request, new CompanyAgreement());
            $this->saveAgreementParticipants($contract, $request->get('participants'));
            if ($request->get('files'))
                $this->saveAgreementFiles($contract, $request->get('files'));
        });

        return $this->successResponse(trans('messages.saved'), 201);
    }

    /**
     * @param Request $request
     * @param CompanyAgreement $companyContract
     * @return CompanyAgreement
     */
    public function saveAgreement(Request $request, CompanyAgreement $companyContract): CompanyAgreement
    {
        $companyContract->fill([
            'parent_id' => $request->get('parent_id'),
            'company_id' => $request->get('company_id'),
            'contract_type' => [
                'id' => $request->get('contract_type')['id'] ?? null,
                'name' => $request->get('contract_type')['name'] ?? null,
            ],
            'agreement_number' => $request->get('agreement_number'),
            'name' => $request->get('name'),
            'start_date' => $request->get('start_date'),
            'end_date' => $request->get('end_date'),
            'agreement_date' => $request->get('end_date'),
            'amount' => $request->get('amount'),
            'currency' => [
                'id' => $request->get('currency')['id'] ?? null,
                'name' => $request->get('currency')['name'] ?? null,
            ],
            'vat' => $request->get('vat'),
            'subject' => $request->get('subject'),
            'status' => $companyContract::activeStatus
        ])->save();
        return $companyContract;
    }

    /**
     * @param CompanyAgreement $companyAgreement
     * @param array $participants
     */
    public function saveAgreementParticipants(CompanyAgreement $companyAgreement, array $participants): void
    {
        $data = [];
        foreach ($participants as $participant) {
            $data[] = [
                'company_agreement_id' => $companyAgreement->getKey(),
                'company_id' => $participant['company_id'] ?? null,
                'name' => $participant['name'],
                'type' => $participant['type'],
                'tax_rate' => $participant['tax_rate'] ?? null,
                'bank_voen' => $participant['bank_voen'] ?? null,
                'company_voen' => $participant['company_voen'] ?? null,
                'account_number' => $participant['account_number'] ?? null,
                'bank_code' => $participant['bank_code'] ?? null,
                'bank_name' => $participant['bank_name'] ?? null,
                'correspondent_account' => $participant['correspondent_account'] ?? null,
                'signed_person_name' => $participant['signed_person_name'] ?? null,
                'signed_person_position' => $participant['signed_person_position'] ?? null,
                'swift' => $participant['swift'] ?? null,
                'intermediary_bank_name' => $participant['intermediary_bank_name'] ?? null,
                'intermediary_bank_swift' => $participant['intermediary_bank_swift'] ?? null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ];
        }
        CompanyAgreementParticipant::insert($data);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function addAdditionToAgreement(Request $request): JsonResponse
    {
        $this->validate($request, $this->getContractAdditionalAgreementRules($request));
        $agreement = CompanyAgreement::where([
            'company_id' => $request->get('company_id'),
            'id' => $request->get('company_agreement_id')
        ])->firstOrFail(['id']);
        DB::transaction(function () use ($request, $agreement) {
            $addition = CompanyAgreementAddition::create([
                'company_agreement_id' => $agreement->getKey(),
                'date' => $request->get('date'),
                'start_date' => $request->get('start_date'),
                'end_date' => $request->get('end_date'),
                'currency' => [
                    'id' => $request->get('currency')['id'] ?? null,
                    'name' => $request->get('currency')['name'] ?? null
                ],
                'subject' => $request->get('subject'),
                'amount' => $request->get('amount'),
                'amount_type' => $request->get('amount_type'),
            ]);
            if ($request->get('files'))
                $this->saveAgreementFiles($agreement, $request->get('files'), $addition->getKey());
        });
        return $this->successResponse(trans('messages.saved'), 201);
    }

    public function saveAgreementFiles(CompanyAgreement $companyAgreement, array $files, $additionalAgreementId = null): void
    {
        $data = [];
        foreach ($files as $index => $file) {
            $path = 'agreement_files/' . $companyAgreement->getKey();
            $originalName = \request()->files->get('files')[$index]['file']->getClientOriginalName();
            $extension = \request()->files->get('files')[$index]['file']->getClientOriginalExtension();
            $originalName = rtrim($originalName, '.' . $extension);
            $name = Str::slug($originalName);
            $name .= ".{$extension}";
            Storage::disk('public')->put($path . '/' . $name, \request()->files->get('files')[$index]['file']);
            $data[] = [
                'id' => Str::uuid(),
                'company_agreement_id' => $companyAgreement->getKey(),
                'name' => $file['name'],
                'file' => $path . '/' . $name
            ];
        }
        CompanyAgreementFile::insert($data);
    }

    /**
     * @param Request $request
     * @return array
     */
    private function getAgreementRules(Request $request): array
    {
        $rules = [
            'parent_id' => [
                'nullable',
                Rule::exists('company_agreements', 'id')->where(function ($query) use ($request){
                    $query->where('company_id', $request->get('company_id'));
                    $query->where('status', CompanyAgreement::activeStatus);
                })
            ],
            'agreement_type' => [
                'required',
                Rule::in(['internal', 'external'])
            ],
            'contract_type' => 'required|array',
            'contract_type.id' => 'required|integer',
            'contract_type.name' => 'required|max:255',
            'name' => 'required|max:500',
            'start_date' => 'required|date_format:Y-m-d',
            'end_date' => 'required|date_format:Y-m-d',
            'agreement_date' => 'required|date_format:Y-m-d',
            'amount' => 'required|numeric',
            'currency' => 'required|array',
            'currency.id' => 'required|integer',
            'currency.name' => 'required|max:150',
            'vat' => 'required|max:255',
            'subject' => 'required|max:500',
            'participants' => 'required|array',
            'participants.*.type' => [
                'required',
                Rule::in(['client', 'executor'])
            ],
            'participants.*.company_id' => [
                'nullable',
                Rule::exists('companies', 'id')
            ],
            'participants.*.name' => 'required|min:2|max:255',
            'participants.*.account_number' => 'required|max:255',
            'participants.*.swift' => 'required|max:255',
            'files' => 'nullable|array|min:1',
            'files.*.name' => 'required|max:255',
            'files.*.file' => 'required'
        ];
        for ($i = 0; $i < count($request->get('participants')); $i++) {
            if (
                $request->get('participants')[$i]['type'] == 'client' or
                ($request->get('agreement_type') == 'internal' and $request->get('participants')[$i]['type'] == 'executor'))
            {
                $rules['participants.' . $i . '.tax_rate'] = 'required|numeric';
                $rules['participants.' . $i . '.bank_voen'] = 'required|max:255';
                $rules['participants.' . $i . '.company_voen'] = 'required|max:255';
                $rules['participants.' . $i . '.account_number'] = 'required|max:255';
                $rules['participants.' . $i . '.bank_code'] = 'required|max:255';
                $rules['participants.' . $i . '.bank_name'] = 'required|max:255';
                $rules['participants.' . $i . '.correspondent_account'] = 'required|max:255';
                $rules['participants.' . $i . '.signed_person_name'] = 'required|max:255';
                $rules['participants.' . $i . '.signed_person_position'] = 'required|max:255';
            }
            if ($request->get('participants')[$i]['type'] == 'executor' and $request->get('agreement_type') == 'external') {
                $rules['participants.' . $i . '.intermediary_bank_name'] = 'required|min:2|max:255';
                $rules['participants.' . $i . '.intermediary_bank_swift'] = 'required|min:2|max:255';
            }
        }
        return $rules;
    }

    public function terminate(Request $request, $id): JsonResponse
    {
        $this->validate($request, $this->getContractTerminationRules($request));

        $contract = CompanyAgreement::where([
            'company_id' => $request->get('company_id'),
            'id' => $id
        ])->firstOrFail(['id']);

        DB::transaction(function () use ($request, $contract) {
            $contract->update(['status', CompanyAgreement::terminatedStatus]);
            CompanyAgreementTermination::create([
                'company_contract_id' => $contract->getKey(),
                'reason' => $request->get('reason'),
                'signed_by' => $request->get('signed_by'),
                'termination_date' => $request->get('termination_date'),
            ]);
        });


        return  $this->successResponse(trans('messages.saved'));
    }


    private function getContractTerminationRules(Request $request): array
    {
        return [
            'company_contract_id' => 'required|integer',
            'reason' => 'required|max:255',
            'signed_by' => 'required|max:255',
            'termination_date' => 'required|date_format:Y-m-d'
        ];
    }

    /**
     * @param Request $request
     * @return array
     */
    private function getContractAdditionalAgreementRules(Request $request): array
    {
        return [
            'company_agreement_id' => 'required|integer',
            'date' => 'required|date_format:Y-m-d',
            'start_date' => 'required|date_format:Y-m-d',
            'end_date' => 'required|date_format:Y-m-d',
            'currency' => 'required|array',
            'currency.id' => 'required|numeric',
            'currency.name' => 'required|max:50',
            'amount' => 'required|numeric',
            'amount_type' => [
                'required',
                Rule::in(['minus', 'plus'])
            ],
            'files' => 'nullable|array|min:1',
            'files.*.name' => 'required|max:255',
            'files.*.file' => 'required'
        ];
    }
}
