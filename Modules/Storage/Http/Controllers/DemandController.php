<?php

namespace Modules\Storage\Http\Controllers;

use App\Models\User;
use App\Models\UserRole;
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
use Modules\Storage\Entities\ArchiveDemand;
use Modules\Storage\Entities\ArchiveDocument;
use Modules\Storage\Entities\Demand;
use Modules\Storage\Entities\DemandAssignment;
use Modules\Storage\Entities\DemandCorrect;
use Modules\Storage\Entities\DemandItem;
use Modules\Storage\Entities\Product;
use Modules\Storage\Entities\ProductColor;
use Modules\Storage\Entities\ProductKind;
use Modules\Storage\Entities\ProductModel;
use Modules\Storage\Entities\ProductTitle;
use Modules\Storage\Entities\PurchaseProduct;
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
            ->with(['items'])
            ->where([
                'employee_id'=>$this->getEmployeeId($request->company_id),
                'company_id'=>$request->company_id,
                'status'=>Demand::STATUS_WAIT,
            ])
            ->paginate($per_page);
        return $this->dataResponse(['createdByUserDemands'=>$demandCreatedByUser],200);
    }

    public function getSentToCorrectionDemands(Request $request)
    {
        $per_page=$request->per_page ?? 10;
        $demands=Demand::query()
            ->with(['items'])
            ->whereHas('corrects',function ($q) use ($request){
                $q->where('to_id',$this->getEmployeeId($request->company_id));
            })
            ->paginate($per_page);
        return $this->dataResponse($demands,Response::HTTP_OK);
    }

    public function directedToUserDemandList(Request $request)
    {
        $per_page=$request->per_page ?? 10;
        $user=User::query()
            ->where('id',Auth::id())
            ->with('roles')
            ->first();
        $roleIds=[];
        foreach ($user['roles'] as $role){
            array_push($roleIds,$role['id']);
        }
        if(in_array(43,$roleIds))
            $progress_status=2;
        else if(in_array(25,$roleIds))
            $progress_status=3;
        else if(in_array(8,$roleIds))
            $progress_status=4;
        else if(in_array(42,$roleIds))
            $progress_status=5;
        $demands=Demand::query()
            ->with(['items'])
            ->where([
                'progress_status'=>$progress_status
            ])
            ->paginate($per_page);

        return $this->dataResponse($demands,200);
    }

    public function getTakenDemands(Request $request)
    {
        $per_page=$request->per_page ?? 10;
        $demands=Demand::query()
            ->with(['items'])
            ->where([
                'took_by'=>$this->getEmployeeId($request->company_id),
                'progress_status'=>1
            ])
            ->paginate($per_page);
        return $this->dataResponse($demands,200);

    }
    public function getSentToEquipmentDemands()
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
                ->with(['items'])
                ->where([
                    'is_sent'=>2
                ])->get();
        }
        return $this->dataResponse($demands,200);

    }

    public function store(Request $request)
    {
//        $this->validate($request,[
//            'name' => ['required', 'string'],
//            'description' => ['nullable', 'string'],
//            'attachment' => ['required','mimes:pdf,docx'],
//            'productInfo'=>['required','array'],
//            'productInfo.*.amount' => ['required', 'integer'],
//            'productInfo.*.title' => ['nullable', 'string','min:1'],//
//            'productInfo.*.title_id' => ['nullable', 'integer'],//
//            'productInfo.*.kind' => ['nullable', 'string', 'min:1'],//
//            'productInfo.*.kind_id' => ['nullable', 'integer'],//
//            'productInfo.*.model' => ['nullable', 'string','min:1'],
//            'productInfo.*.model_id' => ['nullable', 'integer'],
//        ]);

        $employee_id = Employee::where([
            ['user_id' , Auth::id()],
            ['company_id' , $request->get('company_id')]
        ])->first(['id']);

        DB::beginTransaction();

        try {
            $demand=Demand::create([
                'name' => $request->name,
                'type_of_doc' => Demand::DRAFT,
                'description' => $request->description,
                'attachment' => $request->attachment,
                'employee_id' => $employee_id->id,
                'company_id' => $request->company_id,
                'status' =>Demand::STATUS_WAIT,
                'progress_status' =>1,
            ]);

            if ($request->hasFile('attachment')){
                $demand->attachment=$this->uploadImage($request->company_id,$request->attachment);
            }

            foreach ($request->productInfo as $item){
                $demandItem=new DemandItem();
                $demandItem->demand_id=$demand->id;
                $demandItem->amount=$item['amount'];
                $demandItem->title=$item['title'];
                $demandItem->title_id=$item['title_id'];
                $demandItem->kind=$item['kind'];
                $demandItem->kind_id=$item['kind_id'];
                $demandItem->model=$item['model'];
                $demandItem->model_id=$item['model_id'];
                $demandItem->mark=$item['mark'];
                $demandItem->save();
            }

            DB::commit();
            return $this->successResponse($demand,\Symfony\Component\HttpFoundation\Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse($e->getMessage(), \Symfony\Component\HttpFoundation\Response::HTTP_BAD_REQUEST);
        }

    }

    public function show(Request $request, $id)
    {
        $demands = Demand::with([
            'items',
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
           'attachment' => ['nullable','mimes:pdf,docx'],
           'productInfo'=>['nullable','array'],
           'productInfo.*.amount'=>['numeric','nullable'],
           'productInfo.*.title'=>['string','nullable','min:1'],
           'productInfo.*.title_id'=>['integer','nullable'],
           'productInfo.*.kind'=>['string','nullable','min:1'],
           'productInfo.*.kind_id'=>['integer','nullable'],
           'productInfo.*.model'=>['string','nullable','min:1'],
           'productInfo.*.model_id'=>['integer','nullable'],
        ]);

        DB::beginTransaction();

        try {
            $demand = Demand::query()->findOrFail($id);

            if (!$demand)
                return $this->errorResponse(trans('response.demandNotFound'), 404);

            else if ($demand->status != Demand::STATUS_WAIT)
                return $this->errorResponse(trans('response.cannotUpdateStatusError'), 422);
            else if ($demand->progress_status > 2)
                return $this->errorResponse(trans('response.demandAlreadyOnProgress'), 400);

            $demand->update([
                'name'=>$request->name,
                'description'=>$request->description,
            ]);


            if ($request->hasFile('attachment')){
                $demand->attachment=$this->uploadImage($request->company_id,$request->file('attachment'));
            }
            $demand->save();

            foreach ($request->get('productInfo') as $value){
                $product=[
                    'title_id'=>$value['title'],
                    'title'=>$value['title'],
                    'kind'=>$value['kind'],
                    'mark'=>$value['mark'],
                    'model'=>$value['model'],
                    'amount'=>$value['amount'],
                ];

                DemandItem::query()->findOrFail($value['productId'])->update($product);
            }
            DB::commit();
            return $this->successResponse($demand,\Symfony\Component\HttpFoundation\Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse($e->getMessage(), \Symfony\Component\HttpFoundation\Response::HTTP_BAD_REQUEST);
        }
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
        $roles=$this->getUserRoles();
        $roleIds=[];
        foreach ($roles[0]['roles'] as $role){
            array_push($roleIds,$role['id']);
        }
        if (in_array(Demand::DIRECTOR_ROLE,$roleIds))
            $userRole=Demand::DIRECTOR_ROLE;
        else if (in_array(Demand::SUPPLIER_ROLE,$roleIds))
            $userRole=Demand::SUPPLIER_ROLE;
        else if (in_array(Demand::FINANCIER_ROLE,$roleIds))
            $userRole=Demand::FINANCIER_ROLE;
        DB::beginTransaction();

        try {
            $demand=Demand::query()->findOrFail($id);
            $demand->status=Demand::STATUS_REJECTED;
            $demand->save();

            $archiveDocument=new ArchiveDocument();
            $archiveDocument->document_id=$demand->id;
            $archiveDocument->document_type=ArchiveDocument::DEMAND_TYPE;
            $archiveDocument->employee_id=$this->getEmployeeId($request->company_id);
            $archiveDocument->role_id=$userRole;
            $archiveDocument->reason=$request->reason;
            $archiveDocument->status=ArchiveDocument::REJECTED_STATUS;
            $archiveDocument->save();
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

            $demand->update(['progress_status'=>2]);


            DB::commit();
            return $this->successResponse($correction,201);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse($e->getMessage(),Response::HTTP_BAD_REQUEST);
        }

    }

    public function takeDemand(Request $request,$id)
    {
       $demand= Demand::query()->findOrFail($id);
       $demand->is_took=true;
       $demand->took_by=$this->getEmployeeId($request->company_id);
       $demand->save();

       return $this->successResponse(['message'=>trans('response.theDemandWasTookByPresentUser')]);
    }

    public function confirm(Request $request,$demandId)
    {
        $demand=Demand::query()->findOrFail($demandId);
        $user=$this->getUserRoles();
        $roleIds=[];
        foreach ($user[0]['roles'] as $role){
            array_push($roleIds,$role['id']);
        }
        if (in_array(Demand::DIRECTOR_ROLE,$roleIds)){
            if ($demand->status==Demand::STATUS_WAIT){
                $demand->update(['status'=>Demand::STATUS_CONFIRMED]);

                $message=trans('response.theDemandAcceptedByDirector');
                $code=200;
            }

            else{
                $message=trans('response.theDemandAlreadyAccepted');
                $code=400;
            }
        }
          else if (in_array(Demand::SUPPLIER_ROLE,$roleIds)){
            $demand->update(['type_of_doc'=>Demand::NOT_DRAFT]);
            $message=trans('response.theDemandConfirmedBySailor');
            $code=200;
        }
        else{
            $message=trans('response.theDemandAlreadyConfirmed');
            $code=200;
        }
        $demand->progress_status=4;
        $demand->save();
      return $this->successResponse($message,$code);
    }

//    public function editBySupplier(Request $request,$id)
//    {
//        $this->validate($request,[
//            'description' => ['nullable', 'string'],
//            'amount' => ['nullable', 'numeric'],
//            'attachment' => ['nullable','mimes:pdf,docx'],
//            'productInfo'=>['nullable','array'],
//            'productInfo.*.title_id' => ['nullable', 'integer'],
//            'productInfo.*.kind_id' => ['nullable', 'integer'],
//            'productInfo.*.model_id' => ['nullable', 'integer'],
//            'productInfo.*.mark' => ['nullable', 'string','min:3'],
//        ]);
//
//        $demand=Demand::query()->findOrFail($id);
//        $demand->description=$request->description;
//        $demand->attachment=$request->attachment;
//        $demand->title_id=$request->productInfo[0]['title_id'];
//        $demand->amount=$request->productInfo[0]['amount'];
//        $demand->kind_id=$request->productInfo[0]['kind_id'];
//        $demand->model_id=$request->productInfo[0]['model_id'];
//        $demand->mark=$request->productInfo[0]['mark'];
//        $demand->save();
//
//        return $this->successResponse($demand,Response::HTTP_OK);
//    }

    public function getEmployeeId($companyId)
    {
        return Employee::query()
            ->where([
                'user_id'=>Auth::id(),
                'company_id'=>$companyId
            ])
            ->first()['id'];
    }

    public function getUserRoles()
    {
        return User::query()
            ->where('id',Auth::id())
            ->with('roles')
            ->get();
    }

    public function uploadImage($company_id, $file, $str = 'documents')
    {
        if ($file instanceof UploadedFile) {
            return $file->store("/demand/$company_id/$str");
        }

        return null;
    }
}
