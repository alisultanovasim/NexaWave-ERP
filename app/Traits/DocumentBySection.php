<?php


namespace App\Traits;


use App\Models\senderCompany;
use Illuminate\Support\Facades\DB;

trait DocumentBySection
{

    protected function saveBySection($request, $document, $tableName )
    {
        switch ($tableName) {
            case "structure_docs":
                $this->validate($request, [
                    'sender_company_id' => 'required|integer',
                    'sender_company_role_id' => 'sometimes|required|integer',
                    'sender_company_user_id' => 'sometimes|required|integer',
                    'company_user' => 'sometimes|required|integer'
                ]);
                $check = senderCompany::where('company_id', $request->company_id)->where('id', $request->sender_company_id)->exists();
                if (!$check) {
                    DB::rollBack();
                    return $this->errorResponse(trans('apiResponse.senderCompanyNotFound'));
                }
                if ($request->has('sender_company_role_id')) {
                    $check = DB::table('sender_company_roles')->where('id', $request->sender_company_role_id)->where('sender_company_id', $request->sender_company_id)->exists();
                    if (!$check) {
                        DB::rollBack();
                        return $this->errorResponse(trans('apiResponse.senderRoleNotFound'));
                    }
                }
                if ($request->has('sender_company_user_id')) {
                    $check = DB::table('sender_company_users')->where('id', $request->sender_company_user_id)->where('sender_company_id', $request->sender_company_id)->exists();
                    if (!$check) {
                        DB::rollBack();
                        return $this->errorResponse(trans('apiResponse.senderUserNotFound'));
                    }
                }
                return array_merge($request->only('sender_company_role_id', 'sender_company_id', 'sender_company_user_id'), ['document_id' => $document->id]);
                break;
            case "citizen_docs":
                $this->validate($request, [
                    'name' => 'required|min:2,max:255',
//                        'last_name' => 'required|min:2,max:255',
                    'region_id' => 'required|integer',
                    'address' => 'sometimes|required|min:2,max:255',
                    'company_user' => 'sometimes|required|integer'
                ]);
                if ($request->has('region_id')) {
                    $check = DB::table('regions')->where('id', $request->region_id)->exists();
                    if (!$check) {
                        DB::rollBack();
                        return $this->errorResponse(trans('apiResponse.regionNotFound'));
                    }
                }
                return array_merge($request->only('name', 'region_id', 'address'), ['document_id' => $document->id]);
                break;
            case "in_company_docs":
                $this->validate($request, [
                    'from_in_our_company' => 'required|integer',
                    'to_in_our_company' => 'required|integer'
                ]);
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
                    if ($request->has('sender_company_id')) {
                        $check = senderCompany::where('company_id', $request->company_id)->where('id', $request->sender_company_id)->exists();
                        if (!$check) {
                            DB::rollBack();
                            return $this->errorResponse(trans('apiResponse.senderCompanyNotFound'));
                        }
                    }
                    if ($request->has('sender_company_role_id')) {
                        $check = DB::table('sender_company_roles')->where('id', $request->sender_company_role_id)->where('sender_company_id', $request->sender_company_id)->exists();
                        if (!$check) {
                            DB::rollBack();
                            return $this->errorResponse(trans('apiResponse.senderRoleNotFound'));
                        }
                    }
                    if ($request->has('sender_company_user_id')) {
                        $check = DB::table('sender_company_users')->where('id', $request->sender_company_user_id)->where('sender_company_id', $request->sender_company_id)->exists();
                        if (!$check) {
                            DB::rollBack();
                            return $this->errorResponse(trans('apiResponse.senderUserNotFound'));
                        }
                    }
                   return $arr;
                }
                return false;

                break;
            case "citizen_docs":
                $this->validate($request, [
                    'name' => 'sometimes|required|min:2,max:255',
                    'region_id' => 'sometimes|required|integer',
                    'address' => 'sometimes|required|min:2,max:255',
                ]);
                $arr = $request->only('name', 'region_id', 'address');
                if ($arr) {
                    if ($request->has('sender_company_role_id')) {
                        $check = DB::table('regions')->where('id', $request->region_id)->exists();
                        if (!$check) {
                            DB::rollBack();
                            return $this->errorResponse(trans('apiResponse.regionNotFound'));
                        }
                    }
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
                if ($arr)
                   return $arr;
                return false;
                break;
        }
    }
}
