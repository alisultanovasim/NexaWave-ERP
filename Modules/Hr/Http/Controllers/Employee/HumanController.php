<?php

namespace Modules\Hr\Http\Controllers\Employee;

use Illuminate\Support\Facades\Validator;
use Modules\Hr\Entities\Employee\Human;
use App\Traits\ApiResponse;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class HumanController extends Controller
{
    use ApiResponse;

    public function search(Request $request)
    {
        $this->validate($request, [
            'fin' => ['required', 'string']
        ]);
        try {
            $human = Human::where('fin', $request->get('fin'))
                ->first();
            return $this->dataResponse($human);
        } catch (\Exception $exception) {
            return $this->errorResponse(trans('response.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function index(Request $request)
    {
        $this->validate($request, [
            //todo filter
            'paginateCount' => ['sometimes', 'required', 'integer']
        ]);
        $humans = Human::paginate($request->get('paginateCount'));
        return $this->successResponse($humans);
    }

    public function show(Request $request, $id)
    {
        //todo return work places
        $humans = Human::with('employment')->findOrFail($id);
        return $this->successResponse($humans);
    }

    public function store(Request $request)
    {
        $this->validateStore($request->all());
        try {
            $human = Human::create($request->all());
        } catch (QueryException  $exception) {
            if ($exception->errorInfo[1] == 1062) {
                return $this->errorResponse(['fin' => trans('response.alreadyExists')], 422);
            }
            return $this->errorResponse(trans('response.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Exception $exception) {
            return $this->errorResponse(trans('response.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return $this->dataResponse(['human' => [
            'id' => $human->id,
        ]]);
    }

    public function update(Request $request, $id)
    {
        $rules = [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'surname' => ['sometimes', 'required', 'string', 'max:255'],
            'father_name' => ['sometimes', 'required', 'string', 'max:255'],
            'birthday' => ['sometimes', 'sometimes', 'date', 'date_format:d-m-Y'],
            'nationality_id' => ['sometimes', 'sometimes', 'integer'],
            'citizen_id' => ['sometimes', 'sometimes', 'integer'],
            'birthday_country_id' => ['sometimes', 'sometimes', 'integer'],
            'birthday_city_id' => ['sometimes', 'sometimes', 'integer'],
            'birthday_region_id' => ['sometimes', 'sometimes', 'integer'],
            'blood_id' => ['sometimes', 'sometimes', 'integer'],
            'fin' => ['sometimes', 'required', 'string'],
            'gender' => ['sometimes', 'required', 'in:m,f'],
            'passport_seria' => ['sometimes', 'string'],
            'passport_number' => ['sometimes', 'string'],
            'passport_from_organ' => ['sometimes', 'string'],
            'passport_get_at' => ['sometimes', 'date', 'date_format:d-m-Y'],
            'passport_expire_at' =>['sometimes', 'date', 'date_format:d-m-Y'],
        ];
        $this->validate($request, $rules);
        $data = $request->only(array_keys($rules));
        if (!$data) return $this->errorResponse(trans('response.nothing'));
        Human::where('id', $id)->update($data);
        return $this->successResponse('ok');
    }

    public function delete(Request $request, $id)
    {

        Human::where('id', $id)->delete();
        return $this->successResponse('ok');
    }

    public function validateStore($data)
    {
        $validator = Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
            'surname' => ['required', 'string', 'max:255'],
            'father_name' => ['required', 'string', 'max:255'],
            'birthday' => ['sometimes', 'date', 'date_format:d-m-Y'],
            'nationality_id' => ['sometimes', 'integer'],
            'citizen_id' => ['sometimes', 'integer'],
            'birthday_country_id' => ['sometimes', 'integer'],
            'birthday_city_id' => ['sometimes', 'integer'],
            'birthday_region_id' => ['sometimes', 'integer'],
            'blood_id' => ['sometimes', 'integer'],
            'fin' => ['required', 'string'],
            'gender' => ['required', 'in:m,f'],
            'passport_seria' => ['sometimes', 'string'],
            'passport_number' => ['sometimes', 'string'],
            'passport_from_organ' => ['sometimes', 'string'],
            'passport_get_at' => ['sometimes', 'date', 'date_format:d-m-Y'],
            'passport_expire_at' => ['sometimes', 'date', 'date_format:d-m-Y'],
        ]);

        $validator->validate();

    }
}
