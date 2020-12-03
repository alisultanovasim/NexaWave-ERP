<?php


namespace Modules\Hr\Http\Controllers;


use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Hr\Entities\BankInformation;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controller;

class BankInformationController extends Controller
{
    use ApiResponse,ValidatesRequests;

    public function index(Request $request)
    {
        $this->validate($request , [
            'company_id' => ['required' , 'integer'] ,
            'paginateCount' => ['sometimes' , 'required' , 'integer']
        ]);
        $info  = BankInformation::where('company_id' , $request->get('company_id'))
            ->paginate($request->get('paginateCount'));
        return $this->dataResponse($info);
    }

    public function create(Request $request)
    {
        $error = $this->validateRequest($request->all());
        if ($error)
            return $this->errorResponse($error,422);
        try
        {
            DB::beginTransaction();
            $saved = true;
            BankInformation::create([
                'name' => $request->get('name'),
                'short_name' => $request->get('short_name'),
                'registration_date' => $request->get('registration_date'),
                'licence' => $request->get('licence'),
                'auditor' => $request->get('auditor'),
                'address' => $request->get('address'),
                'phone' => $request->get('phone'),
                'correspondent' => $request->get('correspondent'),
                'swift' => $request->get('swift'),
                'code' => $request->get('code'),
                'teleks' => $request->get('teleks'),
                'fax' => $request->get('fax'),
                'email' => $request->get('email'),
                'site' => $request->get('site'),
                'voen' => $request->get('voen'),
                'index' => $request->get('index'),
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
            ? $this->successResponse(trans('message.saved'), 201)
            : $this->errorResponse(trans('message.not_saved'));
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
            $bankInformation = BankInformation::where('id', $id)->where('company_id' , $request->get('company_id'))->first(['id']);
            if (!$bankInformation)
                return $this->errorResponse(trans('messages.not_found'), 404);
            $bankInformation->update([
                'name' => $request->get('name'),
                'short_name' => $request->get('short_name'),
                'registration_date' => $request->get('registration_date'),
                'licence' => $request->get('licence'),
                'auditor' => $request->get('auditor'),
                'address' => $request->get('address'),
                'phone' => $request->get('phone'),
                'correspondent' => $request->get('correspondent'),
                'swift' => $request->get('swift'),
                'code' => $request->get('code'),
                'teleks' => $request->get('teleks'),
                'fax' => $request->get('fax'),
                'email' => $request->get('email'),
                'site' => $request->get('site'),
                'voen' => $request->get('voen'),
                'index' => $request->get('index'),

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

    public function destroy(Request $request, $id)
    {
        return BankInformation::where('id', $id)->where('company_id' , $request->get('company_id'))->delete()
            ? $this->successResponse(trans('messages.saved'))
            : $this->errorResponse(trans('messages.not_saved'));
    }

    protected function validateRequest($input)
    {
        $validationArray = [
            'name' => 'required|max:256',
            'short_name' => 'required|max:100',
            'registration_date' => 'required|date',
            'licence' => 'required|max:300',
            'auditor' => 'required|max:300',
            'address' => 'required|max:500',
            'phone' => 'required|max:100',
            'correspondent' => 'required|max:100',
            'swift' => 'required|max:11',
            'code' => 'required|numeric',
            'teleks' => 'max:50',
            'fax' => 'max:50',
            'email' => 'required|email|max:100',
            'site' => 'max:100',
            'voen' => 'required|max:100',
            'index' => 'required|numeric',
            'company_id' => ['required' , 'integer'] ,
        ];

        $validator = \Validator::make($input, $validationArray);

        if ($validator->fails())
            return $validator->errors();
        return null;
    }
}
