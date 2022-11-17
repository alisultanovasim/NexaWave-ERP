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
            foreach ($request->productInfo as $value){
                $productStorage=StorageProduct::query()
                    ->firstOrCreate(['name'=>$value['storage_name']]);

                $storage=new StoragePurchase();
                $storage->purchase_id=$id;
                $storage->storage_name=$productStorage->name;
                $storage->company_name=$value['company_name'];
                $storage->title_id=$value['title_id'];
                $storage->kind_id=$value['kind_id'];
                $storage->mark_id=$value['mark_id'];
                $storage->model_id=$value['model_id'];
                $storage->unit_id=$value['unit_id'];
                $storage->color=$value['color'];
                $storage->price=$value['price'];
                $storage->amount=$value['amount'];
                $storage->situation=$value['situation'];

                $product=Product::query()
                    ->where([
                        'title_id'=>$value['title_id'],
                        'kind_id'=>$value['kind_id'],
//                    'mark_id'=>$value['mark_id'],
                        'model_id'=>$value['model_id']
                    ])
                    ->first();
                if ($product){
                    $storage->product_id=$product->id;
                }

                $storage->save();

                $totalAmountInStorage=StoragePurchase::query()->where('purchase_id',$id)->sum('amount');
                $totalAmountInPurchase=PurchaseProduct::query()->where('purchase_id',$id)->sum('amount');

                if ($totalAmountInStorage>=$totalAmountInPurchase){
                    $data=[
                        'role_id'=>Purchase::SUPPLIER_ROLE,
                        'employee_id'=>$this->getEmployeeId($request->company_id),
                        'completed_doc_id'=>$storage->id,
                        'created_at'=>now(),
                        'updated_at'=>now(),
                    ];
                    ArchiveDocument::query()->insert($data);
                }

            }
            DB::commit();

            return $this->successResponse($storage);
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
