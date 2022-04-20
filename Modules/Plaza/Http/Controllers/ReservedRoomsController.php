<?php


namespace Modules\Plaza\Http\Controllers;


use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Plaza\Entities\Guest;
use Modules\Plaza\Entities\Meeting;
use Modules\Plaza\Entities\Office;
use Modules\Plaza\Entities\Worker;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
class ReservedRoomsController extends Controller
{
    use ApiResponse  , ValidatesRequests;

    public function rooms(Request $request){
        $this->validate($request,[
            'company_id'=>'required'
        ]);
        $reserved_rooms=\DB::table('meeting_room_reservations')
            ->where('meeting_room_reservations.company_id',$request->company_id)
            ->select('companies.name as company_name','meeting_rooms.name as room_name','start_at as date','meeting_room_reservations.status','price')
            ->leftJoin('companies','companies.id','=','meeting_room_reservations.company_id')
            ->leftJoin('meeting_rooms','meeting_rooms.id','=','meeting_room_reservations.meeting_room')
            ->get();
        if ($reserved_rooms==null){
            return \response()->json(['message'=>'Rezerv edilmis otaq yoxdur'],404);
        }
        return $this->dataResponse($reserved_rooms,200);
    }
    public function filter(Request $request){
        $this->validate($request,[
            'company_id'=>'required',
            'company_name'=>'sometimes|boolean',
            'room_name'=>'sometimes|boolean',
            'date'=>'sometimes|boolean',
            'status'=>'sometimes|in:0,1,2,3',
            'price'=>'sometimes|boolean'
        ]);


//        $data=Meeting::query()->with(['company:id,name','room:id,name'])->where('company_id',$request->company_id)->get();

        try {
            $data=\DB::table('meeting_room_reservations')
                ->where('meeting_room_reservations.company_id',$request->company_id)
                ->select('companies.name as company_name','meeting_rooms.name as room_name','start_at as date','meeting_room_reservations.status','price','meeting_rooms.name')
                ->leftJoin('companies','companies.id','=','meeting_room_reservations.company_id')
                ->leftJoin('meeting_rooms','meeting_rooms.id','=','meeting_room_reservations.meeting_room');

            if ($request->has('company_name'))
                if ($request->company_name==true){
                    $data=$data->orderBy('companies.name','ASC');
                }
                else{
                    $data=$data->orderBy('companies.name','DESC');
                }



            if ($request->has('room_name'))
                if ($request->room_name==true){
                    $data=$data->orderBy('meeting_rooms.name','ASC');
                }
                else{
                    $data=$data->orderBy('meeting_rooms.name','DESC');
                }

            if ($request->has('date'))
                if ($request->date==true){
                    $data=$data->orderBy('start_at','ASC');
                }
                else{
                    $data=$data->orderBy('start_at','DESC');
                }


            if ($request->has('status'))
                $data=$data->where('meeting_room_reservations.status',$request->status);

            if ($request->has('price'))
                if ($request->price==true){
                    $data=$data->orderBy('price','ASC');
                }
                else{
                    $data=$data->orderBy('price','DESC');
                }


            return $this->dataResponse($data->get(),200);
        }
        catch (\Exception $e){
            return $e->getMessage();
        }
    }

}

