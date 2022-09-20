<?php

namespace Modules\Storage\Http\Controllers;

use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Modules\Hr\Entities\Employee\Employee;
use Modules\Plaza\Entities\Kind;
use Modules\Storage\Entities\ArchiveRejectedDemand;
use Modules\Storage\Entities\Demand;
use Modules\Storage\Entities\DemandAssignment;
use Modules\Storage\Entities\DemandCorrect;
use Modules\Storage\Entities\Product;
use Modules\Storage\Entities\ProductColor;
use Modules\Storage\Entities\ProductKind;
use Modules\Storage\Entities\ProductModel;
use Modules\Storage\Entities\ProductTitle;
use Modules\Storage\Entities\Unit;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class DemandController extends Controller
{
    use ApiResponse, ValidatesRequests;

    public function index(Request $request)
    {
        $this->validate($request, [
            'per_page' => ['nullable', 'integer'],
//            'product_id' => ['nullable', 'integer'],
//            'employee_id' => ['required', 'integer']
        ]);
        $per_page=$request->per_page ?? 10;

        $demandCreatedByUser=Demand::query()
            ->where([
                'employee_id'=>$this->getEmployeeId($request->company_id),
                'company_id'=>$request->company_id,
                'status'=>Demand::STATUS_WAIT,
            ])
            ->paginate($per_page);
        return $this->dataResponse(['createdByUserDemands'=>$demandCreatedByUser],200);
    }

    public function getSentToEditDemands(Request $request)
    {
        $per_page=$request->per_page ?? 10;
        $demands=Demand::query()->where('edit_status',1)->paginate($per_page);
        if (!$demands){
            return $this->dataResponse([],Response::HTTP_OK);
        }
        return $this->dataResponse($demands,Response::HTTP_OK);
    }

    public function directedToUserDemandList(Request $request): \Illuminate\Http\JsonResponse
    {
        $per_page=$request->per_page ?? 10;
        $department_id=DB::table('employee_contracts')
//            ->leftJoin('departments','departments.id','=','employee_contracts.department_id')
            ->where('employee_contracts.employee_id',$this->getEmployeeId($request->company_id))
            ->distinct()
            ->get('department_id')->toArray();

        switch ($department_id[0]->department_id){
            case 1:
                $progress_status=4;
                break;
            case 8:
                $progress_status=3;
                break;
            case 43:
                $progress_status=2;
        }

        $demands=DemandAssignment::query()
            ->where([
                'employee_id'=>$this->getEmployeeId($request->company_id)
            ])
            ->with([
                        'demand.product.model',
                    ])
            ->whereHas('demand',function ($q1) use ($progress_status){
                $q1->where([
                    'status'=>Demand::STATUS_WAIT,
                    'progress_status'=>$progress_status]);
            })
            ->paginate($per_page);

        return $this->dataResponse($demands,200);
    }

    public function getSentToequipmentDemands()
    {
        $user=User::query()
            ->where('id',Auth::id())
            ->with('roles')
            ->get();
        $roleIds=[];
        foreach ($user[0]['roles'] as $role){
            array_push($roleIds,$role['id']);
        }
        if (in_array(43,$roleIds)){
            $demands=Demand::query()
                ->where([
                    'is_sent'=>2
                ])->get();
        }
        return $this->dataResponse($demands,200);

    }

    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->validate($request,[
            'name' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'amount' => ['required', 'numeric'],
            'attachment' => ['required','mimes:pdf,docx'],
            'productInfo'=>['required','array'],
            'productInfo.*.title' => ['nullable', 'string','min:1'],//
            'productInfo.*.title_id' => ['nullable', 'integer'],//
            'productInfo.*.kind' => ['nullable', 'string', 'min:1'],//
            'productInfo.*.kind_id' => ['nullable', 'integer'],//
            'productInfo.*.model' => ['nullable', 'string','min:1'],
            'productInfo.*.model_id' => ['nullable', 'integer'],
        ]);

        $employee_id = Employee::where([
            ['user_id' , Auth::id()],
            ['company_id' , $request->get('company_id')]
        ])->first(['id']);

        $demand=Demand::create([
            'name' => $request->name,
            'title' => $request->productInfo[0]['title'],
            'title_id' => $request->productInfo[0]['title_id'],
            'kind' => $request->productInfo[0]['kind'],
            'kind_id' => $request->productInfo[0]['kind_id'],
            'model' => $request->productInfo[0]['model'],
            'model_id' => $request->productInfo[0]['model_id'],
            'type_of_doc' => Demand::DRAFT,
            'description' => $request->description,
            'attachment' => $request->attachment,
            'amount' => $request->amount,
            'employee_id' => $employee_id->id,
            'company_id' => $request->company_id,
            'status' =>Demand::STATUS_WAIT,
            'progress_status' =>1,
        ]);

        if ($request->hasFile('attachment')){
            $demand->attachment=$this->uploadImage($request->company_id,$request->attachment);
        }


        return $this->successResponse($demand,\Symfony\Component\HttpFoundation\Response::HTTP_CREATED);
    }

    public function show(Request $request, $id)
    {
        $demands = Demand::with([
            'employee',
            'assignment',
            'assignment.employee',
            'assignment.items',
            'employee.user',
        ])
            ->where('company_id', $request->get('company_id'))
            ->where('id', $id)
            ->first();
        if (!$demands)
            return $this->errorResponse(trans('response.demandNotFound'), 404);
        return $this->successResponse($demands);
    }

    public function update(Request $request, $id)
    {
        $this->validate($request,[
           'name'=>['string','nullable','min:1'],
           'description'=>['string','nullable','min:1'],
           'attachment'=>['integer','nullable','min:1'],
           'productInfo'=>['nullable','array'],
           'productInfo.*.title'=>['string','nullable','min:1'],
           'productInfo.*.title_id'=>['integer','nullable'],
           'productInfo.*.kind'=>['string','nullable','min:1'],
           'productInfo.*.kind_id'=>['integer','nullable'],
           'productInfo.*.model'=>['string','nullable','min:1'],
           'productInfo.*.model_id'=>['integer','nullable'],
           'amount'=>['numeric','nullable'],
        ]);

        $demand = Demand::where('company_id', $request->get('company_id'))
            ->where('id', $id)
            ->where('employee_id', $this->getEmployeeId($request->company_id))
            ->first(['id', 'status','progress_status']);

        if (!$demand)
            return $this->errorResponse(trans('response.demandNotFound'), 404);

        else if ($demand->status != Demand::STATUS_WAIT)
            return $this->errorResponse(trans('response.cannotUpdateStatusError'), 422);
        else if ($demand->progress_status > 2)
            return $this->errorResponse(trans('response.demandAlreadyOnProgress'), 400);
        $demand->update([
            'name'=>$request->name,
            'description'=>$request->description,
            'amount'=>$request->amount,
            'attachment'=>$request->attachment,
            'title'=>$request->productInfo[0]['title'],
            'title_id'=>$request->productInfo[0]['title_id'],
            'kind'=>$request->productInfo[0]['kind'],
            'kind_id'=>$request->productInfo[0]['kind_id'],
            'model'=>$request->productInfo[0]['model'],
            'model_id'=>$request->productInfo[0]['model_id'],
        ]);

        if ($request->hasFile('attachment')){
            $demand->attachment=$this->uploadImage($request->company_id,$request->file('attachment'));
        }

        return $this->successResponse('ok');


    }

    public function delete(Request $request,$id)
    {

        $demand = Demand::where('company_id', $request->get('company_id'))
            ->where('id', $id)
            ->first(['id', 'status','progress_status','employee_id']);
        if (!isset($demand))
            return $this->errorResponse(trans('response.demandNotFound'), 404);
        else if ($demand->status != Demand::STATUS_WAIT)
            return $this->errorResponse(trans('response.demandStatusIsNotWait'), 400);
        else if ($demand->progess_status > 2)
            return $this->errorResponse(trans('response.demandAlreadyOnProgress'), 400);
        else if ($demand->employee_id!=$this->getEmployeeId($request->company_id))
            return $this->errorResponse(trans('response.userDontHaveAnAccessToDelete'),422);

        Demand::query()->where('id',$id)->delete();
        return $this->successResponse('ok');

    }

    public function reject(Request $request,$id): \Illuminate\Http\JsonResponse
    {
        $this->validate($request,[
           'reason'=>'nullable|string'
        ]);
        DB::beginTransaction();

        try {
            $demand=Demand::query()->findOrFail($id);
            $demand->status=Demand::STATUS_REJECTED;
            $demand->save();
            $archive=new ArchiveRejectedDemand();
            $archive->from_id=$this->getEmployeeId($request->company_id);
            $archive->demand_id=$id;
            $archive->reason=$request->reason;
            $archive->save();
            DB::commit();
            return $this->successResponse('The demand rejected!',200);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse($e->getMessage(),Response::HTTP_BAD_REQUEST);
        }

    }

    public function send($id)
    {
        $demand=Demand::query()->findOrFail($id);
        $demand->update([
           'is_sent'=>2,
            'edit_status'=>false
        ]);
        return $this->successResponse(['message'=>trans('response.theDemandWasSentSuccessfully')],Response::HTTP_OK);
    }

    public function sendToCorrection(Request $request,$id)
    {
        $this->validate($request,[
           'description'=>'string|nullable',
           'employee_id'=>'integer|required',
        ]);

        DB::beginTransaction();

        try {
            $correction=new DemandCorrect();
            $correction->from_id=$this->getEmployeeId($request->company_id);
            $correction->to_id=$request->employee_id;
            $correction->demand_id=$id;
            $correction->description=$request->description;
            $correction->save();

            $demand=Demand::query()
                ->where('id',$id)
                ->where('employee_id',$request->employee_id)->first();
            if ($demand){
                $demand->update(['edit_status'=>true]);
            }
            else{
                return $this->errorResponse(trans('response.demandNotFound'),Response::HTTP_NOT_FOUND);
            }


            DB::commit();
            return $this->successResponse($correction,201);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse($e->getMessage(),Response::HTTP_BAD_REQUEST);
        }

    }

    public function confirm($demandId)
    {
        $demand=Demand::query()->findOrFail($demandId);
        $user=User::query()
            ->where('id',Auth::id())
            ->with('roles')
            ->get();
        $roleIds=[];
        foreach ($user[0]['roles'] as $role){
            array_push($roleIds,$role['id']);
        }
        if (in_array(8,$roleIds)){
            if ($demand->status==Demand::STATUS_WAIT){
                $demand->update(['status'=>Demand::STATUS_CONFIRMED]);
                $demand->increment('progress_status',1);
                $message=trans('response.theDemandAcceptedByDirector');
                $code=200;
            }

            else{
                $message=trans('response.theDemandAlreadyAccepted');
                $code=400;
            }
        }
         if (in_array(41,$roleIds)){
            $demand->update(['type_of_doc'=>Demand::NOT_DRAFT]);
            $message=trans('response.theDemandConfirmedBySailor');
            $demand->increment('progress_status',1);
            $code=200;
        }
        else{
            $demand->increment('progress_status',1);
            $message=trans('response.theDemandConfirmed');
            $code=200;
        }
      return $this->successResponse($message,$code);
    }

    public function editBySupplier(Request $request,$id)
    {
        $this->validate($request,[
            'description' => ['nullable', 'string'],
            'amount' => ['nullable', 'numeric'],
            'attachment' => ['nullable','mimes:pdf,docx'],
            'productInfo'=>['nullable','array'],
            'productInfo.*.title_id' => ['nullable', 'integer'],
            'productInfo.*.kind_id' => ['nullable', 'integer'],
            'productInfo.*.model_id' => ['nullable', 'integer'],
            'productInfo.*.mark' => ['nullable', 'string','min:3'],
        ]);

        $demand=Demand::query()->findOrFail($id);
        $demand->description=$request->description;
        $demand->amount=$request->amount;
        $demand->attachment=$request->attachment;
        $demand->title_id=$request->productInfo[0]['title_id'];
        $demand->kind_id=$request->productInfo[0]['kind_id'];
        $demand->mark=$request->productInfo[0]['mark'];
        $demand->save();

        return $this->successResponse($demand,Response::HTTP_OK);
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

    public function uploadImage($company_id, $file, $str = 'documents')
    {
        if ($file instanceof UploadedFile) {
            return $file->store("/demand/$company_id/$str");
        }

        return null;
    }
}
