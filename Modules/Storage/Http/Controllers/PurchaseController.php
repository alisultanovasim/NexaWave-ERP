<?php

namespace Modules\Storage\Http\Controllers;

use App\Traits\ApiResponse;
use App\Traits\UserInfo;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Modules\Storage\Entities\ArchiveDocument;
use Modules\Storage\Entities\Purchase;
use Modules\Storage\Entities\PurchaseProduct;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

class PurchaseController extends Controller
{
    use ApiResponse, ValidatesRequests,UserInfo;

    public function index(Request $request)
    {
        $this->validate($request, [
            'per_page' => ['nullable', 'integer'],
            'company_id' => ['required', 'integer']
        ]);

        $roles=$this->getUserRoles();
        $roleIds=[];
        foreach ($roles['roles'] as $role){
            array_push($roleIds,$role['id']);
        }
        if(in_array(Purchase::DIRECTOR_ROLE,$roleIds)){
            return $this->dataResponse(Purchase::query()
                ->where([
                    'send_back'=>0,
                    'status'=>Purchase::STATUS_WAIT,
                    'progress_status'=>2
                ])
                ->with('purchaseProducts')
                ->paginate($request->per_page ?? 10),\Symfony\Component\HttpFoundation\Response::HTTP_OK);
        }
        else{
            return $this->dataResponse(Purchase::query()
                ->where([
                    'send_back'=>0,
                    'status'=>Purchase::STATUS_WAIT,
                    'progress_status'=>1,
                    'sender_id'=>$this->getEmployeeId($request->company_id)
                ])
                ->orderBy('id','desc')
                ->with('purchaseProducts')->paginate($request->per_page ?? 10));
        }

    }

    public function show($id)
    {
        return $this->dataResponse(Purchase::query()
            ->with([
                'purchaseProducts',
//                'purchaseProducts.kind',
//                'purchaseProducts.title',
//                'purchaseProducts.mark',
            ])
            ->findOrFail($id),200);
    }

    public function store(Request $request)
    {
        $totalSumPrice=0;
        $this->validate($request,[
            $this->valdiatePurchase(),
            'productInfo'=>['required','array'],
            'productInfo.*.title_id'=>['required','integer',Rule::exists('product_titles','id')],
            'productInfo.*.kind_id'=>['required','integer',Rule::exists('product_kinds','id')],
            'productInfo.*.mark_id'=>['required','integer',Rule::exists('product_models','id')],
            'productInfo.*.model'=>['required','string'],
            'productInfo.*.color'=>['nullable','string'],
            'productInfo.*.made_in'=>['nullable','integer',Rule::exists('countries','id')],
            'productInfo.*.custom_fee'=>['required','numeric'],
            'productInfo.*.transport_fee'=>['required','numeric'],
            'productInfo.*.price'=>['nullable','numeric','gt:0'],
            'productInfo.*.amount'=>['nullable','numeric'],
            'productInfo.*.discount'=>['nullable','numeric'],
            'productInfo.*.edv_percent'=>['nullable','numeric'],
            'productInfo.*.excise_percent'=>['nullable','numeric'],
//            'total_price'=> 'nullable|numeric|gt:' . ($request->custom_fee + $request->transport_fee) .'',
        ]);

        DB::beginTransaction();
        try {
            $purchase=new Purchase();
            $purchase->company_name=$request->company_name;
            $purchase->company_id=$request->company_id;
            $purchase->sender_id=$this->getEmployeeId($request->company_id);
            $purchase->save();

            foreach ($request->get('productInfo') as $value){
                $product=[
                    'purchase_id'=>$purchase->id,
                    'title_id'=>$value['title_id'],
                    'kind_id'=>$value['kind_id'],
                    'mark_id'=>$value['mark_id'],
                    'model'=>$value['model'],
                    'color'=>$value['color'],
                    'made_in'=>$value['made_in'],
                    'measure'=>$value['measure'],
                    'custom_fee'=>$value['custom_fee'],
                    'transport_fee'=>$value['transport_fee'],
                    'price'=>$value['price'],
                    'amount'=>$value['amount'],
                    'discount'=>$value['discount'],
                    'edv_percent'=>$value['edv_percent'],
                    'excise_percent'=>$value['excise_percent'],
                    'total_price'=>($value['price']
                            +$value['custom_fee']
                            +$value['transport_fee']
                            +$value['edv_percent']
                            +$value['excise_percent']
                            -$value['discount'])
                            *$value['amount']
                ];
                $totalSumPrice+=$product['total_price'];
                PurchaseProduct::query()->insert($product);
            }
//            dd($totalSumPrice);
            $lastPurchase=Purchase::query()->findOrFail($purchase->id);
            $lastPurchase->update(['total_price'=>$totalSumPrice]);
            DB::commit();
            return $this->successResponse(trans('response.purchaseAddedSuccessfully'),201);
        }
        catch (\Exception $exception){
            DB::rollBack();
            return $this->errorResponse($exception->getMessage());
        }
    }

