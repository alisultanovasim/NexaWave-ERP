<?php


namespace App\Http\Controllers\Auth;


use App\Http\Controllers\Controller;
use App\Jobs\SendMailCreatePassword;
use App\Models\Company;
use App\Models\User;
use App\Models\UserReset;
use App\Traits\ApiResponse;
use App\Traits\DocumentUploader;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Modules\Hr\Entities\Employee\Contract as EmployeeContract;
use Modules\Hr\Entities\Employee\Employee;
use Modules\Hr\Entities\Employee\UserDetail;
use Modules\Hr\Entities\Positions;
use Modules\Plaza\Entities\OfficeUser;


/**
 * Class UserController
 * @package App\Http\Controllers\Auth
 */
class UserController extends Controller
{
    use ApiResponse;

    public function login(Request $request)
    {
        $this->validate($request, [
            'username' => ['required', 'string'],
            'password' => ['required', 'min:6']
        ]);

        if (!Auth::attempt($request->only('username', 'password'))) {
            return $this->errorResponse(trans('response.invalidLoginOrPassword'));
        }
        $token = Auth::user()->createToken('authToken')->accessToken;
        return $this->dataResponse([
            'token_type' => 'Bearer',
            'access_token' => $token,
            'user' => Auth::user()
        ]);
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws ValidationException
     */
    public function register(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email|unique:users,email',
            'fin' => 'required|unique:user_details,fin',
            'voen' => 'required',
            'password' => 'required|min:6',
            'name' => 'required|min:3|max:50',
            'surname' => 'required|min:3|max:50',
            'gender' => [
                'required',
                Rule::in(['m', 'f'])
            ],
            'company_name' => 'required|min:3'
        ]);

