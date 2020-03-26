<?php


namespace App\Traits;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Esd\Entities\senderCompany;

trait DocumentBySection
{
    use  Query;
    protected function saveBySection(Request $request, $document, $tableName )
    {
        switch ($tableName) {
            case "structure_docs":
                $this->validate($request, [
                    'sender_company_id' => 'required|integer',
                    'sender_company_role_id' => 'sometimes|required|integer',
                    'sender_company_user_id' => 'sometimes|required|integer',
                    'company_user' => 'sometimes|required|integer'
                ]);

//                if ($noteExists = $this->companyInfo(
//                    $request->get('company_id'),
//                    $request->only(['sender_company_id' , 'sender_company_role_id' , 'sender_company_user_id' , 'company_user'])))
//                        return $noteExists;

                $check = senderCompany::whereHas('roles' , function ($q) use ($request) {
                        $q->whereHas('users' , function ($q) use ($request) {
                            $q->where(DB::raw("1") , "1" );
                            if ($request->has('sender_company_user_id'))
                                $q->where('id' ,$request->get('sender_company_user_id'));
                        })->where(DB::raw("1") , "1" );
                        if ($request->has('sender_company_role_id'))
                            $q->where('id' ,$request->get('sender_company_role_id'));
                })
                    ->where('company_id' , $request->get('company_id'))
                    ->where('id', $request->get('sender_company_id'))
                    ->exists();
                if (!$check) return $this->errorResponse(trans('response.unProcess') ,422);
                return array_merge($request->only([
                    'sender_company_role_id', 'sender_company_id', 'sender_company_user_id'
                ]),['document_id' => $document->id]);
                break;
            case "citizen_docs":
                $this->validate($request, [
                    'name' => 'required|min:2,max:255',
                    'region_id' => 'required|integer',
                    'address' => 'sometimes|required|min:2,max:255',
                    'company_user' => 'sometimes|required|integer'
                ]);
                if ($noteExists = $this->companyInfo(
                    $request->get('company_id'),
                    $request->only(['company_user'])))
                    return $noteExists;

                return array_merge($request->only('name', 'region_id', 'address'), ['document_id' => $document->id]);
                break;
            case "in_company_docs":
                $this->validate($request, [
                    'from_in_our_company' => 'required|integer',
                    'to_in_our_company' => 'required|integer'
                ]);
                if ($noteExists = $this->companyInfo(
                    $request->get('company_id'),
                    $request->only(['from_in_our_company' , 'to_in_our_company' ])))
                    return $noteExists;
                return array_merge($request->only('from_in_our_company', 'to_in_our_company'), ['document_id' => $document->id]);
                break;
        }
    }

    protected function dataBySection($request, $document, $tableName)
    {
        switch ($tableName) {
            case "structure_docs":
                $this->validate($request, [
                    'sender_company_id' => 'sometimes|required|integer',
                    'sender_company_role_id' => 'sometimes|required|integer',
                    'sender_company_user_id' => 'sometimes|required|integer',
                ]);
                $arr = $request->only('sender_company_id', 'sender_company_role_id', 'sender_company_user_id');
                if ($arr) {
                    if ($noteExists = $this->companyInfo(
                        $request->get('company_id'),
                        $request->only(['sender_company_id' , 'sender_company_role_id' , 'sender_company_user_id' , 'company_user'])))
                        return $noteExists;
                   return $arr;
                }
                return false;

                break;
            case "citizen_docs":
                $this->validate($request, [
                    'name' => 'sometimes|required|min:2,max:255',
                    'region_id' => 'sometimes|required|integer',
                    'address' => 'sometimes|required|min:2,max:255',
                    'company_user' => 'sometimes|required|integer'
                ]);
                $arr = $request->only('name', 'region_id', 'address');
                if ($arr) {
                    $check = senderCompany::whereHas('roles' , function ($q) use ($request) {
                        $q->whereHas('users' , function ($q) use ($request) {
                            $q->where(DB::raw("1") , "1" );
                            if ($request->has('sender_company_user_id'))
                                $q->where('id' ,$request->get('sender_company_user_id'));
                        })->where(DB::raw("1") , "1" );
                        if ($request->has('sender_company_role_id'))
                            $q->where('id' ,$request->get('sender_company_role_id'));
                    })
                        ->where('company_id' , $request->get('company_id'));
                    if ($request->has('company_user')) $check
                        ->where('id', $request->get('sender_company_id'));
                    $bool = $check->exists();
                    if (!$bool) return $this->errorResponse(trans('response.unProcess') ,422);
                    return $arr;
                }
                return false;
                break;

            case "in_company_docs":
                $this->validate($request, [
                    'from_in_our_company' => 'sometimes|required|integer',
                    'to_in_our_company' => 'sometimes|required|integer'
                ]);
                $arr = $request->only('from_in_our_company', 'to_in_our_company');
                if ($arr){

                    if ($noteExists = $this->companyInfo(
                        $request->get('company_id'),
                        $request->only(['from_in_our_company' , 'to_in_our_company' ])))
                        return $noteExists;
                    return $arr;
                }

                return false;
                break;
        }
    }
}
