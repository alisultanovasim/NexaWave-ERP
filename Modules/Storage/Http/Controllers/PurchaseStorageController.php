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

class PurchaseStorageController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $this->validate($request, [
            'per_page' => ['nullable', 'integer'],
            'company_id' => ['required', 'integer']
        ]);

        return $this->dataResponse(Purchase::query()->where(['send_back'=>0,'status'=>Purchase::STATUS_WAIT])->with('purchaseProducts')->paginate($request->per_page ?? 10));

    }

}
