<?php
namespace Modules\Esd\Http\Controllers;
use App\Traits\ApiResponse;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Modules\Esd\Entities\Document;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class ArchiveController extends Controller
{
    use  ApiResponse  ,ValidatesRequests;

    public function store(Request $request)
    {
        $this->validate($request , [
            'documents' => ['sometimes' , 'required' , 'array'],
            'documents.*' => ['required' , 'integer'] ,
            'company_id' => [ 'required' , 'integer'] ,
        ]);
        try {
            Document::whereIn('id' , $request->documents)->where('company_id' , $request->company_id)->update([
                'status' => Document::ARCHIVE
            ]);
            return $this->successResponse('OK');
        } catch (\Exception $e) {
            return $this->errorResponse(trans('apiResponse.tryLater') , Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(Request $request , $id){
        $this->validate($request , [
            'folder' => ['sometimes' , 'required' , 'string' , 'max:255']
        ]);
        try{
            Document::where('id' , $id)
                ->where('company_id', $request->company_id )
                ->where('status' , Document::ARCHIVE)
                ->update([
                'folder' => $request->folder
            ]);
            return $this->successResponse('OK');
        }catch (\Exception $exception){
            return $this->errorResponse(trans('apiResponse.tryLater') , Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
