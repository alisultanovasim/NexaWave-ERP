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
use Modules\Storage\Entities\ArchiveDemand;
use Modules\Storage\Entities\ArchiveDocument;
use Modules\Storage\Entities\ArchivePropose;
use Modules\Storage\Entities\Demand;
use Modules\Storage\Entities\DemandItem;
use Modules\Storage\Entities\ProductColor;
use Modules\Storage\Entities\ProductKind;
use Modules\Storage\Entities\ProposeCompany;
use Modules\Storage\Entities\ProposeCompanyDetail;
use Modules\Storage\Entities\ProposeDetail;
use Modules\Storage\Entities\Propose;
use Modules\Storage\Entities\ProposeDocument;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

class ProposeController extends Controller
{
    use  ApiResponse, ValidatesRequests;
    public function index(Request $request)
    {
        $per_page=$request->per_page ?? 10;
        $proposes=ProposeDocument::query()
            ->with('proposes')
            ->where(['employee_id'=>$this->getEmployeeId($request->company_id),
//                'progress_status'=>1
            ])
            ->paginate($per_page);
        return $this->dataResponse($proposes,200);
    }

    public function getDirectedToUserProposeList(Request $request)
    {
        $per_page=$request->per_page ?? 10;
        $roles=$this->getUserRoles();

        $roleIds=[];

        foreach ($roles['roles'] as $role){
            array_push($roleIds,$role['id']);
        }

        if(in_array(25,$roleIds)){
            $progress_status=2;
            $proposes=ProposeDocument::query()
                ->with(['proposes'])
                ->where(['employee_id'=>$this->getEmployeeId($request->company_id),'progress_status'=>1])
                ->orWhere('progress_status',$progress_status)
                ->paginate($per_page);
        }
        else if (in_array(8,$roleIds)){
            $progress_status=3;
            $proposes=ProposeDocument::query()
                ->with(['proposes'])
                ->where('progress_status',$progress_status)
                ->paginate($per_page);
        }
        return $this->dataResponse($proposes,200);

    }

    public function show( $id)
    {
        $propose=ProposeDocument::query()->where(['id'=>$id])->with('proposes')->get();
        return $this->dataResponse($propose,200);
    }

