<?php

namespace Modules\Storage\Http\Controllers;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Storage\Entities\Income;
use Modules\Storage\Entities\Outcome;

class ReportController extends Controller
{
    use ApiResponse , ValidatesRequests;


    /**
     * @var \Closure
     */
    private $filter;

    public function __construct(Request $request)
    {
        $this->validate($request , [
            'storage_id' => ['nullable' , 'integer'],
            'per_page' => ['sometimes' , 'required' , 'integer']
        ]);
        // for product filter
        $this->filter = function ($q) use ($request) {
            $q->with("kind:id,name,unit_id" ,'kind.unit' );
            if ($request->get('storage_id')) $q->where('storage_id', $request->get('storage_id'));
        };
    }

    public function income(Request $request){
        $income = Income::with(['product' => $this->filter , 'product.title' , 'product.kind' , 'user:id,name,surname'])
            ->where('company_id' , $request->get('company_id'))
            ->paginate($request->get('per_page'));

        return $this->successResponse($income);
    }

    public function outcome(Request $request){
        $income = Outcome::with(['product' => $this->filter , 'product.title' , 'product.kind' , 'user:id,name,surname'])
            ->where('company_id' , $request->get('company_id'))
            ->paginate($request->get('per_page'));

        return $this->successResponse($income);
    }
}
