<?php

namespace Modules\Storage\Http\Controllers;

use App\Models\User;
use App\Traits\ApiResponse;
use http\Env\Response;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Modules\Hr\Entities\Employee\Employee;
use Modules\Storage\Entities\ArchiveRejectedPropose;
use Modules\Storage\Entities\ArchiveRejectedPurchase;
use Modules\Storage\Entities\ProposeArchive;
use Modules\Storage\Entities\ProposeDocument;
use Modules\Storage\Entities\Purchase;
use Modules\Storage\Entities\PurchaseArchive;
use Modules\Storage\Entities\PurchaseProduct;
use Modules\Storage\Entities\StorageDocyment;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class PurchaseController extends Controller
{
    use ApiResponse, ValidatesRequests;

    public function index(Request $request)
    {
        $this->validate($request, [
            'per_page' => ['nullable', 'integer'],
            'company_id' => ['required', 'integer']
        ]);

        return $this->dataResponse(Purchase::query()->where(['send_back'=>0,'status'=>Purchase::STATUS_WAIT])->with('purchaseProducts')->paginate($request->per_page ?? 10));

    }

    public function store(Request $request)
    {
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
            'productInfo.*.total_price'=>['required','numeric'],
            'total_price'=> 'required|numeric|gt:' . ($request->custom_fee + $request->transport_fee) .'',
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
                PurchaseProduct::query()->insert($product);
            }

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
        $user=User::query()
            ->where('id',Auth::id())
            ->with('roles')
            ->get();

        $roleIds=[];

        foreach ($user[0]['roles'] as $role){
            array_push($roleIds,$role['id']);
        }

        if(!in_array(8,$roleIds)){
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

    public function sendToDirector($id)
    {
        $user=User::query()
            ->where('id',Auth::id())
            ->with('roles')
            ->get();

        $roleIds=[];

        foreach ($user[0]['roles'] as $role){
            array_push($roleIds,$role['id']);
        }

        if(!in_array(42,$roleIds)){
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
        $user=User::query()
            ->where('id',Auth::id())
            ->with('roles')
            ->get();

        $roleIds=[];
        foreach ($user[0]['roles'] as $role){
            array_push($roleIds,$role['id']);
        }

        if(!in_array(8,$roleIds)){
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
            ->paginate($request->per_page ?? 10);

        return $this->dataResponse($purchases);
    }

    public function confirm($id)
    {
        $purchase=Purchase::query()->findOrFail($id);
        $user=User::query()
            ->where('id',Auth::id())
            ->with('roles')
            ->get();
        $roleIds=[];
        foreach ($user[0]['roles'] as $role){
            array_push($roleIds,$role['id']);
        }
        if (in_array(Purchase::DIRECTOR_ROLE,$roleIds)){
            if ($purchase->status==Purchase::STATUS_WAIT){
                $purchase->update(['status'=>Purchase::STATUS_ACCEPTED]);
                $message=trans('response.thePurchaseAcceptedByDirector');
                $code=200;
            }

            else{
                $message=trans('response.thePurchaseAlreadyAccepted');
                $code=400;
            }
        }

        $purchase->progress_status=3;
        $purchase->save();

        return $this->successResponse($message,$code);
    }

    public function getAllConfirmed()
    {
        $purchases=Purchase::query()
            ->with('purchaseProducts')
            ->where(['status'=>Purchase::STATUS_ACCEPTED,'progress_status'=>3])
            ->get();

        return $this->dataResponse($purchases);
    }

    public function reject(Request $request,$id)
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
            $purchase=Purchase::query()->findOrFail($id);
            if ($purchase->status===Purchase::STATUS_REJECTED)
                return $this->errorResponse(trans('response.thePurchaseAlreadyRejected'), \Symfony\Component\HttpFoundation\Response::HTTP_BAD_REQUEST);

            DB::beginTransaction();
            try {
                $purchase->update(['status'=>Purchase::STATUS_REJECTED]);
                ArchiveRejectedPurchase::query()
                    ->firstOrCreate([
                       'purchase_id'=>$purchase->id,
                        'from_id'=>$this->getEmployeeId($request->company_id),
                        'reason'=>$request->reason
                    ]);

                DB::commit();
                return $this->successResponse('The purchase rejected!',200);
            }
            catch (\Exception $exception){
                DB::rollback();
                return $this->errorResponse($exception->getMessage(), \Illuminate\Http\Response::HTTP_BAD_REQUEST);
            }

        }

        return $this->errorResponse(trans('response.youDontHaveAccess'),\Symfony\Component\HttpFoundation\Response::HTTP_BAD_REQUEST);
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function addToStorage(Request $request,$id)
    {
        $this->validate($request,[
            'barcode'=>['required','string','unique:storage_documents'],
            'storage_id'=>['required','integer',Rule::exists('storages','id')],
            'expiration_date'=>'required|date',
            'amount'=>'required|integer|min:1',
            'document_file'=>['required','mimes:pdf,docx,png,jpg,jpeg','max:2048']
        ]);
        $add_to_storage=new StorageDocyment();
        $add_to_storage->propose_id=$id;
        $add_to_storage->barcode=$request->barcode;
        $add_to_storage->storage_id=$request->storage_id;
        $add_to_storage->expiration_date=$request->expiration_date;
        $add_to_storage->amount=$request->amount;
        $add_to_storage->company_id=$request->company_id;

        if ($request->hasFile('document_file')){
            $add_to_storage->document=$this->uploadDocument($request->company_id,$request->document_file);
        }
        $add_to_storage->save();

        return $this->successResponse($add_to_storage,200);
    }

    public function getAllPurchaseArchive(Request $request)
    {
        $per_page=$request->per_page ?? 10;
        return $this->dataResponse(PurchaseArchive::query()->where('company_id',$request->company_id)->paginate($per_page),200);
    }
    public function addToArchive(Request $request,$id): \Illuminate\Http\JsonResponse
    {
        $this->validate($request,[
            'company_name'=>'required|string',
            'product_name'=>'required|string',
            'start_date'=>'required|date',
            'end_date'=>'required|date',
            'product_type'=>'required|string',
            'demand_amount'=>'required|integer',
            'purchase_amount'=>'required|integer',
            'take_over_amount'=>'required|integer'
        ]);

        $array_request=$request->toArray();
        $array_request['supplier_id']=$id;

        $archive=PurchaseArchive::query()
            ->create($array_request);

        return $this->successResponse($archive,201);
    }

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
