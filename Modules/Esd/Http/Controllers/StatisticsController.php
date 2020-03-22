<?php


namespace Modules\Esd\Http\Controllers;


use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Entities\Document;
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
        $statistics =  Document::where('documents.status' , "!=" , config('modules.document.status.draft'))
            ->where('documents.company_id' , $request->company_id)
            ->join('sections' , 'sections.id' , '=' , 'documents.section_id')
            ->groupBy(['sections.name' , 'sections.id'])
            ->get(DB::raw('count(documents.id) as count , sections.name as section , sections.id as section_id '));

//        $total = Document::where('status' , "!=" , config('modules.document.status.draft'))
//            ->where('company_id' , $request->company_id)
//            ->get(DB::raw("count(*) as count"));
        return $this->successResponse([
//            'total' => $total,
            'by_sections' => $statistics
        ]);
    }
}
