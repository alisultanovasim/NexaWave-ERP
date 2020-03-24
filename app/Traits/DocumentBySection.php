<?php


namespace App\Traits;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Entities\senderCompany;

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

                if ($noteExists = $this->companyInfo(
                    $request->get('company_id'),
                    $request->only(['sender_company_id' , 'sender_company_role_id' , 'sender_company_user_id' , 'company_user'])))
                        return $noteExists;

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
                    $request->only(['region_id' , 'company_user' ])))
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
                    if ($noteExists = $this->companyInfo(
                        $request->get('company_id'),
                        $request->only(['region_id' , 'company_user'])))
                        return $noteExists;
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
