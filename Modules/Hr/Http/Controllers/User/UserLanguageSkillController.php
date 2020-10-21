<?php


namespace Modules\Hr\Http\Controllers\User;

use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Hr\Entities\User\UserLanguageSkill;

class UserLanguageSkillController extends Controller
{
    use ValidatesRequests, ApiResponse;

    public function index(Request $request)
    {
        $this->validate($request, [
            'per_page' => ['sometimes', 'required', 'integer']
        ]);
        $skills = UserLanguageSkill::with([
            'user:id,name,surname',
            'language:id,name',
            'listening:id,name',
            'reading:id,name',
            'comprehension:id,name',
            'writing:id,name',
        ])->company();
        if ($request->get('user_id'))
            $skills = $skills->where('user_id', $request->get('user_id'));
        $skills = $skills->paginate($request->get('per_page'));
        return $this->successResponse($skills);
    }

    public function show(Request $request, $id)
    {
        $education = UserLanguageSkill::with([
            'user:id,name,surname',
            'speciality',
            'place',
            'level',
            'state',
            'language'
        ])->company($request->get('company_id'))->where('id', $id)->first();
        if (!$education)
            return $this->errorResponse(trans('response.EducationNotFound'), 404);
        return $this->successResponse($education);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'user_id' => ['required', 'integer'],
            'language_id' => ['required' , 'integer'],//+
            'listening' => ['required' , 'integer'],
            'reading' => ['required' , 'integer'],
            'comprehension' => ['required' , 'integer'],
            'writing' => ['required' , 'integer'],
        ]);
        $exists = User::company()
            ->where('id' , $request->get('user_id'))
            ->exists();
        if (!$exists) return $this->errorResponse(trans('response.userNotFound') , 404);

        UserLanguageSkill::create($request->only($this->inserted()) + ['user_id' => $request->get('user_id')]);
        return $this->successResponse('ok');
    }

    public function update(Request $request , $id)
    {
        $this->validate($request, [
            'language_id' => ['nullable' , 'integer'],
            'listening' => ['nullable' , 'integer'],
            'reading' => ['nullable' , 'integer'],
            'comprehension' => ['nullable' , 'integer'],
            'writing' => ['nullable' , 'integer'],
        ]);

        $check = UserLanguageSkill::company()
            ->where('id' , $id)
            ->first(['id']);
        if (!$check) return $this->errorResponse(trans('response.userNotFound') ,404);

        UserLanguageSkill::where('id' , $id)
            ->update($request->only($this->inserted()));
        return $this->successResponse('ok');
    }

    public function delete(Request $request , $id){
        $check = UserLanguageSkill::company()
            ->where('id' , $id)
            ->first(['id']);
        if (!$check) return $this->errorResponse(trans('response.userNotFound') ,404);

        UserLanguageSkill::where('id' , $id)
            ->delete();

        return $this->successResponse('ok');
    }

    protected function inserted(){
        return [
            'language_id',
            'reading',
            'listening',
            'writing',
            'comprehension',
        ];
    }
}
