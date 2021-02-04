<?php


namespace Modules\Plaza\Http\Controllers;


use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Plaza\Entities\Floor;
use Modules\Plaza\Entities\Office;
use App\Traits\ApiResponse;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Routing\Controller;
class FloorController extends Controller
{
    use ApiResponse  , ValidatesRequests;

    public function index(Request $request)
    {
        $this->validate($request, [
            'company_id' => 'required|integer',
            'filter' => 'sometimes|boolean'
        ]);
        try {
            $columns = $request->filter ? ['id', 'number'] : ['*'];

            $floors = Floor::where('company_id', $request->company_id)->get($columns);

            return $this->successResponse($floors);
        } catch (\Exception $e) {
            return $this->errorResponse(trans('apiResponse.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);

        }
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'number' => 'required|numeric',
            'company_id' => 'required|integer',
            'common_size' => 'required|numeric',
            'image' => 'sometimes|required|file|mimes:jpeg,pdf,png,jpg,gif,svg',
        ]);

        try {
            $floor = new Floor();

            $floor->fill($request->only('number', 'company_id', 'common_size'));

            if ($request->hasFile('image')) {
                $floor->image = $this->uploadImage($request->image, $request->company_id);
            }
            $floor->sold_size = 0;
            $floor->save();
            return $this->successResponse('OK');

        } catch (QueryException $e) {
            if ($e->errorInfo[1] == 1062)
                return $this->errorResponse(['message' => trans('apiResponse.floorAlreadyExists'), 'number' => $request->number]);
            return $this->errorResponse(trans('apiResponse.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Exception $e) {
            return $this->errorResponse(trans('apiResponse.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function uploadImage(UploadedFile $file, int $company_id, $str = "floors"): string
    {
        return $file->store("documents/$company_id/$str");
    }

    public function show(Request $request, $id)
    {
        $this->validate($request, [
            'company_id' => 'required|integer',
            'with_offices' => ['sometimes' , 'required' ]
        ]);
        try {
            $floor = Floor::where([
                'id' => $id,
                'company_id' => $request->company_id
            ])->first();
            if (!$floor)
                return $this->errorResponse(trans('apiResponse.unProcess'));

            if ($request->with_offices) $floor->load('offices:name');

            return $this->successResponse($floor);
        } catch (\Exception $e) {
            return $this->errorResponse(trans('apiResponse.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'company_id' => 'required|integer',
            'image' => 'sometimes|required|file|mimes:jpeg,pdf,png,jpg,gif,svg',
            'common_size' => 'sometimes|required|numeric',
            'number' => 'sometimes|required|number'
        ]);
        $arr = $request->only('common_size', 'image', 'number');
        if (!$arr)
            return $this->errorResponse(trans('apiResponse.nothing'));
        try {
            $floor = Floor::where('id', $id)
                ->where('company_id', $request->company_id)
                ->first();
            if (!$floor)
                return $this->errorResponse(trans('apiResponse.unProcess'));
            if ($request->has('common_size')) {
                if ($request->common_size < $floor->sold_size)
                    return $this->errorResponse(trans('apiResponse.sizeError'));
            }

            if ($request->hasFile('image')) {
                $filename = $this->uploadImage($request->image, $request->company_id);
                if ($floor->image)
                    File::delete(base_path('public/' . $request->company_id . "/floors/" . $floor->image));
                $floor->image = $filename;
            }

            $floor->fill($request->only('common_size', 'number'));

            $check = $floor->save();
            if (!$check)
                return $this->errorResponse(trans('apiResponse.tryLater'));

            return $this->successResponse('OK');

        } catch (QueryException $e) {
            if ($e->getCode() == "23000")
                return $this->errorResponse(['message' => trans('apiResponse.floorAlreadyExists'), 'number' => $request->number]);
            return $this->errorResponse(trans('apiResponse.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Exception $e) {
            return $this->errorResponse(trans('apiResponse.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function destroy(Request $request, $id)
    {
        $this->validate($request, [
            'company_id' => 'required|integer',
        ]);
        try {
            $check = Floor::where('id', $id)
                ->where('company_id', $request->company_id)
                ->delete();
            if (!$check)
                return $this->errorResponse(trans('apiResponse.unProcess'));

            return $this->successResponse("OK");
        } catch (\Exception $e) {
            return $this->errorResponse(trans('apiResponse.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
