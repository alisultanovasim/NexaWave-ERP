<?php


namespace Modules\Esd\Http\Controllers;


use App\Traits\ApiResponse;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Routing\Controller;

class DataController extends Controller
{

    use ApiResponse  ,ValidatesRequests;
    public function index(Request $request)
    {
        $conf = config('esd.document.data');
        $this->validate($request, [

            'company_id' => ['required', 'integer'],
            'data' => ['required', 'array'],
            'data.*' => ['required', 'string', 'max:255',
                Rule::in(array_keys($conf))
            ]
        ]);
        try {
            $data = [];
            foreach ($request->data as $table) {
                if ($conf[$table])
                    $data[$table] = DB::table($table)->where('company_id', $request->company_id)->get();
                else
                    $data[$table] = DB::table($table)->get();

            }
            return $this->successResponse($data);
        } catch (\Exception $exception) {
            return $this->errorResponse(trans('apiResponse.tryLater') , Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}
