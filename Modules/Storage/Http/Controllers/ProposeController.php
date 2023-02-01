<?php

namespace Modules\Storage\Http\Controllers;

use App\Http\Requests\ProposeRequest;
use App\Models\User;
use App\Models\UserRole;
use App\Traits\ApiResponse;
use App\Traits\UserInfo;
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
    use  ApiResponse, ValidatesRequests,UserInfo;
    public function index(Request $request)
    {
        $per_page=$request->per_page ?? 10;
        $proposes=ProposeDocument::query()
            ->with([
                'employee:id,user_id',
                'proposeDetails',
                'proposeDetails.proposeCompany:id,company_name,total_value',
                'proposeDetails.proposeCompany.details:id,propose_company_id,indicator,value'
            ])
            ->where(['employee_id'=>$this->getEmployeeId($request->company_id),
//                'progress_status'=>1
            ])
            ->orderBy('id','desc')
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

        $proposes=ProposeDocument::query()
            ->with([
                'employee:id,user_id'
            ]);

        if(in_array(ProposeDocument::FINANCIER_ROLE,$roleIds)){
            $progress_status=2;
            $proposes
                ->where('progress_status',$progress_status)
                ->with([
                    'proposeDetails',
                    'proposeDetails.proposeCompany:id,company_name,total_value',
                    'proposeDetails.proposeCompany.details:id,propose_company_id,indicator,value'
                ])->orderBy('id','desc');
        }
        else if (in_array(ProposeDocument::DIRECTOR_ROLE,$roleIds)){
            $progress_status=3;
            $proposes
                ->where('progress_status',$progress_status)
                ->with([
                    'selectedProposeDetails',
                    'selectedProposeDetails.proposeCompany:id,company_name,total_value',
                    'selectedProposeDetails.proposeCompany.details:id,propose_company_id,indicator,value'
                ])->orderBy('id','desc');
        }
        return $this->dataResponse($proposes->paginate($per_page),200);

    }

    public function show( $id)
    {
        $propose=ProposeDocument::query()
            ->with([
                'employee:id,user_id',
                'proposes',
                'proposes.company',
                'proposes.company.details',
                'proposes.company.proposeDetails',
//                'proposes.company.proposeDetails.demandItem:id,title_id,kind_id,model_id,mark',
                'proposes.company.proposeDetails.demandItem.kind',
                'proposes.company.proposeDetails.demandItem.title',
                'proposes.company.proposeDetails.demandItem.model'
            ])
            ->findOrFail($id);
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
            foreach ($proposeRequest->proposeDetails as $proposeDetail){
                $proposeCompany=ProposeCompany::query()->firstOrCreate([
                    'company_name'=>$proposeDetail['company_name'],
                    'total_value'=>$proposeDetail['total_value'],
                ]);

                array_push($company_ids,$proposeCompany->id);
                foreach ($proposeDetail['companyDetails'] as $companyDetail){
                    ProposeCompanyDetail::query()
                        ->firstOrCreate([
                            'propose_company_id'=>$proposeCompany->id,
                            'indicator'=>$companyDetail['indicator'],
                            'value'=>$companyDetail['value'],
                        ]);
                }


                foreach ($proposeDetail['companyProposeDetails'] as $companyProposeDetail) {
                    $product=DemandItem::query()
                        ->where([
                            'title_id'=>$companyProposeDetail['title_id'],
                            'kind_id'=>$companyProposeDetail['kind_id'],
                            'model_id'=>$companyProposeDetail['model_id']
                        ])
                        ->firstOrFail('id');
                    $proposeDetails=new ProposeDetail();
                    $proposeDetails->amount=$companyProposeDetail['amount'];
                    $proposeDetails->price=$companyProposeDetail['price'];
                    $proposeDetails->demand_item_id=$product->id;
                    $proposeDetails->propose_document_id=$proposeDocument->id;
                    $proposeDetails->propose_company_id=$proposeCompany->id;
                    $proposeDetails->save();
                }
            }

            foreach ($company_ids as $id){
                $propose=new Propose();
                $propose->propose_document_id=$proposeDocument->id;
                $propose->propose_company_id=$id;
                $propose->save();
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
            $propose->progress_status=2;
            $propose->send_back=0;
            $propose->save();
            return $this->successResponse(['message'=>'Sent successfully'],200);
        }
        else{
            return $this->errorResponse('The Propose was sent one time.',400);
        }
    }

    public function sendProposes(Request $request,$id)
    {
        $this->validate($request,[
            'proposes'=>'array|required',
            'proposes.*'=>'integer|required'
        ]);
        $proposeDoc=ProposeDocument::query()->find($id);
        if (!$proposeDoc){
            return $this->errorResponse('Bu id-li teklif senedi bazada movcud deyil!',Response::HTTP_NOT_FOUND);
        }
        else{
            foreach ($request->proposes as $item){
                ProposeDetail::query()
                    ->where([
                        'propose_document_id'=>$proposeDoc->id,
                        'id'=>$item
                    ])
                    ->update(['selected'=>ProposeDetail::SELECTED]);
//            dd($propose[0]->selected);
//            $propose->selected=ProposeDetail::SELECTED;
//            $propose->save();
            }
            $proposeDoc->update(['progress_status'=>3]);

            return $this->successResponse(['message'=>trans('response.theProposesWereSent')],200);
        }

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
                    $propose->progress_status=4;

                    $message=trans('response.theProposeAcceptedByDirector');
                    $code=200;
                }

                else{
                    $message=trans('response.theProposeAlreadyAccepted');
                    $code=400;
                }
            }
            else if (in_array(ProposeDocument::FINANCIER_ROLE,$roleIds)){
                $propose->progress_status=3;
            }
            $propose->save();

            return $this->successResponse($message,$code);
        }

        else{

            if (in_array(ProposeDocument::DIRECTOR_ROLE,$roleIds)){
                $userRole=ProposeDocument::DIRECTOR_ROLE;
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
            ->orderBy('id','desc')
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

        if(in_array(ProposeDocument::PURCHASED_ROLE,$roleIds))
            $progressStatus=1;
        else{
            return $this->errorResponse(trans('response.notFound'),404);
        }

        $proposes=ProposeDocument::query()
            ->where(['send_back'=>1,'status'=>ProposeDocument::STATUS_WAIT,'progress_status'=>$progressStatus])
            ->with('proposes')
            ->orderBy('id','desc')
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

    public function uploadFile($company_id, $file, $str = 'documents')
    {
        if ($file instanceof UploadedFile) {
            return $file->store("/propose-documents/$company_id/$str");
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
