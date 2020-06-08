<?php

namespace Modules\Hr\Http\Controllers\User;

use App\Repositories\CrudRepository;
use App\Rules\IsValidEmployeeRule;
use App\Traits\ApiResponse;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\ValidationException;
use Modules\Hr\Entities\ContactInformation;
use Modules\Hr\Entities\UserCertificate;

class ContactInformationController extends Controller
{
    use ApiResponse, ValidatesRequests;

    private $contacts;
    private $crudRepository;

    public function __construct(ContactInformation $contacts)
    {
        $this->contacts = $contacts;
        $this->crudRepository = new CrudRepository($contacts);
    }

    public function index(Request $request): JsonResponse {
        $this->validate($request, [
            'per_page' => ['sometimes', 'required', 'integer'],
        ]);
        $contacts = $this->contacts
        ->with([
            'user:id,name,surname',
            'country:id,name',
            'city:id,name',
            'region:id,name',
            'addressType:id,name',
        ])
        ->company()
        ->orderBy('id', 'desc');
        if ($request->get('user_id'))
            $contacts = $contacts->where('user_id', $request->get('user_id'));
        $contacts = $contacts->paginate($request->get('per_page'));
        return $this->successResponse($contacts);
    }

    public function indexV2(Request $request): JsonResponse{
        $builder = $this->crudRepository->builder()
        ->with([
            'user:id,name,surname',
            'country:id,name',
            'city:id,name',
            'region:id,name',
            'addressType:id,name',
        ])
        ->company()
        ->orderBy('id', 'desc');
        $this->crudRepository->build();
        return $this->successResponse($this->crudRepository->paginate());
    }

    public function show($id){
        $contact = $this->contacts
        ->where('id', $id)
        ->with([
            'user:id,name,surname',
            'country:id,name',
            'city:id,name',
            'region:id,name',
            'addressType:id,name',
        ])
        ->company()
        ->firstOrFail();
        return $this->successResponse($contact);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function create(Request $request): JsonResponse {
        $this->validate($request, $this->getContactRules($request));
        $this->saveContact($request, $this->contacts);
        return $this->successResponse(trans('messages.saved'), 201);
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     * @throws ValidationException
     */
    public function update(Request $request, $id): JsonResponse {
        $this->validate($request, $this->getContactRules($request));
        $contact = $this->contacts->where('id', $id)->company()->firstOrFail(['id']);
        $this->saveContact($request, $contact);
        return $this->successResponse(trans('messages.saved'), 200);
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse {
        $contact = $this->contacts->where('id', $id)->company()->firstOrFail(['id']);
        return $contact->delete()
            ? $this->successResponse(trans('messages.saved'))
            : $this->errorResponse(trans('messages.not_saved'), 400);
    }

    /**
     * @param Request $request
     * @param ContactInformation $contactInformation
     */
    private function saveContact(Request $request, ContactInformation $contactInformation): void  {
        $contactInformation->fill($request->only(
            array_keys($this->getContactRules($request))
        ))->save();
    }

    /**
     * @param Request $request
     * @return array
     */
    private function getContactRules(Request $request): array {
        return [
            'user_id' => [
                'required',
                new IsValidEmployeeRule($request->get('company_id'))
            ],
            'country_id' => 'required|exists:countries,id',
            'city_id' => 'required|exists:cities,id',
            'region_id' => 'required|exists:regions,id',
            'address_type_id' => 'required|exists:address_types,id',
            'address' => 'required|min:3|max:255',
            'number' => 'required|max:50',
            'post_index' => 'required|max:255',
            'expire_date' => 'nullable|date',
            'fax' => 'required|max:255'
        ];
    }

}
