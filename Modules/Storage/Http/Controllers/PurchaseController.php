<?php

namespace Modules\Storage\Http\Controllers;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Modules\Storage\Entities\ProposeArchive;
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

        return $this->dataResponse(Purchase::query()->paginate($request->per_page ?? 10));

    }

    public function store(Request $request)
    {
        $this->validate($request,[
            $this->valdiatePurchase(),
            'productInfo'=>['required','array'],
            'productInfo.*.title_id'=>['required','integer',Rule::exists('product_titles','id')],
            'productInfo.*.kind_id'=>['required','integer',Rule::exists('product_kinds','id')],
            'productInfo.*.model_id'=>['required','integer',Rule::exists('product_models','id')],
            'productInfo.*.mark'=>['required','string'],
            'productInfo.*.color_id'=>['nullable','integer',Rule::exists('product_colors','id')],
            'productInfo.*.made_in'=>['nullable','integer',Rule::exists('countries','id')],
            'productInfo.*.custom_tax'=>['required','numeric'],
            'productInfo.*.price'=>['nullable','numeric','gt:0'],
            'productInfo.*.amount'=>['nullable','numeric'],
            'productInfo.*.discount'=>['nullable','numeric'],
            'productInfo.*.edv_percent'=>['nullable','numeric'],
            'productInfo.*.edv_tax'=>['nullable','numeric'],
            'productInfo.*.excise_percent'=>['nullable','numeric'],
            'productInfo.*.excise_tax'=>['nullable','numeric'],
            'productInfo.*.total_price'=>['required','numeric'],
            'total_price'=> 'required|numeric|gt:' . ($request->custom_fee + $request->transport_fee) .'',
        ]);

        DB::beginTransaction();
        try {
            $purchase=Purchase::query()
                ->create($request->only([
                    'supplier_id',
                    'sender',
                    'company_id',
                    'custom_fee',
                    'transport_fee',
                    'transport_tax',
                    'payment_condition',
                    'deliver_condition',
                    'deliver_deadline',
                    'payment_deadline',
                    'total_price'

            ]));
            foreach ($request->get('productInfo') as $value){
                $product=[
                    'purchase_id'=>$purchase->id,
                    'title_id'=>$value['title_id'],
                    'kind_id'=>$value['kind_id'],
                    'model_id'=>$value['model_id'],
                    'mark'=>$value['mark'],
                    'color_id'=>$value['color_id'],
                    'made_in'=>$value['made_in'],
                    'custom_tax'=>$value['custom_tax'],
                    'price'=>$value['price'],
                    'amount'=>$value['amount'],
                    'discount'=>$value['discount'],
                    'edv_percent'=>$value['edv_percent'],
                    'edv_tax'=>$value['edv_tax'],
                    'excise_percent'=>$value['excise_percent'],
                    'excise_tax'=>$value['excise_tax'],
                    'total_price'=>$value['total_price']
                ];
                $purchaseProduct=PurchaseProduct::query()->insert($product);
            }

            DB::commit();
            return $this->successResponse($purchaseProduct,201);
        }
        catch (\Exception $exception){
            DB::rollBack();
            return $this->errorResponse($exception->getMessage());
        }


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
    public function addToArchive(Request $request,$id)
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
            'supplier_id'=>'required|integer',
            'sender'=>'required|integer',
            'company_id'=>'required|integer',
            'custom_fee'=>'nullable|integer',
            'transport_fee'=>'nullable|integer',
            'transport_tax'=>'nullable|integer',
            'payment_condition'=>'nullable|integer',
            'deliver_condition'=>'numeric|integer',
            'deliver_deadline'=>'nullable|date',
            'payment_deadline'=>'nullable|date',
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
