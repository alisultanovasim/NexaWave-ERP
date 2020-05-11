<?php


namespace App\Http\Controllers;


use App\Models\GlobalLog;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use function Deployer\has;

class GlobalLogsController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $this->validate($request, [
            'method' => 'sometimes|required|string',
            'services' => 'sometimes|required|array',
            'services.*' => 'sometimes|required|string',
            'user_id' => 'sometimes|required|integer',
            'created_at' => 'sometimes|required|date',
            'created_after' => 'sometimes|required|date',
            'created_before' => 'sometimes|required|date',
        ]);

        try {
            $logs = GlobalLog::with('user:id,username')->where('company_id', Auth::user()->company_id);

            if ($request->has('method'))
                    $logs->where('method', $request->get('method'));

            if ($request->has('services'))
                $logs->whereIn('service', $request->services);

            if ($request->has('user_id'))
                $logs->where('user_id', $request->user_id);

            if ($request->has('created_at'))
                $logs->where('created_at', $request->created_at);

            if ($request->has('created_before'))
                $logs->where('created_at', "<=", $request->created_before);

            if ($request->has('created_after'))
                $logs->where('created_at', ">=", $request->created_after);

            $logs = $logs
                ->orderBy('id' , 'desc')
                ->paginate($request->per_page ?? 20 , [
                    'id',
                    'description',
                    'user_id',
                    'created_at'
            ]);
            return $this->successResponse($logs);

        } catch (\Exception $exception) {
            return $this->errorResponse(trans('responses.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(Request $request , $id)
    {
        try {
            $log = GlobalLog::with('user:id,username,email')
                ->where('id' , $id)
                ->where('company_id', Auth::user()->company_id)
                ->first();

            if (!$log) return $this->errorResponse(trans('responses.unProcess'),Response::HTTP_NOT_FOUND);

            return $this->successResponse($log);

        } catch (\Exception $exception) {
            return $this->errorResponse(trans('responses.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function delete(Request $request, $id)
    {
        try {
            $check = GlobalLog::where('id', $id)
                ->where('company_id', $request->company_id)
                ->delete();
            if (!$check) return $this->errorResponse(trans('responses.logNotFound'), Response::HTTP_UNPROCESSABLE_ENTITY);
            return $this->successResponse('OK');
        } catch (\Exception $e) {
            return $this->errorResponse(trans('responses.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public static function store(Request $request){

    }

}
