<?php

namespace Modules\Storage\Http\Controllers;

use App\Http\Requests\ProposeRequest;
use App\Models\User;
use App\Models\UserRole;
use App\Traits\ApiResponse;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\Hr\Entities\Employee\Employee;
use Modules\Storage\Entities\Demand;
use Modules\Storage\Entities\ProductColor;
use Modules\Storage\Entities\ProductKind;
use Modules\Storage\Entities\Propose;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

class ProposeController extends Controller
{
    use  ApiResponse, ValidatesRequests;
    public function index(Request $request)
    {
        $per_page=$request->per_page ?? 10;
        $proposes=Propose::query()
            ->where(['employee_id'=>$this->getEmployeeId($request->company_id),
                'status'=>Propose::STATUS_WAIT
            ])
            ->paginate($per_page);
        return $this->dataResponse($proposes,200);
    }

    public function show(Propose $propose)
    {
        return $this->dataResponse($propose,200);
    }

    /**
     * @throws \Throwable
     */
    public function store(ProposeRequest $proposeRequest)
    {
        $isSellerSpecialist=Auth::user()->roles()->find(8);

        if (!$isSellerSpecialist) return $this->errorResponse(trans('response.onlySalesSpecialistHaveAnAccess'),400);
        DB::beginTransaction();

        try {
            $propose=Propose::query()->create($proposeRequest->all());
            $propose->employee_id=$this->getEmployeeId($proposeRequest->company_id);
            $propose->save();
            if ($proposeRequest->hasFile('offer_file')){
                $propose->offer_file=$this->uploadImage($proposeRequest->company_id,$proposeRequest->offer_file);
            }

            DB::commit();
            return $this->successResponse($propose,200);
        }
        catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
        catch (\Throwable $throwable){
            DB::rollBack();
            throw $throwable;
        }
    }

    public function sendPropose($id)
    {
        $propose=Propose::query()->findOrFail($id);
        $propose->increment('progress_status',1);
        return $this->successResponse(['message'=>'Sent successfully'],200);
    }

    public function reject($id)
    {
        $propose=Propose::query()->findOrFail($id);
        $propose->update(['status'=>Demand::STATUS_REJECTED]);
        return $this->successResponse('The demand rejected!',200);
    }

    public function update(Request $request,$id)
    {
        $propose=Propose::query()->findOrFail($id);
        if ($propose->progress_status!=1)
            return $this->errorResponse(trans('response.theProposeIsOnProgress'),Response::HTTP_BAD_REQUEST);
        $propose->update($request->all());
        return $this->successResponse(['message'=>'Updated!'],200);

    }

    public function delete(Request $request,$id)
    {
        $departmentId=DB::table('employee_contracts')
            ->where('employee_id',$this->getEmployeeId($request->company_id))
            ->distinct()
            ->get('department_id');

        $propose=Propose::query()->findOrFail($id);

        if ($propose->employee_id!=$this->getEmployeeId($propose->company_id))
            return $this->errorResponse(trans('response.youDontHaveAccessToDelete'));
        if ($departmentId[0]->department_id==15)
            $propose->delete();
        else
            return $this->errorResponse(trans('response.youDontHaveAccessToDelete'),400);
        return response()->json(['message'=>trans('response.proposeDeleted')],200);
    }

    public function uploadImage($company_id, $file, $str = 'storages')
    {
        if ($file instanceof UploadedFile) {
            return $file->store("/documents/$company_id/$str");
        }

        return null;
    }
    public function getEmployeeId($companyId)
    {
        return Employee::query()
            ->where([
                'user_id'=>Auth::id(),
                'company_id'=>$companyId
            ])
            ->first()['id'];
    }
}
