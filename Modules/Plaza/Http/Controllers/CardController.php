<?php


namespace Modules\Plaza\Http\Controllers;


use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Plaza\Entities\Card;
use Modules\Plaza\Entities\Worker;
use App\Traits\ApiResponse;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;


class CardController extends Controller
{

    use ApiResponse , ValidatesRequests;

    public function index(Request $request)
    {
        $this->validate($request, [
            'company_id' => 'required|integer',

            'with_workers' => 'sometimes|boolean'
        ]);

        try {
            $cards = Card::where('company_id', $request->company_id)->get(['id', 'alias']);
            if ($request->with_workers) $cards->load('worker:id,name,card');
            return $this->successResponse($cards);
        } catch (\Exception $e) {
            return $this->errorResponse(trans('apiResponse.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(Request $request, $id)
    {
        $this->validate($request, [
            'company_id' => 'required|integer',

            'with_workers' => 'sometimes|boolean'
        ]);
        try {
            $card = Card::where('id', $id)->where('company_id', $request->company_id)->first();

            if (!$card) return $this->errorResponse(trans('apiResponse.unProcess'));

            if ($request->with_workers) $card->load('worker:id,name,card');
            return $this->successResponse($card);

        } catch (\Exception $exception) {
            return $this->errorResponse(trans('apiResponse.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'company_id' => 'required|integer',

            'alias' => 'required|string|max:255'
        ]);

        try {
            Card::create($request->only('alias', 'company_id'));
            return $this->successResponse('OK');
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->errorInfo[1] === 1062)
                return $this->errorResponse(['alias' => 'already exists']);
            return $this->errorResponse(trans('apiResponse.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);

        } catch (\Exception $e) {
            return $this->errorResponse(trans('apiResponse.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'company_id' => 'required|integer',

            'alias' => 'required|string|max:255'
        ]);
        try {
            $check = Card::where('company_id', $request->company_id)->where('id', $id)->update(['alias' => $request->alias]);
            if (!$check) $this->errorResponse(trans('apiResponse.unProcess'));
            return $this->successResponse('OK');
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->errorInfo[1] === 1062)
                return $this->errorResponse(['alias' => 'already exists']);
            return $this->errorResponse(trans('apiResponse.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);

        } catch (\Exception $exception) {
            return $this->errorResponse(trans('apiResponse.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function delete(Request $request, $id)
    {
        $this->validate($request, [
            'company_id' => 'required|integer',

        ]);
        try {
            $check = Card::where('company_id', $request->company_id)->where('id', $id)->delete();
            if (!$check) $this->errorResponse(trans('apiResponse.unProcess'));
            return $this->successResponse('OK');
        } catch (QueryException $exception) {
            if ($exception->errorInfo[1] == 1451) {
                $worker = Worker::where("card", $id)->first(["name", "id"]);
                return $this->errorResponse([
                    'error' => trans('apiResponse.workerUserCard', ['name' => $worker->name]),
                    'worker' => $worker
                ]);
            }
            return $this->errorResponse(trans('apiResponse.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Exception $exception) {
            return $this->errorResponse(trans('apiResponse.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


}


/**
 * $office = Office::where('company_id' , $request->company_id->first('id'));
 * if (!$office)
 * Card::create([
 * 'office_id' => $office->id,
 * ]);
 */
