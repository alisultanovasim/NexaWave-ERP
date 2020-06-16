<?php


namespace Modules\Hr\Http\Controllers;


use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Hr\Entities\Section;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controller;

class SectionController extends Controller
{
    use ApiResponse, ValidatesRequests;

    public function index(Request $request)
    {
        $this->validate($request , [
            'company_id' => ['required' , 'integer'],
            'paginateCount' => ['sometimes' , 'integer'],
            'department_id' => ['sometimes' , 'required' , 'integer']
        ]);
        $result = Section::where('company_id' , $request->get('company_id'))->orderBy('position');

        if ($request->has('department_id')) $result->where('department_id' , $request->get('department_id'));

        $result = $result->paginate($request->get('paginateCount'));

        return $this->dataResponse($result);
    }
    public function show(Request $request , $id){
        $this->validate($request , [
            'company_id' => ['required' , 'integer'],
        ]);
        $result = Section::with('department:id,name,short_name')->where('company_id' , $request->get('company_id'))
            ->where('id' , $id)
            ->first();
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
            Section::create($this->dataRequest($request));
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
            $section = Section::where('id', $id)->where('company_id', $request->get('company_id'))->exists();
            if (!$section)
                return $this->errorResponse(trans('messages.not_found'), 404);
            Section::where('id', $id)->update($this->dataRequest($request));
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
        return Section::where('id', $id)->where('company_id' , $request->get('company_id'))->delete()
            ? $this->successResponse(trans('messages.saved'))
            : $this->errorResponse(trans('messages.not_saved'));
    }

    protected function validateRequest($input){
        $validationArray = [
            'name' => 'required|max:256',
            'code' => 'required|max:50',
            'short_name' => 'nullable|max:50',
            'is_closed' => 'boolean',
            'closing_date' => 'nullable|date_format:Y-m-d',
            'position' => 'required|numeric',
            'company_id' =>['sometimes' , 'integer']
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
            'company_id' => $request->get('company_id'),
            'position' => $request->get('position')
        ];

        if ($data['is_closed'])
            $data['closing_date'] = $request->get('closing_date');

        return $data;
    }
}
