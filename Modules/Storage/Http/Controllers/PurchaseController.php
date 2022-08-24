<?php

namespace Modules\Storage\Http\Controllers;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Modules\Hr\Entities\Employee\Employee;
use Modules\Plaza\Entities\Kind;
use Modules\Storage\Entities\Demand;
use Modules\Storage\Entities\Product;
use Modules\Storage\Entities\ProductColor;
use Modules\Storage\Entities\ProductKind;
use Modules\Storage\Entities\ProductModel;
use Modules\Storage\Entities\ProductTitle;
use Modules\Storage\Entities\Purchase;
use Modules\Storage\Entities\Unit;

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
            $this->validatePhurcaseProduct(),
            'total_price'=> 'nullable|numeric|gt:' . ($request->custom_fee + $request->transport_fee) .'',
        ]);

        DB::beginTransaction();
        try {

            DB::commit();
        }
        catch (\Exception $exception){
            DB::rollBack();
            return $this->errorResponse($exception->getMessage(),$exception->getCode());
        }


    }

    public function validatePhurcaseProduct()
    {
        return[
            'title_id'=>['required','integer'],
            'kind_id'=>['required','integer'],
            'model_id'=>['required','integer'],
            'mark'=>['required','string'],
            'color_id'=>['nullable','integer'],
            'made_in'=>['nullable','integer'],
            'custom_tax'=>['required','numeric'],
            'price'=>['nullable','numeric','gt:0'],
            'amount'=>['nullable','numeric'],
            'discount'=>['nullable','numeric'],
            'edv_percent'=>['nullable','numeric'],
            'edv_tax'=>['nullable','numeric'],
            'excise_percent'=>['nullable','numeric'],
            'excise_tax'=>['nullable','numeric'],
            'total_price'=>['required','numeric'],

        ];
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
}
