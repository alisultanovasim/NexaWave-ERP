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

class ProposeController extends Controller
{
    use  ApiResponse, ValidatesRequests;
    public function index()
    {
        return $this->dataResponse(Propose::all(),200);
    }

    public function store(ProposeRequest $proposeRequest)
    {
        $uploadedFile = $proposeRequest->file('offer_file');
        $filename = time().$uploadedFile->getClientOriginalName();

        Storage::disk('local')->putFileAs(
            'propose/'.$filename,
            $uploadedFile,
            $filename
        );

        $propose=Propose::query()->create($proposeRequest->all());
        return $this->successResponse($propose,200);
    }

    public function delete($id)
    {

        $propose=Propose::query()->findOrFail($id);
        $propose->delete();
        return response()->json(['message'=>'Teklif silindi!'],200);
    }
}
