<?php


namespace App\Http\Controllers\Auth;


use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserReset;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Modules\Hr\Entities\Employee\Employee;
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
        return $this->successResponse([
            'access_token' => $token,
            'user' => Auth::user()
        ]);
    }

    public function register(Request $request){
        $this->validate($request, [
            'email' => 'required|unique:users,email',
            'fin' => 'required',
            'password' => 'required|min:6',
            'name' => 'required|min:3|max:50',
            'surname' => 'required|min:3|max:50',
            'gender' => [
                'required',
                Rule::in(['m', 'f'])
            ]
        ]);

    }

    public function index(Request $request)
    {
        //
    }

    public function store(Request $request)
    {
        //
    }


    public function destroy($id)
    {
        //
    }

    public function profile(Request $request)
    {
        $user = Auth::user();
        $office = null;

        if ($user->role_id = User::OFFICE)
            $office = OfficeUser::with(['office:id,image'])->where('user_id',$user)->get();

//        $companies = Employee::with(['company' , 'contracts' => function($q){
//            $q->where('is_active', true);
//        }])->active()
//            ->where('user_id', Auth::id())
//            ->get();

        return $this->successResponse([
            'user' => $user,
//            'companies' => $companies,
            'office' => $office
        ]);
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

}
