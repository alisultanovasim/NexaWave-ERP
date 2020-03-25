<?php


namespace Modules\Esd\Http\Controllers;


use App\Models\User;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Esd\Entities\Assignment;
use Modules\Esd\Entities\AssignmentItem;
use Modules\Esd\Entities\Document;
use Modules\Esd\Entities\Note;
use App\Traits\ApiResponse;
use App\Traits\DocumentBySection;
use App\Traits\DocumentUploader;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controller;
use Modules\Hr\Entities\Employee\Employee;

class AssignmentController extends Controller
{
    use  ApiResponse, DocumentUploader, DocumentBySection  ,ValidatesRequests;

    public function store(Request $request, $id)
    {
        $this->validate($request, [
            "user_ids" => "required|array",
            "user_ids.*" => "required|integer",
            "base" => "required|integer",
            "description" => "sometimes|required",
            "expire_time" => "sometimes|required|date|date_format:Y-m-d",
            'company_id' => 'required|integer',
        ]);
        if (!in_array($request->base, $request->user_ids))
            return $this->errorResponse(["base" => trans("apiResponse.notFound")]);

        $company_id = $request->company_id;
        try {
            DB::beginTransaction();
            $document = Document::where([
                ["id", $id],
                ["company_id", $company_id]
            ])->first(["status", "id"]);


            if (!$document)
                return $this->errorResponse(trans("apiResponse.documentNotFound"));

            if ($document->status !== Document::ACTIVE)
                return $this->errorResponse(['error' => trans("apiResponse.docStatusError", ["status" => $document->status])]);

            if ($request->has('expire_time')) $document->update(['expire_time' => $request->expire_time]);


            $helper = array_unique($request->user_ids);

            if (!$this->checkUser($helper , $request))
             return $this->errorResponse(trans('response.EmployeesIsIncorrect'));

            $assignment = Assignment::create(array_merge($request->only('description', 'expire_time'), [
                'document_id' => $document->id
            ]));
            $assignmentItems = [];
            foreach ($helper as $k => $v) {
                $assignmentItems[$k] = [
                    "user_id" => $v,
                    "assignment_id" => $assignment->id,
                ];
                if ($request->base == $v)
                    $assignmentItems[$k]["is_base"] = 1;
                else
                    $assignmentItems[$k]["is_base"] = 0;
            }
            AssignmentItem::insert($assignmentItems);

            DB::commit();
            return $this->successResponse("OK");
        } catch (QueryException $e) {
            DB::rollBack();

            if ($e->errorInfo[1] == 1062) {
                return $this->errorResponse(['error' => trans("apiResponse.assignmentAlreadyExists")]);
            }
            return $this->errorResponse(trans("apiResponse.tryLater"), Response::HTTP_INTERNAL_SERVER_ERROR);


        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse(trans("apiResponse.tryLater"), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'company_id' => 'required|integer',

            "description" => "sometimes|required",
            "expire_time" => "sometimes|required|date|date_format:Y-m-d",
            'user_ids' => 'sometimes|required|array',
            'user_ids.*' => 'sometimes|required|integer',
            'base' => 'required_with:user_ids|integer'
        ]);
        try {
            DB::beginTransaction();
            $document = Document::where([
                ["id", $id],
                ["company_id", $request->company_id],
            ])->first(["status"]);
            if (!$document)
                return $this->errorResponse(trans("apiResponse.unProcess"));
            if ($document->status !== Document::ACTIVE)
                return $this->errorResponse(trans("apiResponse.docStatusError", ["status" => $document->status]));

            $assignment = Assignment::where('document_id', $id)->first('id');

            $arr = $request->only('expire_time', 'description');
            if ($arr) {
                $check = Assignment::where('id', $assignment->id)->update($arr);
                if (!$check)
                    return $this->errorResponse(trans("apiResponse.unProcess"));
            }

            if ($request->has('user_ids')) {
                if (!in_array($request->base, $request->user_ids))
                    return $this->errorResponse(["base" => trans("apiResponse.notFound")]);

                $helper = array_unique($request->user_ids);
                if (!$this->checkUser($helper , $request))
                    return $this->errorResponse(trans('response.unProcess'));


                $allItems = AssignmentItem::where('assignment_id', $assignment->id)->pluck('user_id')->toArray();

                $check = AssignmentItem::where('assignment_id', $assignment->id)
                    ->where('is_base', 1)
                    ->where('user_id', "!=", $request->base)
                    ->update(['is_base' => 0]);
                $needToDelete = array_diff($allItems, $helper);
                if ($needToDelete) {
                    AssignmentItem::whereIn('user_id', $needToDelete)->delete();
                }

                $needToAdd = array_diff($helper, $allItems);
                if ($needToAdd) {
                    $assignmentItems = [];
                    foreach ($needToAdd as $v) {
                        $assignmentItems[] = [
                            "user_id" => $v,
                            "assignment_id" => $assignment->id
                        ];
                    }
                    AssignmentItem::insert($assignmentItems);
                }

                if ($check) {
                    AssignmentItem::where('assignment_id', $assignment->id)
                        ->where('user_id', "=", $request->base)
                        ->update(['is_base' => 1]);
                }
            }


            DB::commit();


            return $this->successResponse('OK');

        } catch (\Exception $e) {
            return $this->errorResponse(trans('apiResponse.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function delete(Request $request, $id)
    {
        $this->validate($request, [
            'company_id' => 'required|integer',

        ]);
        $company_id = $request->company_id;
        try {
            $document = Document::where("id", $id)
                ->where("company_id", $company_id)
                ->exists();
            if (!$document)
                return $this->errorResponse(trans("apiResponse.documentNotExists"));
            Assignment::where("document_id", $id)
                ->delete();

            return $this->successResponse("OK");

        } catch (\Exception $e) {
            return $this->errorResponse(trans("apiResponse.tryLater"), Response::HTTP_INTERNAL_SERVER_ERROR);

        }

    }

    public function addUsersByMainAssignment(Request $request, $id)
    {
        $this->validate($request, [

            'company_id' => ['required', 'integer'],
            'user_ids' => ['required', 'array', 'max:255'],
            'user_ids.*' => ['required', 'integer']
        ]);
        try {


            $helper = array_unique($request->user_ids);
            if (!$this->checkUser($helper , $request))
                return $this->errorResponse(trans('response.unProcess'));



            $assignment = Assignment::whereHas('document', function ($q) use ($request, $id) {
                $q->where('company_id', $request->company_id)
                    ->where('status', Document::ACTIVE)
                    ->where('id', $id);
            })->first(['id']);

            if (!$assignment) return $this->errorResponse(['assignment' => trans('apiResponse.assignmentNotFound')]);




            $base = AssignmentItem::where('assignment_id', $assignment->id)->where('user_id', $helper)->where('is_base', 1)->first(['id']);
            if (!$base) return $this->errorResponse(['error' => trans('apiResponse.permissionDeny')]);

            AssignmentItem::where('assignment_id', $assignment->id)
                ->whereNotIn('user_id', $helper)
                ->where('parent_id', $base->id)
                ->delete();

            $items = [];

            foreach ($helper as $user_id) {
                $items[] = [
                    'parent_id' => $base->id,
                    'assignment_id' => $assignment->id,
                    'user_id' => $user_id,
                    'status' => AssignmentItem::WAIT
                ];
            }

            AssignmentItem::insertOrIgnore($items);

            return $this->successResponse('ok');

        } catch (\Exception $exception) {
            return $this->errorResponse(trans('apiResponse.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(Request $request, $id)
    {
        $this->validate($request, [

            'company_id' => 'required|integer',
        ]);
        $company_id = $request->company_id;
        try {
            $document = Document::where("id", $id)
                ->where("company_id", $company_id)
                ->exists();
            if (!$document)
                return $this->errorResponse(trans("apiResponse.documentNotExists"));
            $assignment = Assignment::with(['items', 'items.notes', 'items.users:id,name,surname'])
                ->where('document_id', $id)->first();
            return $this->successResponse($assignment);
        } catch (\Exception $e) {
            dd($e);
            //return $this->errorResponse(trans("apiResponse.tryLater"), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function index(Request $request)
    {
        $this->validate($request, [
            "per_page" => "sometimes|required|integer",
            "finished_at" => "sometimes|required|date|date_format:Y-m-d",

            'company_id' => 'required|integer',
            'tome' => 'sometimes|required|integer|int:1,0'

        ]);
        try {
            $assignments = Assignment::with(['items' ,'items.users'  ])
                ->whereHas('document', function ($q) use ($request) {
                    $q->where('company_id', $request->company_id);
                    if ($request->has('finished_at'))
                        $q->where(DB::raw('DATE(expire_time)'), '<=', $request->finished_at)->whereNotNull('expire_time');
                });
            if ($request->has('tome')) {
                $assignments->whereHas('items', function ($q) use ($request) {
                    if ($request->tome)
                        $q->where('user_id', $request->user_id);
                    else
                        $q->where('user_id', "!=", $request->user_id);
                });
            }
            $assignments = $assignments->paginate($request->per_page ?? 10);

            return $this->successResponse($assignments);
        } catch (\Exception $e) {
            return $this->errorResponse(trans("apiResponse.tryLater"), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function addNotes(Request $request, $id)
    {
        $this->validate($request, [
            "notes" => "required|array",
            "notes.*" => "required",

            'company_id' => 'required|integer'
        ]);
        $company_id = $request->company_id;
        try {
            $document = Document::where("id", $id)
                ->where("company_id", $company_id)
                ->where("status", Document::ACTIVE)
                ->exists();
            if (!$document)
                return $this->errorResponse(trans("apiResponse.unProcess"));

            $assignment = Assignment::where("document_id", $id)
                ->first('id');
            if (!$assignment)
                return $this->errorResponse(trans("apiResponse.unProcess"));


            $assignmentItem = AssignmentItem::where('assignment_id', $assignment->id)
                ->where('user_id', $request->user_id)
                ->first('id');
            if (!$assignmentItem)
                return $this->errorResponse(trans('apiResponse.itemNotFound'));

            Note::insert($this->saveNotes($assignmentItem, $request->notes, $request, $str = 'notes'));

            return $this->successResponse("OK");
        } catch (\Exception $e) {
            return $this->errorResponse(trans("apiResponse.tryLater"), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function updateNote(Request $request, $id)
    {
        $this->validate($request, [
            "note" => "required",
            "note_id" => "required|integer",

            'company_id' => 'required|integer',
        ]);
        $company_id = $request->company_id;

        try {
            $document = Document::where("id", $id)
                ->where("company_id", $company_id)
                ->where("status", Document::ACTIVE)
                ->exists();
            if (!$document)
                return $this->errorResponse(trans("apiResponse.unProcess"));

            $assignment = Assignment::where("document_id", $id)
                ->first('id');
            if (!$assignment)
                return $this->errorResponse(trans("apiResponse.unProcess"));


            $assignmentItem = AssignmentItem::where('assignment_id', $assignment->id)
                ->where('user_id', $request->user_id)
                ->first('id');

            if (!$assignmentItem) return $this->errorResponse(trans('apiResponse.assignmentItemNotFound'));

            $note = Note::where("id", $request->note_id)
                ->where('assignment_item_id', $assignmentItem->id)
                ->first();
            if (!$note)
                return $this->errorResponse(trans("apiResponse.noteNotFound"));

            $versions = json_decode($note->versions, true);
            $addingVersions = [
                "resource" => $note->resource,
                "type" => $note->type,
                'size' => $note->size
            ];
            $versions = $versions ?? [];
            array_push($versions, $addingVersions);


            $note->versions = json_encode($versions);

            $note->fill($this->saveNote($assignmentItem, $request->note, $request, 'notes'));

            $note->save();

            return $this->successResponse("OK");
        } catch (\Exception $e) {
            return $this->errorResponse(trans("apiResponse.tryLater"), Response::HTTP_INTERNAL_SERVER_ERROR);

        }
    }

    public function deleteNote(Request $request, $id)
    {
        $this->validate($request, [
            'company_id' => 'required|integer',
            'user_id' => 'required|integer'

        ]);
        try {
            $document = Document::where("id", $id)
                ->where("company_id", $request->company_id)
                ->where("status", Document::ACTIVE)
                ->exists();
            if (!$document)
                return $this->errorResponse(trans("apiResponse.docStatusOrNotFound"));

            $assignment = Assignment::where("document_id", $id)
                ->first('id');
            if (!$assignment)
                return $this->errorResponse(trans("apiResponse.assignmentNotFound"));


            $assignmentItem = AssignmentItem::where('assignment_id', $assignment->id)
                ->where('user_id', $request->user_id)
                ->first('id');

            if (!$assignmentItem)
                return $this->errorResponse(trans("apiResponse.notAssignmentToYou"));


            $check = Note::where('assignment_item_id', $assignmentItem->id)
                ->where('id', $request->note_id)
                ->delete();
            if (!$check)
                return $this->errorResponse(trans("apiResponse.unProcess"));

            return $this->successResponse('OK');
        } catch (\Exception $e) {
            return $this->errorResponse(trans('apiResponse.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function markAsRead(Request $request, $id)
    {
        $this->validate($request, [

            'company_id' => 'required|integer'
        ]);
        $company_id = $request->company_id;
        try {
            $assignment = Assignment::whereHas('document', function ($q) use ($id, $company_id) {
                $q->where("id", $id)
                    ->where("company_id", $company_id)
                    ->where('status', Document::ACTIVE);
            })->where("document_id", $id)
                ->first('id');

            if (!$assignment)
                return $this->errorResponse(trans("apiResponse.unProcess"));


            $check = AssignmentItem::where('assignment_id', $assignment->id)
                ->where('user_id', $request->user_id)
                ->update([
                    'status' => AssignmentItem::WAIT
                ]);
            if (!$check)
                return $this->errorResponse(trans("apiResponse.unProcess"));
            return $this->successResponse("OK");

        } catch (\Exception $e) {
            return $this->errorResponse(trans("apiResponse.tryLater"), Response::HTTP_INTERNAL_SERVER_ERROR);

        }
    }

    public function done(Request $request, $id)
    {
        $this->validate($request, [

            'company_id' => 'required|integer',
            'return' => 'sometimes|required|boolean'
        ]);
        $company_id = $request->company_id;
        try {

            $assignment = Assignment::whereHas('document', function ($q) use ($id, $company_id) {
                $q->where("id", $id)
                    ->where("company_id", $company_id)
                    ->where('status', Document::ACTIVE);
            })
                ->where("document_id", $id)
                ->first(['id', 'document_id']);
            if (!$assignment)
                return $this->errorResponse(trans("apiResponse.unProcess"));

            $assignmentItem = AssignmentItem::where('assignment_id', $assignment->id)
                ->where('user_id', $request->user_id)
                ->first(['id', 'is_base']);

            if (!$assignmentItem)
                return $this->errorResponse(trans("apiResponse.unProcess"));
            if ($assignmentItem->is_base and !$request->return) {
                $check = AssignmentItem::where('assignment_id', $assignment->id)
                    ->where('parent_id', $assignmentItem->id)
                    ->where('status', "!=", AssignmentItem::DONE)
                    ->exists();
                if ($check)
                    return $this->errorResponse(['error' => trans('apiResponse.subAssignedUserNotFinishWork')]);
            }

            if ($this->LastMakeDone($assignment->id , $assignmentItem->id)) Document::where('id', $id)->update(['status' => Document::WAIT_FOR_ACCEPTANCE]);
            else if ($assignmentItem->is_base and $this->issetSubAssigners($assignment->id)) return  $this->errorResponse(['error' => trans('apiResponse.subAssignedUserNotFinishWork')]);

            $check = $assignmentItem->update(["status" => ($request->return) ? AssignmentItem::WAIT : AssignmentItem::DONE]);

            if (!$check)
                return $this->errorResponse(trans("apiResponse.unProcess"));

            return $this->successResponse("OK");

        } catch (\Exception $e) {
            $this->errorResponse(trans('apiResponse.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function changeStatus(Request $request, $id)
    {
        $this->validate($request, [

            'company_id' => 'required|integer',
            'return' => 'sometimes|required|boolean',
            'status' => 'required|integer|in:1,2,3'
        ]);

        try {
            DB::beginTransaction();
            $assignment = Assignment::whereHas('document', function ($q) use ($id, $request) {
                $q->where("id", $id)
                    ->where("company_id", $request->company_id)
                    ->where('status', Document::ACTIVE);
            })
                ->first(['id', 'document_id']);
            if (!$assignment)
                return $this->errorResponse(trans("apiResponse.unProcess"));

            $assignmentItem = AssignmentItem::where('assignment_id', $assignment->id)
                ->where('user_id', $request->user_id)
                ->first(['id', 'is_base']);
            if (!$assignmentItem)
                return $this->errorResponse(trans("apiResponse.unProcess"));

            if ($request->status == AssignmentItem::DONE){
                if ($this->LastMakeDone($assignment->id , $assignmentItem->id)) Document::where('id', $id)->update(['status' => Document::WAIT_FOR_ACCEPTANCE]);
                else if ($assignmentItem->is_base and $this->issetSubAssigners($assignment->id)) return  $this->errorResponse(['error' => trans('apiResponse.subAssignedUserNotFinishWork')]);
            }
            AssignmentItem::where('id', $assignmentItem->id)
                ->update([
                    'status' => $request->status
                ]);
            DB::commit();
            return $this->successResponse('OK');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->errorResponse(trans('apiResponse.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    protected function LastMakeDone($assignmentId , $without = null){
        $obj = AssignmentItem::where('assignment_id', $assignmentId);
            if($without != null)
                $obj->where('id' , '!=' ,$without);

        $bool = $obj->where('status', '!=', AssignmentItem::DONE) ->exists();

        return !$bool;
    }

    protected function issetSubAssigners($assignmentId){
        return AssignmentItem::where('parent_id',$assignmentId)
            ->where('status', "!=", AssignmentItem::DONE)
            ->exists();
    }

    private function checkUser(array $helper, Request $request)
    {
        $employees = Employee::whereIn('user_id' , $helper)
            ->where('company_id', $request->get('company_id'))
            ->where('is_active' , true)
            ->count();
         return count($helper) == $employees;
    }
}
