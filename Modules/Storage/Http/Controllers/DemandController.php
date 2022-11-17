<?php

namespace Modules\Storage\Http\Controllers;

use App\Models\User;
use App\Traits\ApiResponse;
use App\Traits\UserInfo;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Hr\Entities\Employee\Employee;
use Modules\Storage\Entities\ArchiveDocument;
use Modules\Storage\Entities\Demand;
use Modules\Storage\Entities\DemandCorrect;
use Modules\Storage\Entities\DemandItem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class DemandController extends Controller
{
    use ApiResponse, ValidatesRequests,UserInfo;

    public function index(Request $request)
    {
        $this->validate($request, [
            'per_page' => ['nullable', 'integer'],
//            'product_id' => ['nullable', 'integer'],
//            'employee_id' => ['required', 'integer']
        ]);
        $per_page=$request->per_page ?? 10;

        $demandCreatedByUser=Demand::query()
            ->with([
                'items'
            ])
            ->where([
                'employee_id'=>$this->getEmployeeId($request->company_id),
                'company_id'=>$request->company_id,
                'status'=>Demand::STATUS_WAIT,
                'edit_status'=>true,
            ])
            ->paginate($per_page);
        return $this->dataResponse(['createdByUserDemands'=>$demandCreatedByUser],200);
    }

    public function directedToUserDemandList(Request $request)
    {
        $per_page=$request->per_page ?? 10;
        $user=User::query()
            ->where('id',Auth::id())
            ->with('roles')
            ->first();
        $roleIds=[];
        $progress_status=2;
        foreach ($user['roles'] as $role){
            array_push($roleIds,$role['id']);
        }
         if(in_array(Demand::FINANCIER_ROLE,$roleIds)){
             $progress_status=2;
         }
        else if(in_array(Demand::DIRECTOR_ROLE,$roleIds)){
            $progress_status=3;
        }

        else if(in_array(Demand::PURCHASED_ROLE,$roleIds)){
            $progress_status=4;
        }
        $demands=Demand::query()
            ->with(['items','employee.user:id,name'])
            ->where([
                'progress_status'=>$progress_status,
                'status'=>Demand::STATUS_WAIT,
                'edit_status'=>true
            ])
            ->paginate($per_page);

        return $this->dataResponse($demands,200);
    }

//    public function getSentToEquipmentDemands()
//    {
//
//        $user=$this->getUserRoles();
//        $roleIds=[];
//        foreach ($user['roles'] as $role){
//            array_push($roleIds,$role['id']);
//        }
//
//        if (in_array(Demand::SUPPLIER_ROLE,$roleIds)){
//            $demands=Demand::query()
//                ->with(['items','employee.user:id,name'])
//                ->where([
//                    'progress_status'=>1,
//                    'is_sent'=>2
//                ])->get();
//        }
//        return $this->dataResponse($demands,200);
//
//    }

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


        DB::beginTransaction();

        try {
            $demand=Demand::create([
                'name' => $request->name,
                'description' => $request->description,
                'employee_id' => $this->getEmployeeId($request->company_id),
                'company_id' => $request->company_id,
                'status' =>Demand::STATUS_WAIT,
                'progress_status' =>1,
            ]);

            if ($request->hasFile('attachment')){
                $demand->attachment=$this->uploadFile($request->company_id,$request->file('attachment'));
            }
            $demand->save();

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
                $demand->attachment=$this->uploadFile($request->company_id,$request->file('attachment'));
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
            return $this->successResponse($demand,\Symfony\Component\HttpFoundation\Response::HTTP_OK);
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

        $demand->delete();
        return $this->successResponse('ok');

    }


    public function send(Request $request,$id)
    {
        $demand=Demand::query()->findOrFail($id);
        $roles=$this->getUserRoles();

        $roleIds=[];

        foreach ($roles['roles'] as $role){
            array_push($roleIds,$role['id']);
        }

        if ($this->getEmployeeId($request->company_id)==$demand->employee_id){
            $demand->is_sent=2;
            $demand->progress_status=2;
            $demand->edit_status=false;
            $demand->save();
            return $this->successResponse(['message'=>trans('response.theDemandWasSentSuccessfully')],Response::HTTP_OK);
        }
//        else if(in_array(Demand::FINANCIER_ROLE,$roleIds)){
//            $demand->progress_status=3;
//            $demand->save();
//            return $this->successResponse(['message'=>trans('response.theDemandWasSentSuccessfullyByFinancier')],Response::HTTP_OK);
//
//        }
//
//        else if(in_array(Demand::DIRECTOR_ROLE,$roleIds)){
//            $demand->progress_status=4;
//            $demand->save();
//            return $this->successResponse(['message'=>trans('response.theDemandWasSentSuccessfullyByDirector')],Response::HTTP_OK);
//
//        }

        }

    public function sendToCorrection(Request $request,$id)
    {
        $this->validate($request,[
           'description'=>'string|nullable',
           'employee_id'=>'integer|required',
        ]);
        $demand=Demand::query()->findOrFail($id);

        DB::beginTransaction();

        try {
            $demand->update(['edit_status'=>false]);

            $correction=new DemandCorrect();
            $correction->from_id=$this->getEmployeeId($request->company_id);
            $correction->role_id=Demand::SUPPLIER_ROLE;
            $correction->demand_id=$id;
            $correction->description=$request->description;
            $correction->save();

            DB::commit();
            return $this->successResponse($correction,200);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse($e->getMessage(),Response::HTTP_BAD_REQUEST);
        }

    }

    public function getSentToCorrectionDemands(Request $request)
    {
        $per_page=$request->per_page ?? 10;
        $roles=$this->getUserRoles();
        $roleIds=[];
        foreach ($roles['roles'] as $role){
            array_push($roleIds,$role['id']);
        }
        $demands=DemandCorrect::query()
            ->with(['demand','demand.employee.user:id,name'])
            ->where('role_id',Demand::SUPPLIER_ROLE)
            ->paginate($per_page);
        return $this->dataResponse($demands,Response::HTTP_OK);
    }

//    public function takeDemand(Request $request,$id)
//    {
//       $demand= Demand::query()->findOrFail($id);
//       $demand->is_took=true;
//       $demand->took_by=$this->getEmployeeId($request->company_id);
//       $demand->save();
//
//       return $this->successResponse(['message'=>trans('response.theDemandWasTookByPresentUser')]);
//    }

//    public function getTakenDemands(Request $request)
//    {
//        $per_page=$request->per_page ?? 10;
//        $demands=Demand::query()
//            ->with(['items','employee.user:id,name'])
//            ->where([
//                'took_by'=>$this->getEmployeeId($request->company_id),
//                'progress_status'=>1,
//                'type_of_doc'=>Demand::DRAFT
//            ])
//            ->paginate($per_page);
//        return $this->dataResponse($demands,200);
//
//    }

    public function confirmOrReject(Request $request,$demandId)
    {
        $demand=Demand::query()->findOrFail($demandId);
        $user=$this->getUserRoles();
        $roleIds=[];
        foreach ($user['roles'] as $role){
            array_push($roleIds,$role['id']);
        }

        if ($request->status==0){

            if (in_array(Demand::DIRECTOR_ROLE,$roleIds))
                $userRole=Demand::DIRECTOR_ROLE;
            else if (in_array(Demand::FINANCIER_ROLE,$roleIds))
                $userRole=Demand::FINANCIER_ROLE;
            DB::beginTransaction();

            try {
                $demand=Demand::query()->findOrFail($demandId);
                $demand->status=Demand::STATUS_REJECTED;
                $demand->save();

                $archiveDocument=new ArchiveDocument();
                $archiveDocument->demand_id=$demand->id;
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
        else{
            if (in_array(Demand::DIRECTOR_ROLE,$roleIds)){
                if ($demand->status==Demand::STATUS_WAIT){
                    $demand->update(['status'=>Demand::STATUS_CONFIRMED]);
                    $demand->progress_status=4;
                    $demand->save();

                    $message=trans('response.theDemandAcceptedByDirector');
                    $code=200;
                }

                else{
                    $message=trans('response.theDemandAlreadyAccepted');
                    $code=400;
                }
            }
            else if (in_array(Demand::FINANCIER_ROLE,$roleIds)){
                $demand->progress_status=3;
                $demand->save();
                $message=trans('response.theDemandConfirmedByFinancier');
                $code=200;
            }
            else{
                $message=trans('response.theDemandAlreadyConfirmed');
                $code=200;
            }
            return $this->successResponse($message,$code);
        }
    }


    public function uploadFile($company_id, $file, $str = 'documents')
    {
        if ($file instanceof UploadedFile) {
            return $file->store("/demand/$company_id/$str");
        }

        return null;
    }

    public function filePath($path)
    {
        return storage_path('app/public/') .$path;
    }

    public function downloadAttachmentFile(Request $request)
    {
        // Check if file exists in app/storage/file folder
        $file_path = $this->filePath($request->path);
        if (file_exists($file_path))
        {
            // Send Download
            return \response()->download($file_path, $request->attachment, [
                'Content-Length: '. filesize($file_path)
            ]);
        }
        else
        {
            // Error
            exit('Requested file does not exist on our server!');
        }
    }

    public function getUserRoles()
    {
        return User::query()
            ->where('id',Auth::id())
            ->with('roles')
            ->first();
    }

    function getEmployeeId($companyId){
        return Employee::query()
            ->where([
                'user_id'=>Auth::id(),
                'company_id'=>$companyId
            ])
            ->first()['id'];
    }
}
