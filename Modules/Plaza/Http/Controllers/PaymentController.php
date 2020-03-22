<?php


namespace Modules\Plaza\Http\Controllers;
use Carbon\Carbon;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Plaza\Entities\Additive;
use Modules\Plaza\Entities\Office;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controller;
class PaymentController extends Controller
{
    use  ApiResponse  , ValidatesRequests;

    public function pay(Request $request, $id)
    {
        $this->validate($request, [
            'company_id' => 'required|integer',
            'user_id' => 'required|integer',
        ]);

        try {
            DB::beginTransaction();

            $office = Office::where('company_id', $request->company_id)->where('id', $id)->first('month_count', 'payed_month_count');
            if (!$office) return $this->errorResponse('apiResponse.officeNotFound');

            if ($office->month_count <= $office->payed_month_count)
                return $this->errorResponse(trans('apiResponse.monthError'));

            $check = Office::where('id', $id)
                ->update(['payed_month_count' => DB::raw('payed_month_count + 1')]);

            if (!$check) return $this->errorResponse('apiResponse.unProcess');

            DB::commit();

            return $this->successResponse('OK');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse(trans('apiResponse.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function payForPunishment(Request $request, $id)
    {
        $this->validate($request, [
            'company_id' => 'required|integer',
            'user_id' => 'required|integer',
            'additive_id' => 'required|integer'
        ]);
        try {
            $office = Office::where('id', $id)->where('company_id', $request->company_id)->first();

            if (!$office) return $this->errorResponse('apiResponse.officeNotFound');

            $check = Additive::where('id', $request->additive_id)->where('office_id', $id)->update([
                'payed_at' => Carbon::now()->timezone('Asia/Baku')->toDateTimeString()
            ]);
            if (!$check) return $this->errorResponse(trans('apiResponse.unProcess'));
            return $this->successResponse('OK');
        } catch (\Exception $e) {
            return $this->errorResponse(trans('apiResponse.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function index(Request $request, $id)
    {
        $this->validate($request, [
            'company_id' => 'required|integer',
            'user_id' => 'required|integer',
        ]);
        try {
            $office = Office::where('id', $id)->where('company_id', $request->company_id)->first();
            if (!$office) return $this->errorResponse('apiResponse.officeNotFound');

            $additives = Additive::where('office_id', $id)->get();

            return $this->successResponse(["additives" => $additives, "office" => $office]);
        } catch (\Exception $e) {
            return $this->errorResponse(trans('apiResponse.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function createAdditive(Request $request , $id){
        $this->validate($request, [
            'company_id' => 'required|integer',
            'user_id' => 'required|integer',
            'days' => 'required|integer',
            'payed_at' => 'sometimes|required|date|date_format:Y-m-d',
            'month' => 'required|integer'
        ]);
        try {
            $office = Office::where('id', $id)->where('company_id', $request->company_id)->exists();
            if (!$office) return $this->errorResponse('apiResponse.officeNotFound');

            Additive::create($request->only('days' , 'month' , 'payed_at') + ['office_id' => $id]);

            return $this->successResponse('OK');

        } catch (\Exception $e) {
            return $this->errorResponse(trans('apiResponse.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function updateAdditive(Request $request, $id)
    {
        $this->validate($request, [
            'company_id' => 'required|integer',
            'user_id' => 'required|integer',
            'additive_id' => 'required|integer',
            'days' => 'sometimes|required|integer',
            'payed_at' => 'sometimes|date|date:format:Y-m-d'
        ]);
        $arr = $request->only('days' , 'payed_at');
        if (!$arr) return $this->errorResponse(trans('apiResponse.nothing'));

        try {
            $office = Office::where('id', $id)->where('company_id', $request->company_id)->exists();
            if (!$office) return $this->errorResponse('apiResponse.officeNotFound');

            $additives = Additive::where('id', $request->additive_id)->where('office_id', $id)->first();

            if (!$additives) return $this->errorResponse(trans('apiResponse.additiveNotFound'));

            $additives->update($arr);

            return $this->successResponse('OK');

        } catch (\Exception $e) {
            return $this->errorResponse(trans('apiResponse.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function deleteAdditive(Request $request, $id)
    {
        $this->validate($request, [
            'company_id' => 'required|integer',
            'user_id' => 'required|integer',
            'additive_id' => 'required|integer'
        ]);
        try {
            $office = Office::where('id', $id)->where('company_id', $request->company_id)->first();
            if (!$office) return $this->errorResponse('apiResponse.officeNotFound');

            $additives = Additive::where('id', $request->additive_id)->where('office_id', $id)->delete();

            if (!$additives) return $this->errorResponse(trans('apiResponse.additiveNotFound'));

            return $this->successResponse('OK');

        } catch (\Exception $e) {
            return $this->errorResponse(trans('apiResponse.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
