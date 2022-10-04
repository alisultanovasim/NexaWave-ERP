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
        $user=User::query()
            ->where('id',Auth::id())
            ->with('roles')
            ->get();

        $roleIds=[];

        foreach ($user[0]['roles'] as $role){
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

    public function show(Propose $propose)
    {
        return $this->dataResponse($propose->with('details')->get(),200);
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

            $proposeDocument=new ProposeDocument();
            $proposeDocument->demand_id=$proposeRequest->demand_id;
            $proposeDocument->description=$proposeRequest->description;
            $proposeDocument->employee_id=$this->getEmployeeId($proposeRequest->company_id);

            if ($proposeRequest->hasFile('offer_file')){
                $proposeDocument->offer_file=$this->uploadImage($proposeRequest->company_id,$proposeRequest->offer_file);
            }
            $proposeDocument->save();

            $propose_ids=[];

            foreach ($proposeRequest->proposeCompanyDetails as $detail){
                $proposeCompany=ProposeCompany::query()->firstOrCreate(['company_name'=>$detail['company_name']]);

                ProposeCompanyDetail::query()
                    ->firstOrCreate([
                        'propose_company_id'=>$proposeCompany->id,
                        'indicator'=>$detail['indicator'],
                        'value'=>$detail['value'],
                    ]);

                $propose=new Propose();
                $propose->propose_document_id=$proposeDocument->id;
                $propose->propose_company_id=$proposeCompany->id;
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
                $propose->increment('progress_status',1);
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

    public function reject($id)
    {
        $user=User::query()
            ->where('id',Auth::id())
            ->with('roles')
            ->get();

        $roleIds=[];

        foreach ($user[0]['roles'] as $role){
            array_push($roleIds,$role['id']);
        }
        if (in_array(8,$roleIds)){
            $propose=ProposeDocument::query()->findOrFail($id);
            $propose->update(['status'=>ProposeDocument::STATUS_REJECTED]);

            return $this->successResponse('The propose rejected!',200);
        }
        return $this->errorResponse(trans('response.youDontHaveAccess'),Response::HTTP_BAD_REQUEST);

    }

    public function update(Request $request,$id)
    {
        $propose=ProposeDocument::query()->findOrFail($id);
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
}
