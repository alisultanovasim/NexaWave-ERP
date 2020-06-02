<?php


namespace Modules\Esd\Http\Controllers;


use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Esd\Entities\Document;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controller;
use Modules\Esd\Entities\Section;

class StatisticsController extends  Controller
{
    use  ApiResponse  ,ValidatesRequests;
    public function documents(Request $request){
        $this->validate($request , [
            'company_id' => 'required|integer',
        ]);

        $data = Section::withCount(['documents' => function ($q){
            $q
            ->where('company_id' , \request('company_id'))
            ->select(DB::raw('count(*)'));
        }])->get();
        return $this->successResponse($data);
    }
}
