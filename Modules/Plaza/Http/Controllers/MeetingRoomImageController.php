<?php


namespace Modules\Plaza\Http\Controllers;


use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Plaza\Entities\MeetingRoomImage;
use Modules\Plaza\Entities\MeetingRooms;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Controller;

class MeetingRoomImageController extends Controller
{
    use ApiResponse, ValidatesRequests;

    public function index(Request $request)
    {
        $this->validate($request, [
            'company_id' => ['required', 'integer'],

            'room_id' => ['required', 'integer'],
            'image_limit' => ['sometimes', 'required', 'integer']
        ]);
        try {
            $images = MeetingRoomImage::whereHas('room', function ($q) use ($request) {
                $q->where('company_id', $request->company_id)
                    ->where('meeting_room_id', $request->room_id);
            });

            if ($request->has('image_limit')) $images = $images->take($request->image_limit);

            else $images = $images->get();

            return $this->successResponse($images);

        } catch (\Exception $e) {
            return $this->errorResponse(trans('apiResponse.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'company_id' => ['required', 'integer'],

            'room_id' => ['required', 'integer'],
            'images' => ['required', 'array'],
            'images.*' => ['required', 'mimes:png,jpg,jpeg,svg'],
        ]);
        try {
            $check = MeetingRooms::where('id', $request->id)
                ->where('company_id', $request->company_id)->exists();
            $images = [];
            foreach ($request->images as $image)
                $images[] = [
                    'meeting_room_id' => $request->room_id,
                    'url' => $this->uploadImage($request->company_id, $image)
                ];
            MeetingRoomImage::insert($images);
            if (!$check) $this->errorResponse(trans('apiResponse.RoomNotFound'));
            return $this->successResponse('ok');
        } catch (\Exception $e) {
            return $this->errorResponse(trans('apiResponse.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function uploadImage($company_id, $file, $str = 'rooms')
    {
        if ($file instanceof UploadedFile) {
            return $file->storePubliclyAs("documents/$company_id/$str");
        }

        return null;
    }

    public function delete(Request $request, $id)
    {
        $this->validate($request, [
            'company_id' => ['required', 'integer'],

            'room_id' => ['required', 'integer'],
        ]);
        try {
            $check = MeetingRooms::where('id', $request->id)
                ->where('company_id', $request->company_id)->exists();
            if (!$check) $this->errorResponse(trans('apiResponse.RoomNotFound'));
            $check = MeetingRoomImage::where('id', $id)->where('meeting_room_id', $request->room_id)->delete();
            if (!$check) return $this->successResponse(trans('apiResponse.alreadyDeleted'));
            return $this->successResponse('ok');
        } catch (\Exception $e) {
            return $this->errorResponse(trans('apiResponse.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
