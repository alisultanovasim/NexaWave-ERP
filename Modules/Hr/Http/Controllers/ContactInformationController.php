<?php

namespace Modules\Hr\Http\Controllers;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Hr\Entities\ContactInformation;

class ContactInformationController extends Controller
{
    use ApiResponse, ValidatesRequests;

    private $contacts;

    public function __construct(ContactInformation $contacts)
    {
        $this->contacts = $contacts;
    }

    public function index(Request $request): JsonResponse {
        $this->validate($request, [
            'per_page' => ['sometimes', 'required', 'integer']
        ]);

        $contacts = $this->contacts
        ->with([
            'user:id,name,surname',
        ])
        ->company()
        ->paginate($request->get('per_page'));
        return $this->dataResponse($contacts);
    }

    public function show($id){

    }

    public function create(Request $request)
    {
        $this->validate($request, []);
        $this->saveContact($request, $this->contacts);
        return $this->successResponse(trans('messages.saved'), 201);
    }

    public function update(Request $request, $id){
        $this->validate($request, []);
        $contact = $this->contacts->where('id', $id)->company()->firstOrFail(['id']);
        $this->saveContact($request, $contact);
        return $this->successResponse(trans('messages.saved'), 200);
    }

    /**
     * @param Request $request
     * @param ContactInformation $contactInformation
     */
    private function saveContact(Request $request, ContactInformation $contactInformation): void  {
        $contactInformation->fill($request->only([
            'user_id',
            'country_id',
            'city_id',
            'region_id',
            'address_type_id',
            'address',
            'expire_date',
            'post_index',
            'number',
            'fax'
        ]))->save();
    }

    /**
     * @param Request $request
     * @return array
     */
    private function getContactRules(Request $request): array {
        return [
            'user_id' => [
                'required'
            ]
        ];
    }

}
