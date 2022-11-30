<?php

namespace Modules\Storage\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Hr\Entities\Employee\Employee;
use Modules\Storage\Entities\ArchiveDocument;
use Modules\Storage\Entities\Demand;
use Modules\Storage\Entities\DemandCorrect;
use Modules\Storage\Entities\DemandDraft;
use Modules\Storage\Entities\DemandDraftItem;
use Modules\Storage\Entities\DemandItem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class DemandDraftController extends Controller
{
    public function index(Request $request)
    {
        $this->validate($request, [
            'per_page' => ['nullable', 'integer'],
//            'product_id' => ['nullable', 'integer'],
//            'employee_id' => ['required', 'integer']
        ]);
        $per_page=$request->per_page ?? 10;

        $demandCreatedByUser=DemandDraft::query()
            ->with([
                'items',
                'employee'
            ])
            ->where([
                'employee_id'=>$this->getEmployeeId($request->company_id),
                'company_id'=>$request->company_id,
                'status'=>DemandDraft::STATUS_WAIT,
                'return_status'=>false,
            ])
            ->paginate($per_page);
        return $this->dataResponse(['createdByUserDemands'=>$demandCreatedByUser],200);
    }

    public function show($id)
    {
        $demandDraft=DemandDraft::query()->with(['items','employee.user:id,name'])->findOrFail($id);
        return $this->dataResponse($demandDraft,200);
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

        $employee_id = $this->getEmployeeId($request->company_id);
        DB::beginTransaction();

        try {
            $demanddraft=DemandDraft::create([
                'name' => $request->name,
                'description' => $request->description,
                'employee_id' => $employee_id,
                'company_id' => $request->company_id,
                'status' =>DemandDraft::STATUS_WAIT,
            ]);

            if ($request->hasFile('attachment')){
                $demanddraft->attachment=$this->uploadFile($request->company_id,$request->file('attachment'));
            }
            $demanddraft->save();

            foreach ($request->productInfo as $item){
                $demandItem=new DemandDraftItem();
                $demandItem->demand_draft_id=$demanddraft->id;
                $demandItem->amount=$item['amount'];
                $demandItem->title=$item['title'];
//                $demandItem->title_id=$item['title_id'];
                $demandItem->kind=$item['kind'];
//                $demandItem->kind_id=$item['kind_id'];
                $demandItem->model=$item['model'];
//                $demandItem->model_id=$item['model_id'];
                $demandItem->mark=$item['mark'];
                $demandItem->save();
            }

            DB::commit();
            return $this->successResponse($demanddraft,\Symfony\Component\HttpFoundation\Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse($e->getMessage(), \Symfony\Component\HttpFoundation\Response::HTTP_BAD_REQUEST);
        }

    }

    public function send(Request $request,$id)
    {

        $demandDraft=DemandDraft::query()->findOrFail($id);

        if ($this->getEmployeeId($request->company_id)!=$demandDraft->employee_id){
            $this->errorResponse(trans('youDontHaveAccess'),\Symfony\Component\HttpFoundation\Response::HTTP_BAD_REQUEST);
        }

        $demandDraft->update([
            'is_sent'=>true,
            'return_status'=>false
        ]);

        return $this->successResponse(trans('response.sentSuccessfully'),\Symfony\Component\HttpFoundation\Response::HTTP_OK);

    }

    public function getSent()
    {
//        $roles=$this->getUserRoles();
//        $roleIds=[];
//        foreach ($roles['roles'] as $role){
//            array_push($roleIds,$role['id']);
//        }
//        if (!in_array(DemandDraft::SUPPLIER_ROLE,$roleIds)){
//            $this->errorResponse(trans('response.youDontHaveAccess'),Response::HTTP_BAD_REQUEST);
//        }

            $demands=DemandDraft::query()
                ->with(['items','employee.user:id,name'])
                ->where([
                    'status'=>DemandDraft::STATUS_WAIT,
                    'return_status'=>0,
                    'is_sent'=>true
                ])->get();

        return $this->dataResponse($demands,200);

    }


    public function sendToCorrection(Request $request,$id)
    {
        $this->validate($request,[
            'description'=>'string|nullable',
            'employee_id'=>'integer|required',
        ]);
        DB::beginTransaction();

        try {
            $demandDraft=DemandDraft::query()->findOrFail($id);
            $demandDraft->return_status=true;
            $demandDraft->is_sent=false;
            $demandDraft->save();

            DB::commit();
            return $this->successResponse(trans('response.wasSentSuccessfully'),200);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse($e->getMessage(),Response::HTTP_BAD_REQUEST);
        }

    }

    public function getSentToCorrectionDrafts(Request $request)
    {
        try {
            $demandDraft=DemandDraft::query()->where([
                'return_status'=>true,
                'status'=>DemandDraft::STATUS_WAIT,
                'is_sent'=>false,
                'employee_id'=>$this->getEmployeeId($request->company_id)
            ])->with([
                'items',
                'employee'
            ])->get();

            DB::commit();
            return $this->dataResponse($demandDraft,Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse($e->getMessage(),Response::HTTP_BAD_REQUEST);
        }
    }

    public function delete(Request $request,$id)
    {

        $demandDraft = DemandDraft::where('company_id', $request->get('company_id'))
            ->where('id', $id)
            ->first(['id', 'status','employee_id']);
        if (!isset($demandDraft))
            return $this->errorResponse(trans('response.demandDraftNotFound'), 404);
        else if ($demandDraft->status != DemandDraft::STATUS_WAIT)
            return $this->errorResponse(trans('response.demandDraftStatusIsNotWait'), 400);
        else if ($demandDraft->employee_id!=$this->getEmployeeId($request->company_id))
            return $this->errorResponse(trans('response.userDontHaveAnAccessToDelete'),422);

        $demandDraft->delete();
        return $this->successResponse('ok');

    }

    public function update(Request $request, $id)
    {

//        $this->validate($request,[
//           'name'=>['string','nullable','min:1'],
//           'description'=>['string','nullable','min:1'],
//           'attachment' => ['nullable','mimes:pdf,docx'],
//           'productInfo'=>['nullable','array'],
//           'productInfo.*.amount'=>['numeric','nullable'],
//           'productInfo.*.title'=>['string','nullable','min:1'],
//           'productInfo.*.title_id'=>['integer','nullable'],
//           'productInfo.*.kind'=>['string','nullable','min:1'],
//           'productInfo.*.kind_id'=>['integer','nullable'],
//           'productInfo.*.model'=>['string','nullable','min:1'],
//           'productInfo.*.model_id'=>['integer','nullable'],
//        ]);

        DB::beginTransaction();

        try {
            $demandDraft = DemandDraft::query()->findOrFail($id);

            if ($demandDraft->status != DemandDraft::STATUS_WAIT)
                return $this->errorResponse(trans('response.cannotUpdateStatusError'), 422);

            $demandDraft->update([
                'name'=>$request->name,
                'description'=>$request->description,
            ]);


            if ($request->hasFile('attachment')){
                $demandDraft->attachment=$this->uploadFile($request->company_id,$request->file('attachment'));
            }
            $demandDraft->save();

            foreach ($request->get('productInfo') as $value){
                $product=[
                    'title_id'=>$value['title'],
                    'title'=>$value['title'],
                    'kind'=>$value['kind'],
                    'mark'=>$value['mark'],
                    'model'=>$value['model'],
                    'amount'=>$value['amount'],
                ];

                DemandDraftItem::query()->findOrFail($value['productId'])->update($product);
            }
            DB::commit();
            return $this->successResponse($demandDraft,\Symfony\Component\HttpFoundation\Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse($e->getMessage(), \Symfony\Component\HttpFoundation\Response::HTTP_BAD_REQUEST);
        }
    }

    public function confirmOrReject(Request $request,$id)
    {
        $demandDraft=DemandDraft::query()->findOrFail($id);
        $roles=$this->getUserRoles();
        $roleIds=[];
        foreach ($roles['roles'] as $role){
            array_push($roleIds,$role['id']);
        }

        if (in_array(DemandDraft::SUPPLIER_ROLE,$roleIds)){
            if ($request->status==0){
                DB::beginTransaction();

                try {
                    $demandDraft->status=DemandDraft::STATUS_REJECTED;
                    $demandDraft->save();

                    $archiveDocument=new ArchiveDocument();
                    $archiveDocument->demand_draft_id=$demandDraft->id;
                    $archiveDocument->employee_id=$this->getEmployeeId($request->company_id);
                    $archiveDocument->role_id=DemandDraft::SUPPLIER_ROLE;
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
            else if ($request->status==1){
                if ($demandDraft->status==DemandDraft::STATUS_WAIT){
                    $demandDraft->update(['status'=>DemandDraft::STATUS_CONFIRMED]);
                    $demandDraft->save();

                    $message=trans('response.theDemandDraftAcceptedBySupplier');
                    $code=200;
                }
                else{
                    $message=trans('response.theDemandDraftAlreadyAccepted');
                    $code=400;
                }
            }
        }
        else{
            $message=trans('response.youDontHaveAccess');
            $code=200;
        }
        return $this->successResponse($message,$code);
    }

    public function getAccepted()
    {
        $drafts=DemandDraft::query()
            ->with(['items'])
            ->where(['status'=>DemandDraft::STATUS_CONFIRMED])
            ->get();
        return response()->json(['data'=>$drafts],Response::HTTP_OK);
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

    public function getUserRoles()
    {
        return User::query()
            ->where('id',Auth::id())
            ->with('roles')
            ->first();
    }

    public function uploadFile($company_id, $file, $str = 'documents')
    {
        if ($file instanceof UploadedFile) {
            return $file->store("/demand_draft/$company_id/$str");
        }

        return null;
    }
}
