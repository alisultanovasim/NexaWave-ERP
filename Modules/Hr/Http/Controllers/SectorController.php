<?php


namespace Modules\Hr\Http\Controllers;

use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Hr\Entities\Sector;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controller;

class SectorController extends Controller
{
    use ApiResponse, ValidatesRequests;

    public function index(Request $request)
    {
        $this->validate($request , [
            'company_id' => ['required' , 'integer'],
            'paginateCount' => ['sometimes' , 'integer'],
            'section_id' => ['sometimes' , 'required' , 'integer']
        ]);
        $result = Sector::where('company_id' , $request->get('company_id'))->orderBy('position');

        if ($request->has('section_id')) $result->where('section_id' , $request->get('section_id'));

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
            DB::beginTransaction();
            $saved = true;
            Sector::create($this->dataRequest($request));
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
            $sector = Sector::where('id', $id)->where('company_id'  , $request->get('company_id'))->exists();
            if (!$sector)
                return $this->errorResponse(trans('messages.not_found'), 404);
            Sector::where('id', $id)->update($this->dataRequest($request));
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

    public function destroy($id)
    {
        return Sector::where('id', $id)->delete()
            ? $this->successResponse(trans('messages.saved'))
            : $this->errorResponse(trans('messages.not_saved'));
    }


    public function show(Request $request , $id){
        $this->validate($request , [
            'company_id' => ['required' , 'integer'],
        ]);
        $result = Sector::with('section:id,name,short_name')
            ->where('company_id' , $request->get('company_id'))
            ->where('id' , $id)
            ->first();
        return $this->dataResponse($result);
    }


    protected function validateRequest($input){
        $validationArray = [
            'name' => 'required|max:256',
            'code' => 'required|max:50',
            'short_name' => 'nullable|max:50',
            'is_closed' => 'boolean',
            'closing_date' => 'date_format:Y-m-d',
            'position' => 'required|numeric',
            'company_id' => ['required' , 'integer']
        ];
        $validator = \Validator::make($input, $validationArray);

        if($validator->fails())
            return $validator->errors();
        return null;
    }

    protected function dataRequest(Request $request)
    {
        $data =  [
            'name' => $request->get('name'),
            'code' => $request->get('code'),
            'short_name' => $request->get('short_name'),
            'is_closed' => $request->get('is_closed'),
            'position' => $request->get('position'),
            'company_id' => $request->get('company_id')
        ];

        if ($data['is_closed'])
            $data['closing_date'] = $request->get('closing_date');

        return $data;
    }
}