    public function update(Request $request,$id)
    {
        $purchase=Purchase::query()->findOrFail($id);
        $purchase->update($request->all());
            foreach ($request->get('productInfo') as $value){
                $product=[
                    'title_id'=>$value['title_id'],
                    'kind_id'=>$value['kind_id'],
                    'mark_id'=>$value['mark_id'],
                    'model'=>$value['model'],
                    'color'=>$value['color'],
                    'made_in'=>$value['made_in'],
                    'measure'=>$value['measure'],
                    'custom_fee'=>$value['custom_fee'],
                    'transport_fee'=>$value['transport_fee'],
                    'price'=>$value['price'],
                    'amount'=>$value['amount'],
                    'discount'=>$value['discount'],
                    'edv_percent'=>$value['edv_percent'],
                    'excise_percent'=>$value['excise_percent'],
                    'total_price'=>($value['price']
                            +$value['custom_fee']
                            +$value['transport_fee']
                            +$value['edv_percent']
                            +$value['excise_percent']
                            -$value['discount'])
                        *$value['amount']
                ];

                PurchaseProduct::query()->findOrFail($value['productId'])->update($product);
        }
            return $this->successResponse(['message'=>trans('response.updatedSuccessfully!')]);
    }

    public function delete($id)
    {
        $roles=$this->getUserRoles();

        $roleIds=[];

        foreach ($roles['roles'] as $role){
            array_push($roleIds,$role['id']);
        }

        if(!in_array(Purchase::DIRECTOR_ROLE,$roleIds)){
            return $this->errorResponse(trans('response.youDontHaveAccess'),\Symfony\Component\HttpFoundation\Response::HTTP_BAD_REQUEST);
        }
        DB::beginTransaction();

        try {
            Purchase::query()->findOrFail($id)->delete();
//            PurchaseProduct::query()->where('purchase_id',$purchase['id'])->delete();
            DB::commit();
            return $this->successResponse(['message'=>trans('response.deletedSuccessfully!')]);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse($e->getMessage());
        }



    }

    public function send($id)
    {
        $roles=$this->getUserRoles();

        $roleIds=[];

        foreach ($roles['roles'] as $role){
            array_push($roleIds,$role['id']);
        }

        if(!in_array(Purchase::PURCHASED_ROLE,$roleIds)){
            return $this->errorResponse(trans('response.youDontHaveAccess'),\Symfony\Component\HttpFoundation\Response::HTTP_BAD_REQUEST);
        }
            $purchase=Purchase::query()->findOrFail($id);
            $purchase->progress_status=2;
            $purchase->send_back=0;
            $purchase->save();
        return $this->successResponse(['message'=>trans('response.sentSuccessfully!')],\Symfony\Component\HttpFoundation\Response::HTTP_OK);
    }

    public function sendBack($id)
    {
        $roles=$this->getUserRoles();

        $roleIds=[];
        foreach ($roles['roles'] as $role){
            array_push($roleIds,$role['id']);
        }

        if(!in_array(Purchase::DIRECTOR_ROLE,$roleIds)){
            return $this->errorResponse(trans('response.youDontHaveAccess'),\Symfony\Component\HttpFoundation\Response::HTTP_BAD_REQUEST);
        }
        $purchase=Purchase::query()->findOrFail($id);
        $purchase->update(['send_back'=>1]);
        return $this->successResponse(['message'=>trans('response.sentBackSuccessfully!')],\Symfony\Component\HttpFoundation\Response::HTTP_OK);

    }

    public function getAllSentBack(Request $request)
    {
        $purchases=Purchase::query()
            ->with('purchaseProducts')
            ->where(['send_back'=>1,'status'=>Purchase::STATUS_WAIT])
            ->orderBy('id','desc')
            ->paginate($request->per_page ?? 10);

        return $this->dataResponse($purchases,Response::HTTP_OK);
    }

