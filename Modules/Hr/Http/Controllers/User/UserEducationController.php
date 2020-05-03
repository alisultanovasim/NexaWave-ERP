<?php

namespace Modules\Hr\Http\Controllers\User;

use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Hr\Entities\EducationState;
use Modules\Hr\Entities\User\UserEducation;

class UserEducationController extends Controller
{
    use ValidatesRequests, ApiResponse;

    public function index(Request $request)
    {
        $this->validate($request, [
            'per_page' => ['sometimes', 'required', 'integer']
        ]);
        $edu = UserEducation::with([
            'user:id,name,surname',
            'speciality',
            'place:id,name',
            'level:id,name',
            'state:id,name',
            'language:id,name'
        ])
            ->company($request->get('company_id'))
            ->orderBy('id','desc')
            ->paginate($request->get('per_page'));
        return $this->successResponse($edu);
    }

    public function show(Request $request, $id)
    {
        $this->validate($request, [
            'per_page' => ['sometimes', 'required', 'integer']
        ]);
        $education = UserEducation::with([
            'user:id,name,surname',
            'speciality',
            'place:id,name',
            'level:id,name',
            'state:id,name',
            'language:id,name'
        ])
            ->company()
            ->where('id', $id)
            ->first();
        if (!$education)
            return $this->errorResponse(trans('response.EducationNotFound'), 404);

        return $this->successResponse($education);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'user_id' => ['required', 'integer'],
            'language_id' => ['nullable', 'integer'], //+
            'education_specialty_id' => ['required', 'integer'], //+
            'education_place_id' => ['required', 'integer'], //+
            'education_state_id' => ['required', 'integer'],//+
            'education_level_id' => ['required' , 'integer'], //+
            'faculty_id'=>['required' , 'integer'],
            'entrance_date' => ['required' , 'date' , 'date_format:Y-m-d'],
            'graduation_date' => ['required' , 'date' , 'date_format:Y-m-d'],
            'description' => ['nullable' , 'string']
        ]);
        $exists = User::query()
            ->company()
            ->where('id' , $request->get('user_id'))
            ->exists();
        if (!$exists) return $this->errorResponse(trans('response.userNotFound') , 404);

        UserEducation::create($request->only([
            'user_id',
            'education_specialty_id',
            'education_place_id',
            'education_state_id',
            'education_level_id',
            'faculty_id',
            'entrance_date',
            'graduation_date',
            'description',
            'language_id'
        ]));
        return $this->successResponse('ok');
    }

    public function update(Request $request , $id)
    {
        $this->validate($request, [
            'language_id' => ['nullable', 'integer'],
            'education_specialty_id' => ['nullable', 'integer'],
            'education_place_id' => ['nullable', 'integer'],
            'education_state_id' => ['nullable', 'integer'],
            'education_level_id' => ['nullable' , 'integer'],
            'faculty_id'=>['nullable' , 'integer'],
            'entrance_date' => ['nullable' , 'date' , 'date_format:Y-m-d'],
            'graduation_date' => ['nullable' , 'date' , 'date_format:Y-m-d'],
            'description' => ['nullable' , 'string']
        ]);

        $check = UserEducation::company()
            ->where('id' , $id)
            ->first(['id']);
        if (!$check) return $this->errorResponse(trans('response.userNotFound') ,404);
        UserEducation::where('id' , $id)
            ->update($request->only([
                'education_specialty_id',
                'education_place_id',
                'education_state_id',
                'education_level_id',
                'faculty_id',
                'entrance_date',
                'graduation_date',
                'description',
            ]));
        return $this->successResponse('ok');
    }

    public function delete($id){
        $check = UserEducation::query()
            ->company()
            ->where('id' , $id)
            ->first(['id']);
        if (!$check) return $this->errorResponse(trans('response.userNotFound') ,404);

        UserEducation::where('id' , $id)
            ->delete();

        return $this->successResponse('ok');
    }
}
