<?php

namespace Modules\Hr\Http\Controllers;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Hr\Entities\NotificationType;

class NotificationTypeController extends Controller
{
    use ApiResponse, ValidatesRequests;

    private $notificationType;

    public function __construct(NotificationType $notificationType)
    {
        $this->notificationType = $notificationType;
    }

    public function index(Request $request){
        $types = $this->notificationType->get(['id', 'name']);
        return $this->successResponse($types);
    }

    public function create(Request $request){
        $this->validate($request, $this->getRules());
        $this->saveNotificationType($request, $this->notificationType);
        return $this->successResponse(trans('messages.saved'), 201);
    }

    public function update(Request $request, $id){
        $this->validate($request, $this->getRules());
        $type = $this->notificationType->where('id', $id)->firstOrFail(['id']);
        $this->saveNotificationType($request, $type);
        return $this->successResponse(trans('messages.saved'));
    }

    public function destroy($id){
        $type = $this->notificationType->where('id', $id)->firstOrFail(['id']);
        return $type->delete()
            ? $this->successResponse(trans('messages.saved'))
            : $this->errorMessage(trans('messages.not_saved'), 400);
    }

    public function saveNotificationType(Request $request, NotificationType $notificationType): NotificationType{
        $notificationType->fill($request->only(['name']));
        $notificationType->save();
        return $notificationType;
    }

    private function getRules(): array {
        return [
            'name' => 'required|max:50|min:3'
        ];
    }
}
