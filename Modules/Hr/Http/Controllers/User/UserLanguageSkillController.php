<?php


namespace Modules\Hr\Http\Controllers\User;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Esd\Entities\User;
use Modules\Hr\Entities\User\UserLanguageSkill;

class UserLanguageSkillController extends Controller
{
    use ValidatesRequests, ApiResponse;

    public function index(Request $request)
    {
        $this->validate($request, [
            'per_page' => ['sometimes', 'required', 'integer']
        ]);
        UserLanguageSkill::with([
            'user:id,name,surname',
            'language',
            'listening',
            'reading',
            'comprehension',
            'writing',
        ])->company($request->get('company_id'))->paginate($request->get('per_page'));
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
        //check if user able to
        $exists = User::whereHas('employment' , function ($q) use ($request){
            $q->where('company_id' , $request->get('company_id'));
        })
            ->where('id' , $request->get('user_id'))
            ->exists();
        if (!$exists) return $this->errorResponse(trans('response.userNotFound') , 404);

        UserLanguageSkill::create($request->only($this->inserted()));
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

        $check = UserLanguageSkill::company($request->get('company_id'))
            ->where('id' , $id)
            ->first(['id']);
        if (!$check) return $this->errorResponse(trans('response.userNotFound') ,404);

        UserLanguageSkill::where('id' , $id)
            ->update($request->only($this->inserted()));
        return $this->successResponse('ok');
    }

    public function delete(Request $request , $id){
        $check = UserLanguageSkill::company($request->get('company_id'))
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
