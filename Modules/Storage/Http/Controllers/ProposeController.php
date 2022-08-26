<?php

namespace Modules\Storage\Http\Controllers;

use App\Http\Requests\ProposeRequest;
use App\Traits\ApiResponse;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Modules\Storage\Entities\ProductColor;
use Modules\Storage\Entities\ProductKind;
use Modules\Storage\Entities\Propose;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ProposeController extends Controller
{
    use  ApiResponse, ValidatesRequests;
    public function index()
    {
        return $this->dataResponse(Propose::all(),200);
    }

    public function store(ProposeRequest $proposeRequest)
    {
        $propose=Propose::query()->create($proposeRequest->all());
        if ($proposeRequest->hasFile('offer_file')){
            $propose->offer_file=$this->uploadImage($proposeRequest->company_id,$proposeRequest->offer_file);
        }

        return $this->successResponse($propose,200);
    }

    public function delete($id)
    {

        $propose=Propose::query()->findOrFail($id);
        $propose->delete();
        return response()->json(['message'=>'Teklif silindi!'],200);
    }

    public function uploadImage($company_id, $file, $str = 'storages')
    {
        if ($file instanceof UploadedFile) {
            return $file->store("/documents/$company_id/$str");
        }

        return null;
    }
}