    public function confirmOrReject(Request $request,$id)
    {
        $purchase=Purchase::query()->findOrFail($id);
        $roles=$this->getUserRoles();
        $roleIds=[];
        foreach ($roles['roles'] as $role){
            array_push($roleIds,$role['id']);
        }

        if ($request->status==1){

            if (in_array(Purchase::DIRECTOR_ROLE,$roleIds)){
                if ($purchase->status==Purchase::STATUS_WAIT){
                    $purchase->update(['status'=>Purchase::STATUS_ACCEPTED]);
                    $purchase->progress_status=3;

//                    $archiveDocument=new ArchiveDocument();
//                    $archiveDocument->document_id=$purchase->id;
//                    $archiveDocument->document_type=ArchiveDocument::PURCHASE_TYPE;
//                    $archiveDocument->from_id=$this->getEmployeeId($request->company_id);
//                    $archiveDocument->save();

                    $message=trans('response.thePurchaseAcceptedByDirector');
                    $code=200;
                }

                else{
                    $message=trans('response.thePurchaseAlreadyAccepted');
                    $code=400;
                }
            }
            else if (in_array(Purchase::FINANCIER_ROLE,$roleIds)){
                $purchase->progress_status=4;

                $message=trans('response.thePurchaseAcceptedByFinancier');
                $code=200;
            }

            $purchase->save();

            return $this->successResponse($message,$code);
        }
        else{


            if (in_array(Purchase::DIRECTOR_ROLE,$roleIds)){
                $userRole=Purchase::DIRECTOR_ROLE;
                if ($purchase->status===Purchase::STATUS_REJECTED)
                    return $this->errorResponse(trans('response.thePurchaseAlreadyRejected'), \Symfony\Component\HttpFoundation\Response::HTTP_BAD_REQUEST);

                DB::beginTransaction();
                try {
                    $purchase->update(['status'=>Purchase::STATUS_REJECTED]);
                    $archiveDocument=new ArchiveDocument();
                    $archiveDocument->purchase_id=$purchase->id;
                    $archiveDocument->employee_id=$this->getEmployeeId($request->company_id);
                    $archiveDocument->role_id=$userRole;
                    $archiveDocument->reason=$request->reason;
                    $archiveDocument->status=ArchiveDocument::REJECTED_STATUS;
                    $archiveDocument->save();

                    DB::commit();
                    return $this->successResponse('The purchase rejected!',200);
                }
                catch (\Exception $exception){
                    DB::rollback();
                    return $this->errorResponse($exception->getMessage(), \Symfony\Component\HttpFoundation\Response::HTTP_BAD_REQUEST);
                }

            }

            return $this->errorResponse(trans('response.youDontHaveAccess'),\Symfony\Component\HttpFoundation\Response::HTTP_BAD_REQUEST);

        }


    }

    public function getAllConfirmed(Request $request)
    {
        $per_page=$request->per_page ?? 10;
        $purchases=Purchase::query()
            ->with('purchaseProducts')
            ->where(['status'=>Purchase::STATUS_ACCEPTED,'progress_status'=>3])
            ->orderBy('id','desc')
            ->paginate($per_page);

        return $this->dataResponse($purchases);
    }

    public function pay($id)
    {
        $purchase=Purchase::query()->findOrFail($id);
        $purchase->progress_status=4;
        $purchase->save();
        return $this->dataResponse($purchase);
    }

    public function getAllPayed(Request $request)
    {
        $per_page=$request->per_page ?? 10;
        $purchases=Purchase::query()
            ->where([
                'status'=>Purchase::STATUS_ACCEPTED,
                'progress_status'=>4,
                'send_back'=>0
            ])
            ->orderBy('id','desc')
            ->paginate($per_page);
        return $this->dataResponse($purchases);
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     */

    public function valdiatePurchase()
    {
        return [
            'sender_id'=>'required|integer',
            'company_id'=>'required|integer',
            'company_name'=>'required|string'
        ];
    }

    public function uploadDocument($company_id,$file,$str='storages')
    {
        if ($file instanceof UploadedFile){
            return $file->store("documents/demanddocuments/$company_id/$str");
        }

        return null;
    }
}