    /**
     * @throws \Throwable
     */
    public function store(ProposeRequest $proposeRequest)
    {
//        $isSellerSpecialist=Auth::user()->roles()->find(42);
//
//        if (!$isSellerSpecialist) return $this->errorResponse(trans('response.onlySalesSpecialistHaveAnAccess'),400);

        DB::beginTransaction();

        try {

            $proposeDocument=new ProposeDocument();
            $proposeDocument->demand_id=$proposeRequest->demand_id;
            $proposeDocument->description=$proposeRequest->description;
            $proposeDocument->employee_id=$this->getEmployeeId($proposeRequest->company_id);
            $proposeDocument->company_id=$proposeRequest->company_id;

            if ($proposeRequest->hasFile('offer_file')){
                $proposeDocument->offer_file=$this->uploadFile($proposeRequest->company_id,$proposeRequest->file('offer_file'));
            }
            $proposeDocument->save();

            $company_ids=[];

            foreach ($proposeRequest->proposeCompanyDetails as $detail){
                $proposeCompany=ProposeCompany::query()->firstOrCreate(['company_name'=>$detail['company_name']]);

                ProposeCompanyDetail::query()
                    ->firstOrCreate([
                        'propose_company_id'=>$proposeCompany->id,
                        'indicator'=>$detail['indicator'],
                        'value'=>$detail['value'],
                    ]);

                array_push($company_ids,$proposeCompany->id);
            }

            $propose_ids=[];
            foreach ($company_ids as $company_id){
                $propose=new Propose();
                $propose->propose_document_id=$proposeDocument->id;
                $propose->propose_company_id=$company_id;
                $propose->save();
                array_push($propose_ids,$propose->id);
            }


            foreach ($proposeRequest->demandProductDetails as $productDetail){
                $product=DemandItem::query()
                    ->where([
                        'title_id'=>$productDetail['title_id'],
                        'kind_id'=>$productDetail['kind_id'],
                        'model_id'=>$productDetail['model_id']
                    ])
                    ->firstOrFail('id');

                foreach ($propose_ids as $propose_id) {
                    $proposeDetails=new ProposeDetail();
                    $proposeDetails->amount=$productDetail['amount'];
                    $proposeDetails->price=$productDetail['price'];
                    $proposeDetails->demand_item_id=$product->id;
                    $proposeDetails->propose_id=$propose_id;
                    $proposeDetails->save();
                }
            }

            DB::commit();
            return $this->successResponse($proposeDocument,200);
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
        $propose=ProposeDocument::query()->findOrFail($id);
        if($propose->progress_status==1){
                $propose->progress_status=3;
                $propose->send_back=0;
                $propose->save();
            return $this->successResponse(['message'=>'Sent successfully'],200);
        }
        else{
            return $this->errorResponse('The Propose was sent one time.',400);
        }
    }

    public function sendProposes(Request $request)
    {
        $this->validate($request,[
           'proposes'=>'array|required',
           'proposes.*'=>'integer|required'
        ]);

        foreach ($request->proposes as $item){
            $propose=Propose::query()->findOrFail($item);
            $propose->selected=Propose::SELECTED;
            $propose->save();
        }

        return $this->successResponse(['message'=>trans('response.theProposesWereSent')],200);
    }


    public function confirmOrReject(Request $request,$id)
    {
        $propose=ProposeDocument::query()->findOrFail($id);
        $roles=$this->getUserRoles();
        $roleIds=[];
        foreach ($roles['roles'] as $role){
            array_push($roleIds,$role['id']);
        }
        if ($request->status==1){

            if (in_array(ProposeDocument::DIRECTOR_ROLE,$roleIds)){
                if ($propose->status==ProposeDocument::STATUS_WAIT){
                    $propose->update(['status'=>ProposeDocument::STATUS_CONFIRMED]);

                    $message=trans('response.theProposeAcceptedByDirector');
                    $code=200;
                }

                else{
                    $message=trans('response.theProposeAlreadyAccepted');
                    $code=400;
                }
            }
            $propose->progress_status=4;
            $propose->save();

            return $this->successResponse($message,$code);
        }

        else{
            if (in_array(ProposeDocument::DIRECTOR_ROLE,$roleIds))
                $userRole=ProposeDocument::DIRECTOR_ROLE;

            if (in_array(8,$roleIds)){
                if ($propose->status===ProposeDocument::STATUS_REJECTED)
                    return $this->errorResponse(trans('response.theProposeAlreadyRejected'),Response::HTTP_BAD_REQUEST);

                DB::beginTransaction();
                try {
                    $propose->update(['status'=>ProposeDocument::STATUS_REJECTED]);
                    $archiveDocument=new ArchiveDocument();
                    $archiveDocument->propose_id=$propose->id;
                    $archiveDocument->employee_id=$this->getEmployeeId($request->company_id);
                    $archiveDocument->role_id=$userRole;
                    $archiveDocument->reason=$request->reason;
                    $archiveDocument->status=ArchiveDocument::REJECTED_STATUS;
                    $archiveDocument->save();

                    DB::commit();
                    return $this->successResponse('The propose rejected!',200);
                }
                catch (\Exception $exception){
                    DB::rollback();
                    return $this->errorResponse($exception->getMessage(), \Illuminate\Http\Response::HTTP_BAD_REQUEST);
                }

            }

            return $this->errorResponse(trans('response.youDontHaveAccess'),Response::HTTP_BAD_REQUEST);
        }

    }

    public function getAllConfirmedProposes(Request $request)
    {
        $per_page=$request->per_page ?? 10;
        $proposes=ProposeDocument::query()
            ->with('proposes')
            ->where(['progress_status'=>4,'status'=>ProposeDocument::STATUS_CONFIRMED])
            ->paginate($per_page);

        return $this->dataResponse($proposes,200);
    }

    public function sendBack(ProposeDocument $id)
    {
        if ($id->send_back==1){
            return $this->errorResponse(trans('response.theProposeDocAlreadySentBack'),Response::HTTP_BAD_REQUEST);
        }
            $id->progress_status=1;
            $id->send_back=1;
            $id->save();
        return $this->successResponse(['message'=>'The propose was sent back!'],200);
    }

    public function getAllSentBackProposes(Request $request)
    {
        $per_page=$request->per_page ?? 10;

        $roles=$this->getUserRoles();

        $roleIds=[];

        foreach ($roles['roles'] as $role){
            array_push($roleIds,$role['id']);
        }

        if(in_array(42,$roleIds))
            $progressStatus=1;
        else{
            return $this->errorResponse(trans('response.notFound'),404);
        }

        $proposes=ProposeDocument::query()
            ->where(['send_back'=>1,'status'=>ProposeDocument::STATUS_WAIT,'progress_status'=>$progressStatus])
            ->with('proposes')
            ->paginate($per_page);
        return $this->dataResponse($proposes,200);
    }

    public function update(Request $request,$id)
    {
        $proposeDoc=ProposeDocument::query()->findOrFail($id);
        if ($proposeDoc->progress_status!=1)
            return $this->errorResponse(trans('response.theProposeIsOnProgress'),Response::HTTP_BAD_REQUEST);
        DB::beginTransaction();

        try {
            $proposeDoc->update([
                'description'=>$request->description
            ]);

            if ($request->hasFile('offer_file')){
                $proposeDoc->offer_file=$this->uploadFile($request->company_id,$request->file('offer_file'));
            }
            $proposeDoc->save();

            foreach ($request->get('proposeDetails') as $value){
                $propose=[
                    'amount'=>$value['amount'],
                    'price'=>$value['price'],
                ];

                $proposeDetail=ProposeDetail::query()->where(['propose_id'=>$value['propose_id'],'id'=>$value['propose_detail_id']])->first();
                    $proposeDetail->update($propose);
            }

            foreach ($request->get('proposeCompanyDetails') as $item){
                $detail=[
                    'indicator'=>$item['indicator'],
                    'value'=>$item['value'],
                ];

                $proposeCompanyDetail=ProposeCompanyDetail::query()->where(['propose_company_id'=>$item['propose_company_id'],'id'=>$item['company_detail_id']])->first();
                $proposeCompanyDetail->update($detail);
            }

            foreach ($request->get('propose') as $val){
                $detail=[
                    'company_name'=>$val['company_name']
                ];
                $proposeCompany=Propose::query()->where('id',$val['propose_id'])->first('propose_company_id');
                ProposeCompany::query()->where('id',$proposeCompany->propose_company_id)->update($detail);
            }
            DB::commit();
            return $this->successResponse(trans('response.updatedSuccessfully!'),\Symfony\Component\HttpFoundation\Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse($e->getMessage(), \Symfony\Component\HttpFoundation\Response::HTTP_BAD_REQUEST);
        }

    }

    public function delete(Request $request,$id)
    {
//        $departmentId=DB::table('employee_contracts')
//            ->where('employee_id',$this->getEmployeeId($request->company_id))
//            ->distinct()
//            ->get('department_id');

        $propose=ProposeDocument::query()->findOrFail($id);

        if ($propose->employee_id!=$this->getEmployeeId($request->company_id))
            return $this->errorResponse(trans('response.youDontHaveAccessToDelete'));

        $propose->delete();
        return response()->json(['message'=>trans('response.proposeDeleted')],200);
    }

    public function uploadFile($company_id, $file, $str = 'storages')
    {
        if ($file instanceof UploadedFile) {
            return $file->store("/propose_documents/$company_id/$str");
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

    public function getUserRoles()
    {
        return User::query()
            ->where('id',Auth::id())
            ->with('roles')
            ->first();
    }
}
