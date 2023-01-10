<?php

namespace Modules\Storage\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Esd\Entities\Archive;
use Modules\Hr\Entities\Employee\Employee;
use Modules\Storage\Entities\ArchiveDemand;
use Modules\Storage\Entities\ArchiveDocument;
use Modules\Storage\Entities\Product;
use Modules\Storage\Entities\Purchase;
use Modules\Storage\Entities\PurchaseProduct;
use Modules\Storage\Entities\StorageProduct;
use Modules\Storage\Entities\StoragePurchase;
use Modules\Storage\Entities\StoragePurchaseItem;
use Symfony\Component\HttpFoundation\Response;

class PurchaseStorageController extends Controller
{
    public function index(Request $request)
    {
        return $this->dataResponse(Purchase::query()
            ->where(['send_back'=>0,'status'=>Purchase::STATUS_ACCEPTED,'progress_status'=>3])
            ->with([
                'purchaseProducts',
                'proposeDocument.proposes.detail.demandItem'
            ])
            ->paginate($request->per_page ?? 10)
        );
    }

    public function store(Request $request,$id)
    {


        DB::beginTransaction();
        try {
            $purchase=Purchase::query()->findOrFail($id);
            $strPurchase=new StoragePurchase();
            $strPurchase->purchase_id=$id;
            $strPurchase->company_name=$purchase->company_name;
            $strPurchase->save();

            $productStorage = StorageProduct::query()->firstOrCreate(['name' => $request['storage_name']]);

            foreach ($request->productInfo as $value) {

                $storagePItem = new StoragePurchaseItem();
                $storagePItem->storage_purchase_id = $strPurchase->id;
                $storagePItem->purchase_product_id = $value['purchase_product_id'];
                $storagePItem->storage_id = $productStorage->id;
                $storagePItem->measure = $value['measure'];
                $storagePItem->amount = $value['amount'];
                $storagePItem->situation = $value['situation'];

                $purchaseProduct = PurchaseProduct::query()->findOrFail($value['purchase_product_id']);
//            dd($purchaseProduct);
                $product = Product::query()
                    ->where([
                        'title_id' => $purchaseProduct->title_id,
                        'kind_id' => $purchaseProduct->kind_id,
//                    'mark_id'=>$value['mark_id'],
                        'model_id' => $purchaseProduct->mark_id
                    ])
                    ->first();
                if ($product) {
                    $storagePItem->product_id = $product->id;
                }

                $storagePItem->save();
            }
//        dd($strPurchase->id);
            $totalAmountInStorage=StoragePurchaseItem::query()->where('storage_purchase_id',$strPurchase->id)->sum('amount');
            $totalAmountInPurchase=PurchaseProduct::query()->where('purchase_id',$id)->sum('amount');
            if ($totalAmountInStorage >= $totalAmountInPurchase){
                $strPurchase->update(['is_completed'=>true]);
                    $data=[
                        'role_id'=>Purchase::SUPPLIER_ROLE,
                        'employee_id'=>$this->getEmployeeId($request->company_id),
                        'completed_doc_id'=>$strPurchase->id,
                        'created_at'=>now(),
                        'updated_at'=>now(),
                    ];
                    ArchiveDocument::query()->insert($data);
            }
            DB::commit();

            return $this->successResponse('Completed',Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse($e->getMessage());
        }



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
