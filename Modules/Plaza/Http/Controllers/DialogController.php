<?php


namespace Modules\Plaza\Http\Controllers;


use App\Models\User;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Auth;
use Modules\Hr\Entities\Employee\Employee;
use Modules\Plaza\Entities\Dialog;
use Modules\Plaza\Entities\Message;
use Modules\Plaza\Entities\Office;
use App\Traits\ApiResponse;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controller;
class DialogController extends Controller
{
    use ApiResponse  , ValidatesRequests;


    public function getDialogWithOffices(Request $request)
    {
        $this->validate($request, [
            'company_id' => 'required|integer',
            'office_id' => 'sometimes|required|integer',
            'kind_id' => 'sometimes|required|integer',
            'from_office' => 'sometimes|required|in:0,1',
            'per_page' => 'sometimes|required|integer',
            'tome' => 'sometimes|required|boolean'
        ]);
        try {
            DB::beginTransaction();
            $dialog = Dialog::with(['office:id,name', 'kind:id,title' , 'user:id,name'])->where('company_id', $request->company_id);

            if ($request->has('kind_id')) $dialog->where('kind_id', $request->kind_id);
            if ($request->has('office_id')) $dialog->where('office_id', $request->office_id);
            if ($request->has('from_office')) $dialog->where('from_office', $request->from_office);
            if ($request->get('tome')) $dialog->where('assigned_user', Auth::id());


            $dialogs = $dialog->orderBy('id', 'desc')->paginate($request->per_page ?? 10);

            DB::commit();
            return $this->successResponse($dialogs);
        } catch (\Exception $exception) {
            dd($exception);
            DB::rollBack();
            return $this->errorResponse(trans('apiResponse.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function showDialogWithOffices(Request $request, $id)
    {
        $this->validate($request, [
            'company_id' => 'required|integer',

            'per_page' => 'sometimes|required|integer'
        ]);
        try {

            $check = Dialog::with(['kind:id,title', 'office:id,name' , "user:id,name"])->where('company_id', $request->company_id)->where('id', $id)->first();
            if (!$check) return $this->errorResponse(trans('apiResponse.DialogNotFound'));

            if ($check->status  == 2){
                $check->status = 0;
                Dialog::where('id', $id)->update([
                    'status'=>0
                ]);
            }


            Message::where('dialog_id', $id)->where(['from_office' => 1])->update(['is_read' => 1]);

            $message = Message::where('dialog_id', $id)->orderBy('id', 'desc')->get();

            $finder = [];
            foreach (config('static-data.part') as $data){
                $partName = $data['name'];
                foreach ($data['users'] as $d){
                    $userName = $d['name'];
                    if ($d['id'] == $check->assigned_user ){
                        $finder['part'] = [
                            'id' => $data['id'],
                            'name' => $partName,
                            'user' => [
                                'id' => $d['id'],
                                'name'=>$userName
                            ]
                        ];
                        break;
                    }
                }
            }
            $check->assigned_user = $finder;
            return $this->successResponse([
                'messages' => $message,
                'dialog' => $check
            ]);
        } catch (\Exception $exception) {
            return $this->errorResponse(trans('apiResponse.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function createToOffice(Request $request)
    {
        $this->validate($request, [
            'company_id' => 'required|integer',

            'office_id' => 'required|integer',
            'kind_id' => 'required|integer',
            'body' => 'required|string',
            'theme' => 'required|string'
        ]);
        try {
            $office = Office::where('id', $request->office_id)->where('company_id', $request->company_id)->exists();

            if (!$office) return $this->errorResponse(trans('apiResponse.officeNotFound'));

            $dialog = Dialog::create($request->only('company_id', 'user_id', 'office_id', 'kind_id', 'theme') + ['from_office' => 0]);

            Message::create([
                    'dialog_id' => $dialog->id,
                    'from_office' => 0
                ] + $request->only('body'));
            return $this->successResponse('OK');
        } catch (QueryException  $e) {
            if ($e->errorInfo[1] == 1452) {
                if (preg_match("/\(\`[a-z\_]+\`\)/", $e->errorInfo[2], $find)) {
                    $info = substr($find[0], 2, -2);
                    return $this->errorResponse([$info => "does not exist"], Response::HTTP_UNPROCESSABLE_ENTITY);
                }
            }
            if ($e->errorInfo[1] == 1062) {
                if (preg_match("/offices_(.*)_unique/", $e->getMessage(), $find)) {
                    return $this->errorResponse([$find[1] => trans('apiResponse.alreadyExists')], Response::HTTP_UNPROCESSABLE_ENTITY);
                }
            }
            return $this->errorResponse(trans('apiResponse.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Exception $e) {
            return $this->errorResponse(trans('apiResponse.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function updateDialogForPlaza(Request $request, $id)
    {
        $this->validate($request, [
            'assigned_user' => 'sometimes|required|integer',
            'note' => 'sometimes',
            'end_time' => 'sometimes|required|date|date_format:Y-m-d',
            'company_id' => 'sometimes|required|integer',
            'status' => 'sometimes|required|integer|in:0,1',

        ]);
        try {
            $check = Dialog::where('id', $id)->where('company_id', $request->company_id)->exists();
            if (!$check) return $this->errorResponse('apiResponse.DialogNotFound' ,404);

            if ($request->has('assigned_user')){
                $check = Employee::where('user_id' , $request->get('assigned_user'))
                    ->where('company_id' , $request->get('assigned_user'))
                    ->where('is_active' , true)
                    ->exists();
                if (!$check) return $this->errorResponse('apiResponse.EmployeeNotFoundNotFound' , 404);
            }

           Dialog::where('id', $id)->update($request->only('assigned_user', 'note', 'end_time', 'status'));

            return $this->successResponse('OK');
        } catch (\Exception $e) {
            return $this->errorResponse(trans('apiResponse.tryLater'));
        }
    }

    public function addMessageFromPlaza(Request $request, $id)
    {
        $this->validate($request, [
            'company_id' => 'required|integer',

            'body' => 'required|string'
        ]);
        try {
            $dialog = Dialog::where('company_id', $request->company_id)->where('id', $id)->exists();
            if (!$dialog) return $this->errorResponse(trans('apiResponse.dialogNotFound'));
            Message::create($request->only('body') + ['from_office' => 0, 'dialog_id' => $id]);
            return $this->successResponse('OK');
        } catch (\Exception $e) {
            return $this->errorResponse(trans('apiResponse.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getDialogWithPlaza(Request $request)
    {
        $this->validate($request, [
            'company_id' => 'required|integer',

            'office_id' => 'required|required|integer',
            'kind_id' => 'sometimes|required|integer',
            'per_page' => 'sometimes|required|integer',
            'from_office' => 'sometimes|required|in:0,1',
        ]);
        try {
            $dialog = Dialog::with('office:id,name', 'kind:id,title' , "user:id,name")->where('company_id', $request->company_id)->where('office_id', $request->office_id);

            if ($request->has('kind_id')) $dialog->where('kind_id', $request->kind_id);
            if ($request->has('from_office')) $dialog->where('from_office', $request->from_office);

            $dialog = $dialog->orderBy('id', 'desc')->paginate($request->per_page ?? 10);
            return $this->successResponse($dialog);
        } catch (\Exception $exception) {
            return $this->errorResponse(trans('apiResponse.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function updateDialogForOffice(Request $request, $id)
    {
        $this->validate($request, [
            'kind_id' => 'required|integer',
            'body' => 'required|string',
            'theme' => 'required|string'
        ]);
        try {
            $dialog = Dialog::where('id', $id)->where('company_id', $request->company_iod)->where('office_id', $request->office_id)->first('status');
            if (!$dialog) return $this->errorResponse('apiResponse.DialogNotFound');

            if ($dialog->status !== 2) return $this->errorResponse('apiResponse.statusError');

            $check = Dialog::where('id', $id)->update($request->only('kind_id', 'body', 'theme'));
            if (!$check) return $this->errorResponse('apiResponse.unProcess');

            return $this->successResponse('OK');
        } catch (\Exception $e) {
            return $this->errorResponse(trans('apiResponse.tryLater'));
        }
    }

    public function showDialogWithPlaza(Request $request, $id)
    {
        $this->validate($request, [
            'company_id' => 'required|integer',

            'office_id' => 'required|integer',
            'per_page' => 'sometimes|required|integer'
        ]);
        try {
            $check = Dialog::with(['kind:id,title', 'office:id,name' , "user:id,name"])->where('company_id', $request->company_id)->where('office_id', $request->office_id)->where('id', $id)->first();
            if (!$check) return $this->errorResponse(trans('apiResponse.DialogNotFound'));

            Message::where('dialog_id', $id)->where(['from_office' => 1])->update(['is_read' => 1]);

            $message = Message::where('dialog_id', $id)->orderBy('id', 'desc')->get();

            return $this->successResponse([
                'messages' => $message,
                'dialog' => $check
            ]);

        } catch (\Exception $exception) {
            return $this->errorResponse(trans('apiResponse.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function createToPlaza(Request $request)
    {
        $this->validate($request, [
            'company_id' => 'required|integer',

            'office_id' => 'required|integer',
            'kind_id' => 'required|integer',
            'body' => 'required|string',
            'theme' => 'required|string'
        ]);
        try {
            DB::beginTransaction();
            $office = Office::where('id', $request->office_id)->where('company_id', $request->company_id)->exists();

            if (!$office) return $this->errorResponse(trans('apiResponse.officeNotFound'));

            $dialog = Dialog::create($request->only('company_id', 'user_id', 'office_id', 'kind_id', 'theme') + ['from_office' => 1 ,
                    'status'=>2]);

            Message::create([
                    'dialog_id' => $dialog->id
                ] + $request->only('body') + ['from_office' => 1]);
            DB::commit();
            return $this->successResponse('OK');
        } catch (QueryException  $e) {
            DB::rollBack();
            if ($e->errorInfo[1] == 1452) {
                if (preg_match("/\(\`[a-z\_]+\`\)/", $e->errorInfo[2], $find)) {
                    $info = substr($find[0], 2, -2);
                    return $this->errorResponse([$info => "does not exist"], Response::HTTP_UNPROCESSABLE_ENTITY);
                }
            }
            if ($e->errorInfo[1] == 1062) {
                if (preg_match("/offices_(.*)_unique/", $e->getMessage(), $find)) {
                    return $this->errorResponse([$find[1] => trans('apiResponse.alreadyExists')], Response::HTTP_UNPROCESSABLE_ENTITY);
                }
            }

            return $this->errorResponse(trans('apiResponse.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse(trans('apiResponse.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function addMessageFromOffice(Request $request, $id)
    {
        $this->validate($request, [
            'company_id' => 'required|integer',

            'body' => 'required|string'
        ]);
        try {
            $dialog = Dialog::where('company_id', $request->company_id)->where('id', $id)->exists();

            if (!$dialog) return $this->errorResponse(trans('apiResponse.dialogNotFound'));

            Message::create($request->only('body') + ['from_office' => 1, 'dialog_id' => $id]);

            return $this->successResponse('OK');
        } catch (\Exception $e) {
            return $this->errorResponse(trans('apiResponse.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
