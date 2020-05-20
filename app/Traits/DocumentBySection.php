<?php


namespace App\Traits;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Esd\Entities\senderCompany;
use Modules\Hr\Entities\Employee\Employee;

trait DocumentBySection
{
    protected function saveBySection(Request $request, $tableName)
    {
        switch ($tableName) {
            case "structure_docs":
                $this->validate($request, [
                    'sender_company_id' => [ 'sometimes','required', 'integer'],
                    'sender_company_role_id' => ['sometimes', 'required', 'integer'],
                    'sender_company_user_id' => ['sometimes', 'required', 'integer'],
                ]);
                if ($request->has('sender_company_id')){
                    $check = senderCompany::where('company_id', $request->get('company_id'))
                        ->where('id', $request->get('sender_company_id'))
                        ->checkSender($request)
                        ->exists();
                    if (!$check) return $this->errorResponse(trans('response.senderDataIsNotValid'));
                }


                return $request->only([
                    'sender_company_id',
                    'sender_company_role_id',
                    'sender_company_user_id'
                ]);
                break;
            case "citizen_docs":
                $this->validate($request, [
                    'name' => ['required', 'min:2,max:255'],
                    'region_id' => ['required', 'integer'],
                    'address' => ['sometimes', 'required', 'min:2,max:255'],
                ]);
                return $request->only([
                    'name',
                    'region_id',
                    'address'
                ]);
                break;
            case "in_company_docs":
                $this->validate($request, [
                    'from_in_our_company' => ['required', 'integer'],
                    'to_in_our_company' => ['required', 'integer']
                ]);
                $data = [$request->get('from_in_our_company') ,$request->get('to_in_our_company')  ];

                $existsCount = Employee::whereIn('id' , $data)
                    ->where('company_id' , $request->get('company_id'))
                    ->count();
                if ($existsCount != count($data)) return $this->errorResponse(trans('response.employeeNotFound'));

                return $data;
                break;
        }
    }
}
