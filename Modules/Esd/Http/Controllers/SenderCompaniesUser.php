<?php


namespace Modules\Esd\Http\Controllers;


use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Entities\senderCompany;
use Modules\Entities\senderCompanyRole;
use Modules\Entities\senderCompanyUser;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controller;

class SenderCompaniesUser extends  Controller
{
    use  ApiResponse  ,ValidatesRequests;

    public function index(Request $request){
        $this->validate($request , [
            'company_id'=>'required|integer',
            'user_id'=>'required|integer' ,
            'sender_company_id'=>'required|integer',
            'name'=>'sometimes|required|max:255',
            'role_id' => 'sometimes|Required|integer'
        ]);
        try{
            $check = senderCompany::where('id' , $request->sender_company_id)
                ->where('company_id' , $request->company_id)
                ->exists();
            if (!$check) return $this->errorResponse(trans('apiResponse.senderCompanyNotFound'));

            $users = senderCompanyUser::where('sender_company_id' , $request->sender_company_id);
            if ($request->has('name')) $users->where('name' , 'like' , $request->name . "%");
            if ($request->has('role_id')) $users->where('sender_company_role_id' , $request->role_id);
            $users = $users->get(['id' , 'name']);
            return $this->successResponse($users);
        }catch (\Exception $e){
            return $this->errorResponse(trans('apiResponse.tryLater') , Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function store(Request $request){
        $this->validate($request , [
            'company_id'=>'required|integer',
            'user_id'=>'required|integer' ,
            'sender_company_id'=>'required|integer',
            'sender_company_role_id'=>'required|integer',
            'name'=>'required|max:255'
        ]);
        try{
            $check = senderCompany::where('id' , $request->sender_company_id)
                ->where('company_id' , $request->company_id)
                ->exists();
            if (!$check) return $this->errorResponse(trans('apiResponse.senderCompanyNotFound'));

            $check = senderCompanyRole::where('id' , $request->sender_company_role_id)
                ->where('sender_company_id' , $request->sender_company_id)
                ->exists();
            if (!$check) return $this->errorResponse(trans('apiResponse.senderCompanyRoleNotFound'));

            senderCompanyUser::create($request->only('sender_company_id' , 'sender_company_role_id' , 'name'));
            return $this->successResponse('OK');
        }catch (\Exception $e){
            return $this->errorResponse(trans('apiResponse.tryLater') , Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function update(Request $request){
        $this->validate($request , [
            'company_id'=>'required|integer',
            'user_id'=>'required|integer' ,
            'sender_company_role_id'=>'sometimes|required|integer',
            'name'=>'sometimes|required|max:255',
            'sender_company_user_id'=>'required|integer'
        ]);
        try{
            $user = senderCompanyUser::where('id' ,$request->sender_company_user_id)
                ->first(['id' , 'sender_company_id']);
            if (!$user) return $this->errorResponse(trans('apiResponse.senderCompanyRoleNotFound'));

            $check = senderCompany::where('id' ,$user->sender_company_id)
                ->where('company_id' , $request->comapny_id )
                ->exists();
            if (!$check) return $this->errorResponse(trans('apiResponse.senderCompanyRoleNotFound'));

            if ($request->has('sender_company_role_id')){
                $check = senderCompanyRole::where('id' ,$user->sender_company_id)
                    ->where('company_id' , $request->comapny_id )
                    ->exists();
                if (!$check) return $this->errorResponse(trans('apiResponse.senderCompanyRoleNotFound'));
            }
            senderCompanyUser::where('id' ,$request->sender_company_user_id)->update($request->only('sender_company_role_id' , 'name'));

            return $this->successResponse('OK');

        }catch (\Exception $e) {
            return $this->errorResponse(trans('apiResponse.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function delete(Request $request){
        $this->validate($request , [
            'company_id'=>'required|integer',
            'user_id'=>'required|integer' ,
            'sender_company_user_id'=>'required|integer'
        ]);
        try {
            $user = senderCompanyUser::where('id', $request->sender_company_user_id)
                ->first(['sender_company_id']);
            if (!$user) return $this->errorResponse(trans('apiResponse.senderCompanyRoleNotFound'));

            $check = senderCompany::where('id' ,$user->sender_company_id)
                ->where('company_id' , $request->comapny_id )
                ->exists();
            if (!$check) return $this->errorResponse(trans('apiResponse.senderCompanyRoleNotFound'));
            senderCompanyUser::where('id', $request->sender_company_user_id)->delete();
            return $this->successResponse('OK');

        }catch (\Exception $e) {
            return $this->errorResponse(trans('apiResponse.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    public function getAllRoles(Request $request){
        $this->validate($request , [
           'company_id'=>'required|integer',
           'user_id'=>'required|integer',
            'sender_company_id'=>'required|integer',
            'name'=>'sometimes|required|max:255'

        ]);
        try{
            $check = senderCompany::where('id' , $request->sender_company_id)
                ->where('company_id' , $request->company_id)
                ->exists();
            if (!$check) return $this->errorResponse(trans('apiResponse.senderCompanyNotFound'));
            $roles = senderCompanyRole::where('sender_Company_id' , $request->sender_company_id);
            if ($request->has('name')) $roles->where('name' , 'like' , $request->name . "%");
            $roles = $roles->get(['id' , 'name']);
            return $this->successResponse($roles);
        }catch (\Exception $e){
            return $this->errorResponse(trans('apiResponse.tyrLater') , Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function storeRole(Request $request){
        $this->validate($request , [
           'company_id'=>'required|integer',
           'user_id'=>'required|integer',
            'sender_company_id'=>'required|integer',
            'name'=>'required|max:255'
        ]);
        try{
            $check = senderCompany::where('id' , $request->sender_company_id)
                ->where('company_id' , $request->company_id)
                ->exists();
            if (!$check) return $this->errorResponse(trans('apiResponse.senderCompanyNotFound'));
            senderCompanyRole::create($request->only('sender_company_id', 'name'));
            return $this->successResponse('OK');
        }catch (\Exception $e){
            return $this->errorResponse(trans('apiResponse.tyrLater') , Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function updateRole(Request $request){
        $this->validate($request , [
           'company_id'=>'required|integer',
           'user_id'=>'required|integer',
            'sender_company_role_id'=>'required|integer',
            'name'=>'required|integer'
        ]);
        try{

            $role = senderCompanyRole::where('id' , $request->sender_company_role)->first(['id' , 'sender_company_id']);
            if (!$role) return $this->errorResponse(trans('apiResponse.senderCompanyRoleNotFound'));

            $check = senderCompany::where('id' , $role->sender_company_id)
                ->where('company_id' , $request->company_id)
                ->exists();
            if (!$check) return $this->errorResponse(trans('apiResponse.senderCompanyNotFound'));

            senderCompanyRole::where('id' , $role->id)->updete(['name'=> $request->name]);
            return $this->successResponse('OK');
        }catch (\Exception $e){
            return $this->errorResponse(trans('apiResponse.tyrLater') , Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function deleteRole(Request $request){
        $this->validate($request , [
           'company_id'=>'required|integer',
           'user_id'=>'required|integer',
        ]);
        try{
            $role = senderCompanyRole::where('id' , $request->sender_company_role)->first(['id' , 'sender_company_id']);
            if (!$role) return $this->errorResponse(trans('apiResponse.senderCompanyRoleNotFound'));

            $check = senderCompany::where('id' , $role->sender_company_id)
                ->where('company_id' , $request->company_id)
                ->exists();
            if (!$check) return $this->errorResponse(trans('apiResponse.senderCompanyNotFound'));

            senderCompanyRole::where('id' , $role->id)->delete();
            return $this->successResponse('OK');

        }catch (\Exception $e){
            return $this->errorResponse(trans('apiResponse.tyrLater') , Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}
