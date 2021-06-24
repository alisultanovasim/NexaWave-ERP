<?php


namespace Modules\Plaza\Http\Controllers;


use App\Mail\ReservationEmail;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Exception;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Modules\Plaza\Entities\Meeting;
use Modules\Plaza\Entities\MeetingRoomImage;
use Modules\Plaza\Entities\MeetingRooms;
use Modules\Plaza\Entities\Office;
use Modules\Plaza\Entities\RoomType;

/**
 * Class MeetingRoomController
 * @package Modules\Plaza\Http\Controllers
 */
class MeetingRoomController extends Controller
{
    use ApiResponse, ValidatesRequests;

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function getAllRooms(Request $request)
    {
        $this->validate($request, [
            'company_id' => 'required|integer',
            'with_images' => ['sometimes', 'required', 'boolean']
        ]);
        try {
            $rooms = MeetingRooms::with(['type'])
//                ->where('company_id', $request->company_id)
                ->orderBy('id', 'desc');

            if ($request->with_images) $rooms->with(['images']);
            else  $rooms->with(['images' => function ($q) {
                $q->take(1);
            }]);

            $rooms = $rooms->get();
            return $this->successResponse($rooms);
        } catch (Exception $e) {
            return $this->errorResponse('apiResponse.tryLater', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function storeRooms(Request $request)
    {
        $this->validate($request, [
            'company_id' => 'required|integer',

            'size' => 'required|numeric',
            'description' => 'sometimes|nullable',
            'name' => 'required|string|max:255',

            'images' => 'sometimes|required|array',
            'images.*' => 'required_with:image|mimes:png,jpg,jpeg,svg',

            'person_count' => 'sometimes|required|integer',
            'status' => 'sometimes|required|in:0,1',
            'type.*' => 'sometimes|array',
            'type.*.name' => 'required_with:type|max:255',
            'type.*.max_person_count' => 'required_with:type|integer'
        ]);
        try {
            DB::beginTransaction();

            $arr = $request->only('company_id', 'size', 'description', 'name', 'person_count', 'status', 'schema');

            $arr['created_at'] = Carbon::now('Asia/Baku')->toDateTimeString();

            $room = MeetingRooms::create($arr);

            if ($request->has('images')) {
                $images = [];
                foreach ($request->images as $image) {
                    $images[] = [
                        'url' => $this->uploadImage($request->company_id, $image),
                        'meeting_room_id' => $room->id,
                    ];
                }
                MeetingRoomImage::insert($images);
            }
            if ($request->has('type')) {
                $types = [];
                foreach ($request->type as $type)
                    $types[] = [
                        'room_id' => $room->id,
                        'name' => $type['name'],
                        'max_person_count' => isset($type['max_person_count']) ? $type['max_person_count'] : null
                    ];
                RoomType::insert($types);
            }

            DB::commit();
            return $this->successResponse('OK');
        } catch (Exception $e) {
            return $this->errorResponse('apiResponse.tryLater', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param $company_id
     * @param $file
     * @param string $str
     * @return false|string|null
     */
    public function uploadImage($company_id, $file, $str = 'rooms')
    {
        if ($file instanceof UploadedFile) {
            return $file->store("documents/$company_id/$str");
        }

        return null;
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     * @throws ValidationException
     */
    public function showRooms(Request $request, $id)
    {
        $this->validate($request, [
            'company_id' => 'required|integer',

            'from' => 'sometimes|date|date_format:Y-m-d H:i:s',
            'to' => 'sometimes|date|date_format:Y-m-d H:i:s',
            'time_zone' => 'sometimes|required',
            'meeting_room' => 'sometimes|required|integer',
            'with_images' => ['sometimes', 'required', 'boolean']
        ]);
        try {
            $room = MeetingRooms::with(['meetings' => function ($q) use ($request) {
                $timezone = $request->timezone ?? 'Asia/Baku';
                $from = Carbon::now($timezone)->toDateTimeString();
                if ($request->has('from'))
                    $from = $request->from;
                $q->where(function ($query) use ($from) {
                    $query->where('start_at', '>=', $from)->orWhere('finish_at', '>=', $from);
                });
                if ($request->has('to'))
                    $q->where(function ($query) use ($request) {
                        $query->where('start_at', '<=', $request->to)->orWhere('finish_at', '<=', $request->to);
                    });
            }, 'type'])->where('id', $id)->where('company_id', $request->company_id);
            if ($request->with_images) $room = $room->with(['images']);
            $room = $room->first();
            return $this->successResponse($room);
        } catch (Exception $e) {
            return $this->errorResponse('apiResponse.tryLater', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     * @throws ValidationException
     */
    public function updateRoom(Request $request, $id)
    {
        $this->validate($request, [
            'company_id' => 'required|integer',

            'size' => 'sometimes|required|numeric',
            'schema' => 'sometimes|required|mimes:png,jpg,jpeg,svg',
            'description' => 'sometimes',
            'name' => 'sometimes|required|string|max:255',
            'person_count' => 'sometimes|required|integer',
            'status' => 'sometimes|required|in:0,1',
            'type.*' => 'sometimes|array',
            'type.*.name' => 'sometimes|required|max:255',
            'type.*.max_person_count' => 'sometimes|required|integer'
        ]);
        $arr = $request->only('size', 'description', 'name', 'person_count', 'status');
        if (!$arr and !$request->has('type') and !$request->has('schema')) return $this->errorResponse(trans('apiResponse.nothing'));

        try {
            $check = MeetingRooms::where('id', $id)
                ->where('company_id', $request->company_id)
                ->exists();
            if (!$check) return $this->errorResponse(trans('apiResponse.RoomNotFound'));

            if ($request->has('schema'))
                $arr['schema'] = $this->uploadImage($request->company_id, $request->schema);

            if ($arr)
                MeetingRooms::where('id', $id)
                    ->update($arr);


            RoomType::where('room_id', $id)->delete();

            if ($request->has('type')) {
                $types = [];
                foreach ($request->type as $type)
                    $types[] = [
                        'room_id' => $id,
                        'name' => $type['name'],
                        'max_person_count' => $type['max_person_count']
                    ];
                RoomType::insert($types);
            }
            return $this->successResponse('OK');
        } catch (Exception $e) {
            return $this->errorResponse('apiResponse.tryLater', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     * @throws ValidationException
     */
    public function deleteRoom(Request $request, $id)
    {
        $this->validate($request, [
            'company_id' => 'required|integer',
            'user_id' => 'required|integer'
        ]);
        try {
            $check = MeetingRooms::where('company_id', $request->company_id)
                ->where('id', $id)
                ->delete();
            if (!$check) return $this->errorResponse(trans('apiResponse.RoomNotFound'));
            return $this->successResponse('ok');
        } catch (\Illuminate\Database\QueryException $e) {
            return $this->errorResponse('apiResponse.afterDomeTimesYoCantDelete', Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (Exception $e) {
            return $this->errorResponse('apiResponse.tryLater', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function index(Request $request)
    {
        $this->validate($request, [
            'office_id' => 'sometimes|required|integer',
            'company_id' => 'required|integer',
            'from' => 'sometimes|date|date_format:Y-m-d H:i:s',
            'to' => 'sometimes|date|date_format:Y-m-d H:i:s',
            'time_zone' => 'sometimes|required',
            'meeting_room' => 'sometimes|required|integer',
        ]);

        try {
            $timezone = $request->timezone ?? 'Asia/Baku';

            $meeting = Meeting::with('room:id,name,status', 'office:id,name')->where('company_id', $request->company_id);

            if ($request->has('office_id')) {
                if ($request->office_id == 0) $meeting->whereNull('office_id');
                else
                    $meeting->where('office_id', $request->office_id);
            }


            if ($request->has('meeting_room')) $meeting->where('meeting_room', $request->meeting_room);

            if ($request->has('status')) $meeting->where('status', $request->status);

            $from = Carbon::now()->timezone($timezone)->toDateString();

            if ($request->has('from')) {
                $start = Carbon::createFromFormat('Y-m-d H:i:s', $request->from, $timezone);

                $from = $request->from;
            }

            $meeting->where(function ($query) use ($from) {
                $query->where('start_at', ">=", $from)->orWhere('finish_at', ">=", $from);
            });

            if ($request->has('to')) {
                $end = Carbon::createFromFormat('Y-m-d H:i:s', $request->to, $timezone);
                if ($start > $end) return $this->errorResponse(trans('apiResponse.timeError2'));
                $meeting->where(function ($query) use ($request) {
                    $query->where('finish_at', "<=", $request->to)->orWhere('start_at', "<=", $request->to);
                });
            }
            $meeting = $meeting->orderBy('start_at', 'ASC')->get();

            return $this->successResponse($meeting);

        } catch (Exception $exception) {
            return $this->errorResponse(trans('apiResponse.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);

        }

    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     * @throws ValidationException
     */
    public function show(Request $request, $id)
    {
        $this->validate($request, [
            'office_id' => 'sometimes|required|integer',
            'company_id' => 'required|integer',
        ]);

        try {
            $meeting = Meeting::with('room:id,name,status', 'office:id,name')
                ->where('company_id', $request->company_id)
                ->where('id', $id)->first();
            return $this->successResponse($meeting);
        } catch (Exception $exception) {
            return $this->errorResponse(trans('apiResponse.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);

        }

    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'office_id' => 'sometimes|required|integer',
            'company_id' => 'required|integer',
            'start_at' => 'required|date|date_format:Y-m-d H:i:s',
            'finish_at' => 'required|date|date_format:Y-m-d H:i:s',
            'event_name' => 'required|min:2|max:255',
            'description' => 'sometimes|nullable',
            'time_zone' => 'sometimes|required',
            'meeting_room' => 'required|integer',
        ]);
        $company_id = $request->company_id;

        try {

            $timezone = $request->timezone ?? 'Asia/Baku';
            $start = Carbon::createFromFormat('Y-m-d H:i:s', $request->start_at, $timezone);
            $end = Carbon::createFromFormat('Y-m-d H:i:s', $request->finish_at, $timezone);
            $now = Carbon::now()->timezone($timezone);
            if ($start < $now) return $this->errorResponse(trans('apiResponse.reservationTimeError'));
            if ($start > $end) return $this->errorResponse(trans('apiResponse.reservationTimeError'));
            if ($start == $end) return $this->errorResponse(trans('apiResponse.timeError'));

            if ($request->has('office_id')) {
                $office = Office::where([
                    'id' => $request->office_id,
                    'company_id' => $company_id
                ])->first();
                if (!$office) return $this->errorResponse(trans('apiResponse.unProcess'));
            }
            $meeting_rooms = MeetingRooms::where([
                'id' => $request->meeting_room,
                'company_id' => $company_id
            ])->first();
            if (!$meeting_rooms) return $this->errorResponse(trans('apiResponse.MeetingRoomNotFound'));
            if ($meeting_rooms->status != 1) return $this->errorResponse(['meeting_room' => trans('apiResponse.roomIsNotActive')]);

            $check = Meeting::where('company_id', $company_id)
                ->where('meeting_room', $request->meeting_room)
                ->where('status', config('plaza.reservation.status.wait'))
                ->where(function ($query) use ($start, $end) {
                    $query->where([
                        ['start_at', "<", $start],
                        ['finish_at', ">", $start]
                    ])->orWhere([
                        ['start_at', "<", $end],
                        ['finish_at', ">", $end]
                    ])->orWhere([
                        ['start_at', ">=", $start],
                        ['finish_at', "<=", $end]
                    ]);
                })->exists();
            if ($check) return $this->errorResponse(trans('apiResponse.reservationTimeError'));
           Meeting::create($request->only('company_id', 'start_at', 'finish_at', 'office_id', 'finish_at', 'event_name', 'description', 'meeting_room'));
           //Send email to plaza
//           dispatch(new ReservationEmail("isa.qurbanov996@gmail.com",$office->name,$start,$meeting_rooms->name));
            Mail::to("i.babirli@outlook.com")->send(new ReservationEmail("i.babirli@outlook.com",$office->name,$start,$meeting_rooms->name));
            return $this->successResponse('OK');
        } catch (Exception $e) {
            return $this->errorResponse(trans('apiResponse.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     * @throws ValidationException
     */
    public function updateForPlaza(Request $request, $id)
    {
        $this->validate($request, [
            'office_id' => 'sometimes|required|integer',
            'company_id' => 'required|integer',
            'status' => ['sometimes', 'required', 'in:0,1,2,3'],
            'price' => 'sometimes|required|numeric'
        ]);
        try {
            $meeting = Meeting::where([
                ['id', $id],
                ['company_id', $request->company_id],
            ])->first();
            if (!$meeting) return $this->errorResponse(trans('apiResponse.MeetingNotFound'));
            Meeting::where('id', $id)->update($request->only('plaza_note', 'status', 'price'));
            return $this->successResponse('OK');
        } catch (Exception $e) {
            return $this->errorResponse(trans('apiResponse.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     * @throws ValidationException
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'office_id' => 'sometimes|required|integer',
            'company_id' => 'required|integer',
            'start_at' => 'sometimes|required|date|date_format:Y-m-d H:i:s',
            'finish_at' => 'sometimes|required|date|date_format:Y-m-d H:i:s',
            'event_name' => 'sometimes|required|min:2|max:255',
            'description' => 'sometimes',
            'time_zone' => 'sometimes|required',
            'meeting_room' => 'sometimes|required|integer',
            'status' => ['sometimes', 'required', 'in:2,3']
        ]);
        $arr = $request->only('office_note', 'start_at', 'finish_at', 'event_name', 'description', 'status');
        if (!$arr) return $this->errorResponse(trans('apiResponse.nothing'));
        try {
            $meeting = Meeting::where([
                ['id', $id],
                ['company_id', $request->company_id]
            ])->first();

            if (!$meeting) return $this->errorResponse(trans('apiResponse.unProcess'));

            if ($request->has('office_id') and $meeting->office_id != $request->office_id) return $this->errorResponse(trans('apiResponse.notYourMeeting'));

            if ($meeting->status === config('plaza.reservation.status.rejected')) return $this->errorResponse(trans('apiResponse.alreadyRejected'));

            if ($meeting->status === config('plaza.reservation.status.accepted')) return $this->errorResponse(trans('apiResponse.onlyAccepted'));

            if ($meeting->finish_at < Carbon::now()->timezone('Asia/Baku')->addMinutes(5)->toDateTimeString()) return $this->errorResponse(trans('apiResponse.alreadyCantUpdate'));


            if ($request->has('meeting_room')) {
                $check = MeetingRooms::where([
                    'id' => $request->meeting_room,
                    'company_id' => $request->company_id
                ])->exists();
                if (!$check) return $this->errorResponse(trans('apiResponse.MeetingRoomNotFound'));
                if ($check->status != 1) return $this->errorResponse(['meeting_room' => trans('apiResponse.roomIsNotActive')]);
            }

            $timezone = $request->timezone ?? 'Asia/Baku';

            if ($request->has('start_at')) {
                $start = Carbon::createFromFormat('Y-m-d H:i:s', $request->start_at, $timezone)->toDateTimeString();
                $now = Carbon::now()->timezone($timezone);
                if ($start < $now) return $this->errorResponse(trans('apiResponse.reservationTimeError'));
                $end = $meeting->finish_at;
                if ($request->has('finish_at')) {
                    $end = Carbon::createFromFormat('Y-m-d H:i:s', $request->finish_at, $timezone);
                }
                if ($start > $end) return $this->errorResponse(trans('apiResponse.reservationTimeError'));
                $check = Meeting::where('company_id', '>=', $request->company_id)
                    ->where(function ($query) use ($start, $end) {
                        $query->where([
                            ['start_at', "<", $start],
                            ['finish_at', ">", $start]
                        ])->orWhere([
                            ['start_at', "<", $end],
                            ['finish_at', ">", $end]
                        ])->orWhere([
                            ['start_at', ">=", $start],
                            ['finish_at', "<=", $end]
                        ]);
                    })
                    ->where('id', "!=", $meeting->id)
                    ->exists();
                if ($check) return $this->errorResponse(trans('apiResponse.reservationTimeError'));
            } else {
                if ($request->has('finish_at')) {
                    $end = Carbon::createFromFormat('Y-m-d H:i:s', $request->finish_at, $timezone);
                    if ($meeting->start_at > $end) return $this->errorResponse(trans('apiResponse.reservationTimeError'));
                    $check = Meeting::where('company_id', '>=', $request->company_id)
                        ->where('start_at', "<", $request->finish_at)
                        ->where('start_at', ">", $meeting->finish_at)
                        ->where('id', "!=", $meeting->id)
                        ->exists();
                    if ($check) return $this->errorResponse(trans('apiResponse.reservationTimeError'));
                }
            }

            Meeting::where('id', $id)->update($arr);
            return $this->successResponse('OK');

        } catch (Exception $e) {
            return $this->errorResponse(trans('apiResponse.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     * @throws ValidationException
     */
    public function delete(Request $request, $id)
    {
        $this->validate($request, [
            'office_id' => 'required|integer',
            'company_id' => 'required|integer',
        ]);
        try {
            $check = Meeting::where([
                ['id', $id],
                ['company_id', $request->company_id],
                ['office_id', $request->office_id],
                //['start_at' ,">=" , Carbon::now()->timezone('Asia/Baku')->addMinutes(5)->toDateTimeString()]
            ])->delete();
            if (!$check) return $this->errorResponse(trans('apiResponse.unProcess'));

            return $this->successResponse('OK');

        } catch (Exception $exception) {
            return $this->errorResponse(trans('apiResponse.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }
}
