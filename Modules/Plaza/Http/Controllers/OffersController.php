<?php


namespace Modules\Plaza\Http\Controllers;


use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Plaza\Entities\Offer;
use App\Traits\ApiResponse;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controller;
class OffersController extends Controller
{
    use ApiResponse  , ValidatesRequests;

    /**
     *   specialization_id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function index(Request $request)
    {
        $this->validate($request, [
            'company_id' => 'required|integer',

            'name' => 'sometimes|required|string',
            'come_at' => 'sometimes|required|date|date_format:Y-m-d',
            'status' => 'sometimes|required|in:1,0',
            'from' => 'sometimes|required|date|date_format:Y-m-d',
            'to' => 'sometimes|required|date|date_format:Y-m-d',
            'max_room_count'=>'sometimes|required|integer',
            'min_room_count'=>'sometimes|required|integer',
            'max_worker_count'=>'sometimes|required|integer',
            'min_worker_count'=>'sometimes|required|integer',
            'max_car_count'=>'sometimes|required|integer',
            'min_car_count'=>'sometimes|required|integer',
            'specialization_ids'=>'sometimes|required|array',
            'specialization_ids.*'=>'required_with:specialization_ids|integer'
        ]);
        try {

            $offers = Offer::with('specialization')->where('company_id', $request->company_id);

            if ($request->has('come_at'))
                $offers->where('come_at', $request->time);

            if ($request->has('status'))
                $offers->where('status', $request->status);

            if ($request->has('name'))
                $offers->where('name', 'like', $request->name . "%");

            if ($request->has('from'))
                $offers->where("come_at", ">", $request->from);

            if ($request->has('to'))
                $offers->where("come_at", "<", $request->to);

            if ($request->has('max_room_count'))
                $offers->where("room_count", ">=", $request->max_room_count);

            if ($request->has('min_room_count'))
                $offers->where("room_count", "<=", $request->min_room_count);

            if ($request->has('max_worker_count'))
                $offers->where("worker_count", ">=", $request->max_worker_count);

            if ($request->has('min_worker_count'))
                $offers->where("worker_count", "<=", $request->min_worker_count);

            if ($request->has('max_car_count'))
                $offers->where("car_count", ">=", $request->max_car_count);

            if ($request->has('min_car_count'))
                $offers->where("car_count", "<=", $request->min_car_count);

            if ($request->has('specialization_id')){
                $offers->where("specialization_id", $request->specialization_id);
            }

            $offers = $offers->orderBy('id', 'DESC')->paginate($request->per_page ?? 10);


            return $this->successResponse($offers);

        } catch (\Exception $e) {
            return $this->errorResponse(trans('apiResponse.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'company_id' => 'required|integer',

            'client' => 'required|string|max:255',
            'name' => 'required|string|max:255',

            'size' => 'sometimes|required|numeric',
            'description' => 'sometimes|required|string',
            'phone' => 'sometimes|required',
            'come_at' => 'required|date|date_format:Y-m-d',
            'status' => 'sometimes|required|in:1,0',

            'min_room_count'=>'sometimes|required|integer',
            'min_worker_count'=>'sometimes|required|integer',
            'min_car_count'=>'sometimes|required|integer',
            'specialization_ids.*'=>'sometimes|integer'
        ]);
        try {
            Offer::create($request->all());
            return $this->successResponse('OK');
        }catch (QueryException $e) {
            DB::rollBack();
            if ($e->errorInfo[1] == 1452) {
                    return $this->errorResponse(['specialization_ids' => "does not exist"], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            return $this->errorResponse(trans('apiResponse.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);

        } catch (\Exception $e) {
            return $this->errorResponse(trans('apiResponse.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(Request $request, $id)
    {
        $this->validate($request, [
            'company_id' => 'required|integer',

        ]);
        try {
            $offer = Offer::where('company_id' , $request->company_id)->where("id",$id)->first();
            if (!$offer) return $this->errorResponse(trans('apiResponse.unProcess'));
            return $this->successResponse($offer);
        } catch (\Exception $e) {
            return $this->errorResponse(trans('apiResponse.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'company_id' => 'required|integer',

            'client' => 'sometimes|required|string',
            'name' => 'sometimes|required|integer',
            'size' => 'sometimes|required|numeric',
            'description' => 'sometimes|required|string',
            'phone' => 'sometimes|required',
            'come_at' => 'sometimes|required|date|date_format:Y-m-d',
            'status' => 'sometimes|required|in:1,0'
        ]);
        $arr = $request->only('size', 'client', 'name', 'description', 'phone', 'come_at', 'status');
        if (!$arr) return $this->errorResponse(trans('apiResponse.nothing'));
        try {
            $offer = Offer::where('company_id', $request->company_id)
                ->where("id", $id)
                ->update($arr);
            if (!$offer) return $this->errorResponse(trans('apiResponse.unProcess'));
            return $this->successResponse('OK');
        } catch (\Exception $e) {
            return $this->errorResponse(trans('apiResponse.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function delete(Request $request, $id)
    {
        $this->validate($request, [
            'company_id' => 'required|integer',

        ]);
        try {
            $offer = Offer::where('company_id', $request->company_id)
                ->where("id", $id)
                ->delete();
            if (!$offer) return $this->errorResponse(trans('apiResponse.unProcess'));
            return $this->successResponse('OK');
        } catch (\Exception $e) {
            return $this->errorResponse(trans('apiResponse.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
