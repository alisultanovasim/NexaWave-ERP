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
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

/**
 * Class UserController
 * @package App\Http\Controllers\Auth
 */
class UserController extends Controller
{
    use ApiResponse;


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

    /**
     * @param Request $request
     * @return JsonResponse|Response|ResponseFactory
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
            return $this->errorResponseBase(trans('responses.current_password_is_not_valid'), 400);
        if ($request->get('password') !== $request->get('password_match'))
            return $this->errorResponseBase(trans('responses.passwords_doesnt_match'), 400);
        $isUpdated = User::where('id', Auth::id())->update(['password' => Hash::make($request->get('password'))]);
        return $isUpdated
            ? $this->successResponse(['success' => trans('responses.password_updated')])
            : $this->errorResponseBase(trans('responses.password_not_updated'), 500);
    }

    /**
     * @param Request $request
     * @return JsonResponse|Response|ResponseFactory
     * @throws ValidationException
     */
    public function sendResetLinkToEmail(Request $request){
        $this->validate($request, [
            'email' => 'required|exists:users,email'
        ]);
        $userReset = new UserReset();
        $resetCountForToday = $userReset->today()->where('email', $request->get('email'))->count();
        if ($resetCountForToday >= $userReset->getDailyResetCount())
            return $this->errorResponseBase(trans('responses.extend_daily_reset_limit'), 400);
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
            : $this->errorResponseBase(trans('responses.reset_link_not_sent'), 500);
    }

    /**
     * @param $hash
     * @return JsonResponse|Response|ResponseFactory
     */
    public function checkResetHashExists($hash){
        $hash = UserReset::where('hash', $hash)->notExpired()->exists();
        return $hash
            ? $this->successResponse(['exists' => true])
            : $this->errorResponseBase(trans('responses.not_found'), 404);
    }

    /**
     * @param Request $request
     * @return Response|ResponseFactory|mixed
     * @throws ValidationException
     * @throws \Throwable
     */
    public function reset(Request $request){
        $this->validate($request, [
            'password' => 'required|min:6',
            'password_match' =>  'required|min:6',
            'hash' => 'required'
        ]);
        if ($request->get('password') !== $request->get('password_match'))
            return $this->errorResponseBase(trans('responses.passwords_doesnt_match'), 400);
        $userReset = UserReset::notExpired()->where('hash', $request->get('hash'))->first(['email', 'hash']);
        if (!$userReset)
            return $this->errorResponseBase(trans('responses.invalid_reset_token'), 400);
        return DB::transaction(function () use ($request, $userReset){
            User::where('email', $userReset->email)->update([
                'password' => Hash::make($request->get('password'))
            ]);
            UserReset::where('hash', $request->get('hash'))->delete();
            return $this->successResponse(['success' => trans('response.password_reset')]);
        });
    }

}
