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

        ]);
    }
}
