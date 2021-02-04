<?php
namespace Modules\Esd\Http\Controllers;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;

use Modules\Esd\Entities\sendForm;
use Modules\Esd\Entities\sendType;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Esd\Entities\Section;
use Illuminate\Support\Facades\DB;

class SectionController extends Controller
{
    use ApiResponse  ,ValidatesRequests;
    /**
     * Display a listing of the resource.
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try{
            $section = Section::all();
            return $this->successResponse($section);
        }catch (\Exception $e){
            return $this->errorResponse(trans("apiResponse.tryLater"));
        }
    }
    public function getSendTypes(){
      $data =  sendType::all(['id' , 'name']);
        return $this->successResponse($data);
    }

    public function getSendForms(){
        $data =  sendForm::all(['id' , 'name']);
        return $this->successResponse($data);
    }
}
