<?php


namespace Modules\Hr\Http\Controllers;


use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Hr\Entities\Color;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controller;

//todo adding mysql relation error excepstion handler
class ColorController extends Controller
{
    use ApiResponse,ValidatesRequests;

    public function index(Request $request)
    {
        $this->validate($request , [
            'paginateCount' => ['sometimes' , 'required' , 'integer'],
            'name' => ['sometimes', 'string' , 'max:255']
        ]);

        $result = Color::orderBy('id' , 'desc');

        if ($request->has('name'))
            $result->where('name' , 'like' , $request->get('name'));

        $result = $result->paginate($request->get('paginateCount'));

        return $this->dataResponse($result);
    }

    public function create(Request $request)
    {
        $error = $this->validateRequest($request->all());
        if ($error)
            return $this->errorResponse($error, 422);
        try
        {
            $saved = true;
            Color::create([
                'name' => $request->get('name'),
                'code' => $request->get('code'),
            ]);
        }
        catch (\Exception $exception)
        {
            $saved = false;
        }
        return $saved
            ? $this->successResponse(trans('messages.saved'), 201)
            : $this->errorResponse(trans('messages.not_saved'));
    }

    public function update(Request $request, $id)
    {
        $error = $this->validateRequest($request->all());
        if ($error)
            return $this->errorResponse($error, 422);
        try
        {
            $saved = true;

            $color = Color::where('id', $id)->exists();
            if (!$color)
                return $this->errorResponse(trans('messages.not_found'), 404);

            Color::where('id' , $id)->update([
                'name' => $request->get('name'),
                'code' => $request->get('code'),
                'position' => $request->get('position'),
            ]);
        }
        catch (\Exception $e)
        {
            $saved = false;
            DB::rollBack();
        }
        return $saved
            ? $this->successResponse(trans('messages.saved'))
            : $this->errorResponse(trans('messages.not_saved'));
    }

    public function destroy(Request $request , $id)
    {
        return Color::where('id', $id)->delete()
            ? $this->successResponse(trans('messages.saved'))
            : $this->errorResponse(trans('messages.not_saved'));
    }

    protected function validateRequest($input){
        $validationArray = [
            'name' => ['required','max:255'],
            'code' => ['required' , 'max:50'],
            'position' => ['required','numeric'],
        ];
        $validator = \Validator::make($input, $validationArray);

        if($validator->fails())
            return $validator->errors();
        return null;
    }
}
