<?php


namespace Modules\Plaza\Http\Controllers;


use App\Traits\ApiResponse;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ReservedRoomsController extends Controller
{
    use ApiResponse, ValidatesRequests;

    public function rooms(Request $request)
    {
        $this->validate($request, [
            'company_id' => 'required',
            'per_page' => 'sometimes|integer'
        ]);

        $per_page = $request->per_page ?? 10;
        $reserved_rooms = \DB::table('meeting_room_reservations')
            ->where('meeting_room_reservations.company_id' , $request->company_id)
            ->select('offices.name as office_name', 'companies.name as company_name', 'meeting_rooms.name as room_name', 'start_at as start_date', 'finish_at as end_date', 'meeting_room_reservations.status', 'price')
            ->join('companies', 'companies.id', '=', 'meeting_room_reservations.company_id')
            ->join('meeting_rooms', 'meeting_rooms.id', '=', 'meeting_room_reservations.meeting_room')
            ->join('offices', 'offices.company_id', '=', 'meeting_room_reservations.company_id')
            ->orderBy('meeting_room_reservations.start_at', 'desc');
        if (isset($request->office_id) && $request->office_id != null) {
            $reserved_rooms=$reserved_rooms->where('meeting_room_reservations.office_id' , $request->office_id);
        }

        $reserved_rooms=$reserved_rooms->paginate($per_page);
        if ($reserved_rooms == null) {
            return response()->json(['message' => 'Rezerv edilmis otaq yoxdur'], 404);
        }
        return $this->dataResponse($reserved_rooms, 200);
    }

    public function filter(Request $request)
    {
        $this->validate($request, [
            'company_id' => 'required',
            'office_name' => 'sometimes|string',
            'room_name' => 'sometimes|string',
            'date' => 'sometimes|date',
            'status' => 'sometimes|in:0,1,2,3',
            'price' => 'sometimes',
            'per_page' => 'sometimes|integer'
        ]);


//        $data=Meeting::query()->with(['company:id,name','room:id,name'])->where('company_id',$request->company_id)->get();

        $per_page = $request->per_page ?? 10;
        try {
            $data = \DB::table('meeting_room_reservations')
                ->where('meeting_room_reservations.company_id', $request->company_id)
                ->select('offices.name as office_name', 'companies.name as company_name', 'meeting_rooms.name as room_name', 'start_at as start_date', 'finish_at as end_date', 'meeting_room_reservations.status', 'price')
                ->leftJoin('companies', 'companies.id', '=', 'meeting_room_reservations.company_id')
                ->leftJoin('meeting_rooms', 'meeting_rooms.id', '=', 'meeting_room_reservations.meeting_room')
                ->leftJoin('offices', 'offices.company_id', '=', 'meeting_room_reservations.company_id');

            if ($request->has('office_name'))
                $data->where('offices.name', 'like', '%' . $request->get('office_name') . '%');


            if ($request->has('room_name'))
                $data = $data->where('meeting_rooms.name', 'like', '%' . $request->get('room_name') . '%');

            if ($request->has('date'))
                $data = $data->where('meeting_room_reservations.start_at', 'like', '%' . $request->get('date') . '%');


            if ($request->has('status'))
                $data = $data->where('meeting_room_reservations.status', $request->status);

            if ($request->has('price'))
                $data = $data->where('meeting_room_reservations.price', 'like', '%' . $request->price . '%');


            return $this->dataResponse($data->paginate($per_page), 200);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

}

