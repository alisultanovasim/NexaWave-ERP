<?php


namespace Modules\Hr\Http\Controllers;


use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Hr\Entities\Department;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


//todo mysql relation exception make
//by vusal
use Illuminate\Routing\Controller;

class DepartmentController extends Controller
{
    use ApiResponse , ValidatesRequests;

    public function index(Request $request)
    {
        $this->validate($request , [
           'company_id' => ['required' , 'integer'],
            'paginateCount' => ['sometimes' , 'integer']
        ]);
        $result = Department::with(['city:id,name'])->paginate($request->paginateCount);

        return $this->dataResponse($result);
    }


    public function show(Request $request , $id){
        $this->validate($request , [
            'company_id' => ['required' , 'integer'],
        ]);
        $result = Department::withAllRelations()
            ->where('company_id' , $request->get('company_id'))
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
            Department::create($this->dataRequest($request));
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
            $department = Department::where('id', $id)->where('company_id', $request->get('company_id'))->exists();
            if (!$department)
                return $this->errorResponse(trans('messages.not_found'), 404);
            Department::where('id', $id)->update($this->dataRequest($request));
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
        return Department::where('id', $id)->where('company_id' ,$request->get('company_id') )->delete()
            ? $this->successResponse(trans('messages.saved'))
            : $this->errorResponse(trans('messages.not_saved'));
    }

    protected function validateRequest($input){
        $validationArray = [
            'name' => 'required|max:256',
            'code' => 'required|max:50',
            'short_name' => 'max:50',
            'is_parent_department' => 'boolean',
            'department_id' => 'numeric|min:1',
            'phone' => 'max:50',
            'fax' => 'max:50',
            'address' => 'max:256',
            'email' => 'email|max:100',
            'web_site' => 'max:100',
            'zip_code' => 'max:10',
            'country_id' => 'required|numeric|min:1|exists:countries,id',
            'city_id' => 'required|numeric|min:1|exists:cities,id',
            'region_id' => 'numeric|min:1|exists:regions,id',
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
            'is_parent_department' => $request->get('is_parent_department') ? $request->get('is_parent_department') : 0,
            'phone' => $request->get('phone'),
            'fax' => $request->get('phone'),
            'address' => $request->get('address'),
            'email' => $request->get('email'),
            'web_site' => $request->get('web_site'),
            'zip_code' => $request->get('zip_code'),
            'country_id' => $request->get('country_id'),
            'city_id' => $request->get('city_id'),
            'region_id' => $request->get('region_id'),
            'is_closed' => $request->get('is_closed') ? $request->get('is_closed') : false,
            'position' => $request->get('position'),
            'company_id' => $request->get('company_id')
        ];
        if (!$data['is_parent_department'])
            $data['department_id'] = $request->get('department_id');
        $data['closing_date'] = $request->get('closing_date') ? $request->get('closing_date') : null;

        return $data;
    }
}
