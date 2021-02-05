<?php

namespace Modules\Hr\Http\Controllers\User;

use App\Models\User;
use App\Rules\IsValidEmployeeRule;
use App\Traits\ApiResponse;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\ValidationException;
use Modules\Hr\Entities\UserCertificate;

class UserCertificateController extends Controller
{
    use ApiResponse, ValidatesRequests;

    private $certificate;

    /**
     * UserCertificateController constructor.
     * @param UserCertificate $certificate
     */
    public function __construct(UserCertificate $certificate)
    {
        $this->certificate = $certificate;
    }


    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse {
        $this->validate($request, [
            'per_page' => ['sometimes', 'required', 'integer']
        ]);

        $certificates = $this->certificate
        ->with([
            'user:id,name,surname',
        ])
        ->company()
        ->orderBy('id', 'desc');
        if ($request->get('user_id'))
            $certificates = $certificates->where('user_id', $request->get('user_id'));
        $certificates = $certificates->paginate($request->get('per_page'));

        return $this->successResponse($certificates);
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse{
        return $this->successResponse($this->firstOrFailCertificate($id, ['*']));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function create(Request $request){
        $this->validate($request, $this->getCertificateRules($request));
        $this->saveCertificate($request, $this->certificate);
        return $this->successResponse(trans('messages.saved'), 201);
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     * @throws ValidationException
     */
    public function update(Request $request, $id){
        $this->validate($request, $this->getCertificateRules($request));
        $certificate = $this->firstOrFailCertificate($id);
        $this->saveCertificate($request, $certificate);
        return $this->successResponse(trans('messages.saved'), 200);
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function destroy(Request $request, $id): JsonResponse {
        $certificate = $this->firstOrFailCertificate($id);
        return $certificate->delete()
            ? $this->successResponse(trans('messages.saved'))
            : $this->errorResponse(trans('messages.not_saved'), 400);
    }

    /**
     * @param $id
     * @param array $select
     * @return mixed
     */
    private function firstOrFailCertificate($id, array $select = []){
        if (!count($select))
            $select = ['id'];
        return $this->certificate
            ->where([
                'id' => $id
            ])
            ->with([
                'user:id,name,surname',
            ])
            ->company()
            ->firstOrFail($select);
    }

    /**
     * @param Request $request
     * @param UserCertificate $certificate
     */
    private function saveCertificate(Request $request, UserCertificate $certificate): void {
        $certificate->fill($request->only([
            'user_id',
            'name',
            'speciality',
            'description',
            'training_place',
            'start_date',
            'expire_date',
            'getting_date',
            'number',
            'note',
        ]))->save();
    }


    /**
     * @param Request $request
     * @return array
     */
    private function getCertificateRules(Request $request): array {
        return [
            'user_id' => [
                'required',
                new IsValidEmployeeRule($request->get('company_id'))
            ],
            'name' => 'required|max:50',
            'speciality' => 'required|max:255',
            'description' => 'required|max:255',
            'training_place' => 'required|max:255',
            'start_date' => 'required|date',
            'getting_date' => 'required|date',
            'expire_date' => 'required|date',
            'number' => 'required|numeric',
            'note' => 'nullable|max:255'
        ];
    }
}