        return DB::transaction(function () use ($request) {
            $user = new User();
            $user->fill([
                'name' => $request->get('name'),
                'username' => $request->get('fin'),
                'email' => $request->get('email'),
                'voen' => $request->get('voen'),
                'password' => Hash::make($request->get('password')),
                'role_id' => User::EMPLOYEE,
            ]);
            $user->save();
            UserDetail::create([
                'user_id' => $user->getKey(),
                'fin' => $request->get('fin'),
                'gender' => $request->get('gender'),
            ]);
            $company = new Company();
            $company->fill([
                'name' => $request->get('company_name')
            ]);
            $company->save();
            $employee = new Employee();
            $employee->fill([
                'company_id' => $company->getKey(),
                'user_id' => $user->getKey()
            ]);
            $employee->save();
            EmployeeContract::create([
                'employee_id' => $employee->getKey(),
                'position_id' => Positions::DIRECTOR,
            ]);
            $request->merge(['username' => $request->get('fin')]);
            return $this->login($request);
        });
    }

    public function destroy($id)
    {
        //
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function changePassword(Request $request)
    {
        $this->validate($request, [
            'current_password' => 'required',
            'password' => 'required|min:6',
            'password_match' => 'required|min:6',
        ]);
        if (!Hash::check($request->get('current_password'), Auth::user()->getAttribute('password')))
            return $this->errorResponse(trans('responses.current_password_is_not_valid'), 400);
        if ($request->get('password') !== $request->get('password_match'))
            return $this->errorMessage(trans('responses.passwords_doesnt_match'), 400);
        $isUpdated = User::where('id', Auth::id())->update(['password' => Hash::make($request->get('password'))]);
        return $isUpdated
            ? $this->successResponse(['success' => trans('responses.password_updated')])
            : $this->errorResponse(trans('responses.password_not_updated'), 500);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function sendResetLinkToEmail(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|exists:users,email'
        ]);
        $userReset = new UserReset();
        $resetCountForToday = $userReset->today()->where('email', $request->get('email'))->count();
        if ($resetCountForToday >= $userReset->getDailyResetCount())
            return $this->errorResponse(trans('responses.extend_daily_reset_limit'), 400);
        $userReset->fill([
            'id' => Str::uuid(),
            'email' => $request->get('email'),
            'user_ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'hash' => $userReset->getRandomHash(),
            'expire_date' => Carbon::now()->addSeconds($userReset->getExpireTime())
        ]);
        $userReset->save();
        //send email here
        return $userReset
            ? $this->successResponse(['success' => trans('responses.reset_link_sent')])
            : $this->errorResponse(trans('responses.reset_link_not_sent'), 500);
    }

    /**
     * @param $hash
     * @return JsonResponse
     */
    public function checkResetHashExists($hash)
    {
        $hash = UserReset::where('hash', $hash)->notExpired()->exists();
        return $hash
            ? $this->successResponse(['exists' => true])
            : $this->errorResponse(trans('responses.not_found'), 404);
    }

    /**
     * @param Request $request
     * @return JsonResponse|mixed
     * @throws ValidationException
     */
    public function reset(Request $request)
    {
        $this->validate($request, [
            'password' => 'required|min:6',
            'password_match' => 'required|min:6',
            'hash' => 'required'
        ]);
        if ($request->get('password') !== $request->get('password_match'))
            return $this->errorResponse(trans('responses.passwords_doesnt_match'), 400);
        $userReset = UserReset::notExpired()->where('hash', $request->get('hash'))->first(['email', 'hash']);
        if (!$userReset)
            return $this->errorResponse(trans('responses.invalid_reset_token'), 400);
        return DB::transaction(function () use ($request, $userReset) {
            User::where('email', $userReset->email)->update([
                'password' => Hash::make($request->get('password'))
            ]);
            UserReset::where('hash', $request->get('hash'))->delete();
            return $this->successResponse(['success' => trans('response.password_reset')]);
        });
    }

    public function index(Request $request)
    {
        $this->validate($request , [
           'paginateCount' => ['sometimes' , 'required' , 'integer'],
            'name'=> ['sometimes' ,'string' , 'max:255'],
        ]);

        $user = User::orderBy('id' , 'desc');

        if ($request->has('name') and $request->get('name'))
            $user->where('name' , $request->get('name'));

        $user = $user->paginate($request->get('paginateCount'));

        return $this->successResponse($user);
    }

    public function store(Request $request)
    {
        $user = self::createUser($request);

         SendMailCreatePassword::dispatch($user);

        return $this->successResponse('ok');
    }

    public function update(Request $request , $id)
    {
        self::updateUser($request , $id);

        return $this->successResponse('ok');
    }

    public function show(Request $request , $id){
        $user = User::findOrFail($id);
        $user->load([
            'employment',
            'employment.contract',
            'employment.contract.position:id,name' ,
            'employment.contract.section:id,name,short_name',
            'employment.contract.sector:id,name,short_name',
            'employment.contract.department:id,name,short_name',
            'employment.contract.currency',
            'employment.company' ,
            'details' ,
            'details.nationality',
            'details.citizen',
            'details.birthdayCity',
            'details.birthdayCountry',
            'details.birthdayRegion',
            'education' ,
            'education.speciality',
            'education.place:id,name',
            'education.level:id,name',
            'education.state:id,name',
            'education.language:id,name'
        ]);
        return $this->successResponse($user);
    }

    public function searchByFin(Request $request ){
        $this->validate($request , [
            'fin' => ['required' , 'string' , 'max:255']
        ]);
        $user = User::whereHas('details' , function ($q) use ($request){
            $q->where('fin' , $request->get('fin'));
        })->first();
        return $this->successResponse($user);
    }

    public static function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'surname' => ['required', 'string', 'max:255'],
            'voen' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'fin' => ['required', 'string', 'max:255'],
            'birthday' => ['nullable', 'date', 'date_format:dd-mm-YYYY'],
            'father_name' => ['nullable', 'string', 'max:255'],
            'gender' => ['required', 'string', 'in:f,m'],
            'nationality_id' => ['nullable', 'integer'],
            'citizen_id' => ['nullable', 'integer'],
            'birthday_country_id' => ['nullable', 'integer'],
            'birthday_city_id' => ['nullable', 'integer'],
            'birthday_region_id' => ['nullable', 'integer'],
            'blood_id' => ['nullable', 'integer'],
            'eye_color_id' => ['nullable', 'integer'],
            'passport_seria' => ['nullable', 'string', 'max:255'],
            'passport_number' => ['nullable', 'string', 'max:255'],
            'passport_from_organ' => ['nullable', 'string', 'max:255'],
            'passport_get_at' => ['nullable', 'date', 'date_format:dd-mm-YYYY'],
            'passport_expire_at' => ['nullable', 'date', 'date_format:dd-mm-YYYY'],
            'social_insurance_no' => ['nullable' , 'string' , 'max:255'],
            'military_status' => ['nullable' , 'string' , 'max:255'],
            'military_start_at' => ['nullable' , 'date', 'date_format:dd-mm-YYYY'],
            'military_end_at' => ['nullable' , 'date', 'date_format:dd-mm-YYYY'],
            'military_state_id' => ['nullable' , 'string' , 'max:255'],
            'military_passport_number' => ['nullable' , 'string' , 'max:255'],
            'military_place' => ['nullable' , 'string' , 'max:255'],
            'driving_license_number' => ['nullable' , 'string' , 'max:255'],
            'driving_license_categories' => ['nullable' , 'string' , 'max:255'],
            'driving_license_organ' => ['nullable' , 'string' , 'max:255'],
            'driving_license_get_at' => ['nullable' , 'date', 'date_format:dd-mm-YYYY'],
            'driving_license_expire_at' => ['nullable' , 'date', 'date_format:dd-mm-YYYY'],
            'foreign_passport_number' => ['nullable' , 'string' , 'max:255'],
            'foreign_passport_organ' => ['nullable' , 'string' , 'max:255'],
            'foreign_passport_get_at' => ['nullable' , 'date', 'date_format:dd-mm-YYYY'],
            'foreign_passport_expire_at' => ['nullable' , 'date', 'date_format:dd-mm-YYYY'],
            'family_status_document_number' => ['nullable' , 'string' , 'max:255'],
            'family_status_state' => ['nullable' , 'string' , 'max:255'],
            'family_status_register_at' => ['nullable' , 'date', 'date_format:dd-mm-YYYY'],
            'avatar' => ['nullable' , 'mimes:png,jpg,jpeg'],
        ];
    }

    public static function updateRules()
    {
        return [
            'name' => ['nullable', 'string', 'max:255'],
            'surname' => ['nullable', 'string', 'max:255'],
            'voen' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
//            'fin' => ['nullable', 'string', 'max:255'],
            'birthday' => ['nullable', 'date', 'date_format:dd-mm-YYYY'],
            'father_name' => ['nullable', 'string', 'max:255'],
            'gender' => ['nullable', 'string', 'in:f,m'],
            'nationality_id' => ['nullable', 'integer'],
            'citizen_id' => ['nullable', 'integer'],
            'birthday_country_id' => ['nullable', 'integer'],
            'birthday_city_id' => ['nullable', 'integer'],
            'birthday_region_id' => ['nullable', 'integer'],
            'blood_id' => ['nullable', 'integer'],
            'eye_color_id' => ['nullable', 'integer'],
            'passport_seria' => ['nullable', 'string', 'max:255'],
            'passport_number' => ['nullable', 'string', 'max:255'],
            'passport_from_organ' => ['nullable', 'string', 'max:255'],
            'passport_get_at' => ['nullable', 'date', 'date_format:dd-mm-YYYY'],
            'passport_expire_at' => ['nullable', 'date', 'date_format:dd-mm-YYYY'],
            'social_insurance_no' => ['nullable' , 'string' , 'max:255'],
            'military_status' => ['nullable' , 'string' , 'max:255'],
            'military_start_at' => ['nullable' , 'date', 'date_format:dd-mm-YYYY'],
            'military_end_at' => ['nullable' , 'date', 'date_format:dd-mm-YYYY'],
            'military_state_id' => ['nullable' , 'string' , 'max:255'],
            'military_passport_number' => ['nullable' , 'string' , 'max:255'],
            'military_place' => ['nullable' , 'string' , 'max:255'],
            'driving_license_number' => ['nullable' , 'string' , 'max:255'],
            'driving_license_categories' => ['nullable' , 'string' , 'max:255'],
            'driving_license_organ' => ['nullable' , 'string' , 'max:255'],
            'driving_license_get_at' => ['nullable' , 'date', 'date_format:dd-mm-YYYY'],
            'driving_license_expire_at' => ['nullable' , 'date', 'date_format:dd-mm-YYYY'],
            'foreign_passport_number' => ['nullable' , 'string' , 'max:255'],
            'foreign_passport_organ' => ['nullable' , 'string' , 'max:255'],
            'foreign_passport_get_at' => ['nullable' , 'date', 'date_format:dd-mm-YYYY'],
            'foreign_passport_expire_at' => ['nullable' , 'date', 'date_format:dd-mm-YYYY'],
            'family_status_document_number' => ['nullable' , 'string' , 'max:255'],
            'family_status_state' => ['nullable' , 'string' , 'max:255'],
            'family_status_register_at' => ['nullable' , 'date', 'date_format:dd-mm-YYYY'],
            'avatar' => ['nullable' , 'mimes:png,jpg,jpeg'],

        ];
    }

    public static function createUser(Request $request)
    {
        Validator::make($request->all(),self::rules())->validate();


        $password = Str::random(9);
        $user = User::create(array_merge(
            $request->only(['name', 'email', 'surname', 'voen']),
            [
                'username' => $request->get('fin'),
                'password' => Hash::make($password),
                'role_id' => User::EMPLOYEE
            ]
        ));

        $data = $request->all();
        if ($request->hasFile('avatar')){
            $name = "{$user->id}.{$request->file('avatar')->getClientOriginalExtension()}";
            $request->file('avatar')->move(public_path('users')  , $name);
            $data['avatar'] = $name;
        }

        $user->details()->create($request->all());


        SendMailCreatePassword::dispatch($user , $password);

        return $user;
    }

    public static function updateUser(Request $request , $id)
    {
        Validator::make($request->all(), self::updateRules())->validate();
        $data = $request->only('name' , 'email' , 'voen' , 'surname');

        if ($data)
            User::where('id' ,$id)->update($data);

        $data = $request->all();
        if ($request->hasFile('avatar')){
            $name = "$id.{$request->file('avatar')->getClientOriginalExtension()}";
            $request->file('avatar')->move(public_path('users')  , $name);
            $data['avatar'] = $name;
        }


        $userDetail = UserDetail::whereHas('user' , function ($q) use ($id) {
            $q->where('id' , $id);
        })->first(['id']);

        $userDetail->fill($request->all());

        return true;
    }



}
