<?php

namespace App\Http\Controllers;

use App\Notification\Models\NotificationToken;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class NotificationController extends Controller
{

    private $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService=$notificationService;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException|Throwable
     */
    public function registerToken(Request $request): JsonResponse
    {
        $this->validate($request, [
            'fcm_token' => "required|string"
        ]);

        DB::transaction(function () use ($request) {
            $notificationToken = NotificationToken::where("fcm_token", $request->input("fcm_token"))->first();
            if ($notificationToken) {
                $this->notificationService->set($notificationToken);
            }
            $this->notificationService->registerFCMToken([
                'user_id' => auth()->id(),
                'fcm_token' => $request->input("fcm_token"),
                'user_agent' => $request->userAgent(),
                'user_ip' => $request->ip()
            ]);
        });
        return $this->successResponse([]);
    }
}
