<?php


namespace Modules\Esd\Http\Controllers;


use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Esd\Entities\Document;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controller;

class StatisticsController extends  Controller
{
    use  ApiResponse  ,ValidatesRequests;
    public function documents(Request $request){
        $this->validate($request , [
            'company_id' => 'required|integer',

        ]);
        $statistics =  Document::where('documents.status' , "!=" , Document::DRAFT)
            ->where('documents.company_id' , $request->get('company_id'))
            ->join('document_sections' , 'document_sections.id' , '=' , 'documents.section_id')
            ->groupBy(['document_sections.name' , 'document_sections.id'])
            ->get(DB::raw('count(documents.id) as count , document_sections.name as section , document_sections.id as section_id '));

//        $total = Document::where('status' , "!=" , config('esd.document.status.draft'))
//            ->where('company_id' , $request->company_id)
//            ->get(DB::raw("count(*) as count"));
        return $this->successResponse([
//            'total' => $total,
            'by_sections' => $statistics
        ]);
    }
}
