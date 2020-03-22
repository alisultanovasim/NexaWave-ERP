<?php


namespace Modules\Hr\Http\Controllers;


use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Hr\Entities\PunishmentType;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controller;

class PunishmentTypeController extends Controller
{
    use ApiResponse, ValidatesRequests;

    public function index(Request $request)
    {
        $this->validate($request , [
            'company_id' => ['required', 'integer'],
            'paginateCount' => ['sometimes' , 'required' , 'integer']
        ]);

        $result = PunishmentType::where('company_id' , $request->get('company_id'))->paginate($request->get('paginateCount'));


        return $this->dataResponse($result);
    }

    public function create(Request $request)
    {
        $error = $this->validateRequest($request->all());
        if ($error)
            return $this->errorResponse($error, 422);
        try
        {
            DB::beginTransaction();
            $saved = true;
            PunishmentType::create([
                'name' => $request->get('name'),
                'code' => $request->get('code'),
                'position' => $request->get('position'),
                'company_id' => $request->get('company_id')
            ]);
            DB::commit();
        }
        catch (\Exception $exception)
        {
            $saved = false;
            DB::rollBack();
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
            DB::beginTransaction();
            $saved = true;
            $punishmentType = PunishmentType::where('id', $id)->where('company_id' , $request->get('company_id'))->first(['id']);
            if (!$punishmentType)
                return $this->errorResponse(trans('messages.not_found'), 404);
            $punishmentType->update([
                'name' => $request->get('name'),
                'code' => $request->get('code'),
                'position' => $request->get('position'),
            ]);
            DB::commit();
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
        return PunishmentType::where('id', $id)->where('company_id' , $request->get('company_id'))->delete()
            ? $this->successResponse(trans('messages.saved'))
            : $this->errorResponse(trans('messages.not_saved'));
    }

    protected function validateRequest($input){
        $validationArray = [
            'name' => 'required|max:256',
            'code' => 'required|max:50',
            'position' => 'required|numeric',
            'company_id' => ['required', 'integer'],
        ];
        $validator = \Validator::make($input, $validationArray);

        if($validator->fails())
            return $validator->errors();
        return null;
    }
}
