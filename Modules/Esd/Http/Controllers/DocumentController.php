<?php


namespace Modules\Esd\Http\Controllers;

use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Auth;
use Modules\Esd\Entities\AssignmentItem;
use Modules\Esd\Entities\Section;
use App\Traits\DocumentBySection;
use App\Traits\DocumentUploader;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use App\Traits\ApiResponse;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Esd\Entities\Doc;
use Modules\Esd\Entities\Document;
use Illuminate\Routing\Controller;


class DocumentController extends Controller
{
    use  ApiResponse, DocumentUploader, DocumentBySection, ValidatesRequests;

    public function index(Request $request)
    {
        $this->validate($request, [
            'company_id' => 'required|integer',
            'theme' => 'sometimes|required',
            "per_page" => "sometimes|required|integer",
            "status" => "sometimes|required|in:0,1,3,4",
//            "status.*" => "sometimes|required|in:0,1,3,4",
            "time" => "sometimes|required|in:1,0",
            "section_id" => "sometimes|required|array",
            'inner_inspect' => 'sometimes|boolean',
            "section_id.*" => "sometimes|required|integer",
            'tome' => "sometimes|required|in:0,1",
            'send_type' => 'sometimes|required|integer',
            'send_form' => 'sometimes|required|integer',
            'page_count' => 'sometimes|required|integer',
            'copy_count' => 'sometimes|required|integer',
            'page_operation' => 'sometimes|in:>=,<=,<,>',
            'copy_operation' => 'sometimes|in:>=,<=,<,>',
            'to' => "sometimes|required|integer",
            'from' => "sometimes|required|integer",
            'time_from' => 'sometimes|required|date|date_format:Y-m-d',

            'register_time' => 'sometimes|required|date|date_format:Y-m-d',
            'register_time_from' => 'sometimes|required|date|date_format:Y-m-d',
            'register_time_to' => 'sometimes|required|date|date_format:Y-m-d',

            'expire_time' => 'sometimes|required|date|date_format:Y-m-d',
            'expire_time_from' => 'sometimes|required|date|date_format:Y-m-d',
            'expire_time_to' => 'sometimes|required|date|date_format:Y-m-d',

            'time_to' => 'sometimes|required|date|date_format:Y-m-d',
            'sender_comp_id' => "sometimes|required|integer",
            'folder' => 'sometimes|required',
            'document_no' => 'sometimes|required',
            'register_number' => 'sometimes|required',
            'year' => 'sometimes|required|regex:/[0-9]{4}/',
            'order_by' => 'sometimes|required|string|max:255',
            'direction' => 'sometimes|required|in:desc,asc'
        ]);
        try {
            $perPage = $request->has("per_page") ? $request->per_page : 10;
            $documents = Document::with([
                'section:id,name', 'sendForm', 'sendType',
                'assignment' => function ($q) {
                    $q->with(['item' => function ($q) {
                        $q
                            ->whereHas('employee' , function ($q) {
                                $q->where('user_id' , Auth::id());
                            })
                            ->select(['assignment_id' , 'status' , 'is_base']);
                    },'stuck' => function($q){
                        $q->with(['employee:id,user_id' , 'employee.user:id,name,surname'])
                            ->where('status' , AssignmentItem::NOT_SEEN)
                            ->orWhere('status' , AssignmentItem::REJECTED)
                            ->orWhere('status' , AssignmentItem::WAIT);
                    }]);
                },
            ])
                ->where("company_id", $request->company_id)
                ->where("status", "!=", Document::DRAFT);

            /**  Start filter  */
            if ($request->has("status")) {
                $documents->where("status", $request->get('status'));
            }else{
                $documents->where("status", '!=' , Document::ARCHIVE);
            }

            if ($request->has('year'))
                $documents->where(DB::raw('YEAR(created_at)'), $request->year);

            if ($request->has('send_type'))
                $documents->where("send_type", $request->send_type);

            if ($request->has('send_form'))
                $documents->where("send_type", $request->send_form);

//            if ($request->has("from"))
//                $documents->where("from", $request->from);

            if ($request->has("time_from"))
                $documents->where("created_at", ">", $request->time_from);

            if ($request->has("time_to"))
                $documents->where("created_at", "<", $request->time_to);

            if ($request->has("folder"))
                $documents->where("folder", 'like', $request->sender_comp_id . "%");

            if ($request->has("theme"))
                $documents->where("theme", 'like', $request->theme . "%");

            if ($request->has("document_no"))
                $documents->where("document_no", 'like', $request->document_no . "%");

            if ($request->has("register_number"))
                $documents->where("register_number", 'like', $request->register_number . "%");

            if ($request->has('page_count')) {
                $operation = "=";
                if ($request->has('page_operation')) $operation = $request->page_operation;
                $documents->where("page_count", $operation, $request->page_count);
            }

            if ($request->has('copy_count')) {
                $operation = "=";
                if ($request->has('copy_operation')) $operation = $request->copy_operation;
                $documents->where("copy_count", $operation, $request->copy_count);
            }

            if ($request->has("expire_time"))
                $documents->where("expire_time", "{$request->expire_time}");


            if ($request->has("expire_time_to"))
                $documents->where("expire_time",">=" ,   $request->get('expire_time_to'));

            if ($request->has("expire_time_from"))
                $documents->where("expire_time", "<=" , $request->get('expire_time_from'));


            if($request->has('inner_inspect')){
                $documents->where('inner_inspect' , $request->get('inner_inspect'));
            }

            if ($request->has("register_time_to"))
                $documents->where("register_time", ">=" , $request->get('register_time_to'));

            if ($request->has("register_time_from"))
                $documents->where("register_time","<=" ,  $request->get('register_time_from'));


            if ($request->has("register_time"))
                $documents->where("register_time", $request->register_time);

            if ($request->has('to')) {
                $documents->where("company_user", $request->to);
            }

            if ($request->get('tome')){
                $documents->where(function ($q){
                    $q->whereHas('assignment' , function ($q){
                        $q->whereHas('items' ,function ($q){
                            $q->where('user_id' , Auth::id());
                        });
                    })->orWhere('from' , Auth::id());
                });
            }
            if ($request->has('section_id')) {
                $documents->whereIn('section_id', $request->section_id);
                if (count($request->section_id) == 1) {
                    $table = Section::RULES[$request->section_id[array_key_first($request->section_id)]];
                    if (!$table) return $this->errorResponse(['section_id' => trans('apiResponse.sectionIdNotValid')]);
                    $documents->join($table, "documents.id", '=', "$table.document_id");
                }
            }

            /** End filter  */

            $data = [DB::raw('documents.*')];
            if (isset($table)) {
                foreach (config("esd.tables.{$table}") as $column)
                    array_push($data, "$table.$column");
                $documents->WithAllRelations();
            }

            if ($request->has('order_by')) {
                $direction = $request->direction ?? 'desc';
                $documents->orderBy($request->get('order_by'), $direction);
            } else {
                $documents->orderBy("documents.id", "DESC");
            }
            $documents = $documents->paginate($perPage, $data);

            return $this->successResponse($documents);
        } catch (QueryException $ex) {
            dd($ex);
            if ($ex->errorInfo[1] == 1054)
                return $this->errorMessage(['order_by' => ['not valid data']], Response::HTTP_UNPROCESSABLE_ENTITY);
            return $this->errorResponse(trans('apiResponse.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'theme' => 'required|min:2|max:255',
            'base_document' => 'required',
            'document_no' => 'sometimes|required|max:255',
            'register_number' => 'required|min:2|max:255',
            'sub_documents' => 'sometimes|required|array',
            'sub_documents.*' => 'sometimes|required',
            'section_id' => 'required|integer',
            'description' => 'sometimes|required',
            'register_time' => 'sometimes|required|date|date_format:Y-m-d',

            'company_id' => 'required|integer',
            'parent_id' => 'sometimes|required|integer',
            'page_count' => 'sometimes|required|integer',
            'copy_count' => 'sometimes|required|integer',
            'send_type' => 'sometimes|required|integer',
            'send_form' => 'sometimes|required|integer',
            'inner_inspect' => 'sometimes|required|integer|in:0,1',
            'expire_time' => 'sometimes|required|date|date_format:Y-m-d',
            'document_time' => 'sometimes|required|date|date_format:Y-m-d',
            'send_to_user' => 'required|integer|in:0,1',
            'company_user' => 'sometimes|required|integer'
        ]);

        $arr = $request->only(
            'inner_inspect',
            'register_number',
            'document_no',
            'page_count', 'copy_count',
            'send_type', 'send_form', 'expire_time',
            'parent_id', 'company_id', 'theme',
            'description', 'section_id', 'document_time', 'register_time', 'send_to_user', 'company_user');

        $arr['from'] = Auth::id();

        try {
            DB::beginTransaction();
            $document = new  Document;
            if ($request->has('parent_id')) {
                $check = Document::where('id', $request->parent_id)
                    ->where('company_id', $request->company_id)
                    ->where('status', '!=', Document::DRAFT)->exists();
                if (!$check)
                    return $this->errorResponse(trans('apiResponse.parentNotFound'));
            }
            if ('in_company_docs' == Section::RULES[$request->get('section_id')]) $arr['company_user'] = null;
//            else {/* check company_user  */}
            $document->fill($arr);
            $document->save();

            $base = Doc::create($this->BaseDocumentBuilder($request->base_document, $document, $request));

            if ($request->has("sub_documents"))
                Doc::insert($this->SubDocumentsBuilder($document, $request->sub_documents, $base, $request));

            $table = Section::RULES[$document->section_id];

            $data = $this->saveBySection($request, $table);
            if ($data instanceof JsonResponse) return $data;

            DB::table($table)->insert($data +  ['document_id' => $document->id]);

            DB::commit();
            return $this->successResponse("OK");

        } catch (QueryException $e) {
            DB::rollBack();
            if ($e->errorInfo[1] == 1452) {
                if (preg_match("/\(\`[a-z\_]+\`\)/", $e->errorInfo[2], $find)) {
                    $info = substr($find[0], 2, -2);
                    return $this->errorResponse([$info => trans('apiResponse.notExists')], Response::HTTP_UNPROCESSABLE_ENTITY);
                }
            }
            if ($e->errorInfo[1] == 1062) {
                if (preg_match("/documents_(.*)_unique/", $e->getMessage(), $find)) {
                    return $this->errorResponse([$find[1] => trans('apiResponse.alreadyExists')], Response::HTTP_UNPROCESSABLE_ENTITY);
                }
            }
            if ($e->errorInfo[1] == 1292) {
                if (preg_match("/\`[a-z\_]+\`/", $e->errorInfo[2], $find)) {
                    return $this->errorResponse([$find[0] => trans('apiResponse.inCorrectFormat')], Response::HTTP_UNPROCESSABLE_ENTITY);
                }
                if (preg_match("/\'[a-z\_]+\'/", $e->errorInfo[2], $find)) {
                    return $this->errorResponse([$find[0] => trans('apiResponse.inCorrectFormat')], Response::HTTP_UNPROCESSABLE_ENTITY);
                }
            }

            return $this->errorResponse(trans('apiResponse.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);

        } catch (ValidationException $exception) {
            return $this->errorResponse($exception->errors());
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse(trans("apiResponse.tryLater"), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function addDocument(Request $request, $id)
    {
        $this->validate($request, [
            "document" => "required|array",
            "document.*" => "required",
            'company_id' => 'required|integer',
        ]);

        $company_id = $request->company_id;

        try {
            $document = Document::where("id", $id)
                ->where("company_id", $company_id)
                ->whereIn("status", [Document::WAIT, Document::ACTIVE])
                ->first('id');
            if (!$document)
                return $this->errorResponse(trans("apiResponse.docNotFound"), Response::HTTP_UNPROCESSABLE_ENTITY);
            $baseDoc = Doc::where("document_id", $id)->whereNull('parent_id')->first("id");
            if (!$baseDoc)
                return $this->errorResponse(trans("apiResponse.unProcess"), Response::HTTP_UNPROCESSABLE_ENTITY);

            Doc::insert($this->SubDocumentsBuilder($document, $request->document, $baseDoc, $request));

            return $this->successResponse("OK");

        } catch (\Exception $e) {
            return $this->errorResponse(trans('response.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);

        }
    }

    public function updateDocument(Request $request, $id)
    {
        $this->validate($request, [
            'company_id' => 'required|integer',
            "document" => "required",
            "doc_id" => "required|integer",
        ]);
        $company_id = $request->company_id;
        try {
            $document = Document::where("id", $id)
                ->where("company_id", $company_id)
                ->whereIn("status", [Document::WAIT, Document::ACTIVE])
                ->exists();
            if (!$document)
                return $this->errorResponse(trans("apiResponse.unProcess"));

            $doc = Doc::where("id", $request->doc_id)->where("document_id", $id)->first();
            if (!$doc)
                return $this->errorResponse(trans("apiResponse.unProcess"));


            $versions = json_decode($doc->versions, true);
            $addingVersions = [
                "resource" => $doc->resource,
                "type" => $doc->type,
                "uploader" => $doc->uploader,
                'size' => $doc->size
            ];
            $versions = $versions ?? [];
            array_push($versions, $addingVersions);


            $doc->versions = json_encode($versions);


            $sub_document = $this->saveDoc($request->document, $request, $str = 'documents');
            $doc->fill($sub_document);
            $doc->save();

            return $this->successResponse("OK");
        } catch (\Exception $e) {

            return $this->successResponse(trans("apiResponse.tryLater"), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    public function show(Request $request, $id)
    {
        $this->validate($request, [
            'company_id' => 'required|integer'
        ]);
        try {
            $document = Document::where([
                "id" => $id,
                "company_id" => $request->company_id
            ])
                ->where("status", "!=", Document::DRAFT)
                ->first(['section_id']);
            if (!$document)
                return $this->errorResponse(trans("apiResponse.unProcess"));

            $table = $document->section['table'];

            /**
             * , 'docs.subDocs'
             */
            $document = Document::with([
                'section', 'docs', 'sendType', 'sendType', 'sendForm',
                'parent', 'assignment', 'assignment.items',
                'assignment.uploader:id,name,surname',
                'assignment.items.employee.user:id,name,surname', 'assignment.items.notes', 'assignment.items.rejects'])->where(["documents.id" => $id])
                ->where("status", "!=", Document::DRAFT)
                ->join($table, "documents.id", '=', "$table.document_id");

            $data = [DB::raw('documents.*')];
            foreach (config("esd.tables.{$table}") as $column)
                array_push($data, "$table.$column");

            $document->WithAllRelations();

            $document = $document->first($data);
            return $this->successResponse($document);
        } catch (\Exception $e) {
            return $this->errorResponse(trans('response.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'theme' => 'sometimes|required|min:2|max:255',
            'document_no' => 'sometimes|required|max:255',
            'register_number' => 'sometimes|required|min:2|max:255',
            'section_id' => 'sometimes|required|integer',
            'description' => 'sometimes|required',
            'document_time' => 'sometimes|required|date|date_format:Y-m-d',
            'inner_inspect' => 'sometimes|required|integer|in:0,1',
//            'end_time' => 'sometimes|required|date|date_format:Y-m-d',
            'register_time' => 'sometimes|required|date|date_format:Y-m-d',

            'company_id' => 'required|integer',
            'parent_id' => 'sometimes|required|integer',
            'page_count' => 'sometimes|required|integer',
            'copy_count' => 'sometimes|required|integer',
            'company_user' => 'sometimes|integer',
            'send_type' => 'sometimes|required|integer',
            'send_form' => 'sometimes|required|integer',
            'has_permission' => 'sometimes|required|integer|in:0,1',
            'send_to_user' => 'sometimes|required|integer|in:0,1',
            'expire_time' => 'sometimes|required|date|date_format:Y-m-d',
        ]);
        $company_id = $request->company_id;
        $arr = $request->only('send_to_user',
            'company_user',
            'inner_inspect',
            'register_time',
            'document_time',
            'register_number', 'document_no', 'page_count', 'chapter_count', 'send_type', 'send_form', 'parent_id', "theme", "description", "body", "section_id", 'expire_time');
        try {
            DB::beginTransaction();


            //todo check permission of update hr user
            $document = Document::where("id", $id)
                ->where("company_id", $company_id)
                ->where("status", "=", Document::WAIT);

            $document = $document->first(['id', 'section_id']);
            if (!$document)
                return $this->errorResponse(trans("apiResponse.documentNotInValidStatus"));


            if ('in_company_docs' == Section::RULES[$document->section_id]) $arr['company_user'] = null;

            $document->fill($arr);

            $document->save();


            $table = Section::RULES[$document->section_id];

            $data = $this->saveBySection($request, $table);

            if ($data instanceof JsonResponse) return $data;

            if ($data)
                DB::table($table)->where('document_id', $document->id)->update((array)$data);

            DB::commit();

            return $this->successResponse("OK");

        } catch (QueryException $e) {
            DB::rollBack();
            if ($e->errorInfo[1] == 1452) {
                if (preg_match("/\(\`[a-z\_]+\`\)/", $e->errorInfo[2], $find)) {
                    $info = substr($find[0], 2, -2);
                    return $this->errorResponse([$info => "does not exist"], Response::HTTP_UNPROCESSABLE_ENTITY);
                }
            }

            if ($e->errorInfo[1] == 1062) {
                if (preg_match("/documents_(.*)_unique/", $e->getMessage(), $find)) {
                    return $this->errorResponse([$find[1] => trans('apiResponse.alreadyExists')], Response::HTTP_UNPROCESSABLE_ENTITY);
                }
            }

            if ($e->errorInfo[1] == 1292) {
                if (preg_match_all("/\`[a-z\_]+\`/", $e->errorInfo[2], $find)) {
                    $info = $find[0];
                    return $this->errorResponse([$info[count($info) - 1] => "incorrect format"], Response::HTTP_UNPROCESSABLE_ENTITY);
                }
                if (preg_match_all("/\'[a-z\_]+\'/", $e->errorInfo[2], $find)) {
                    return $this->errorResponse([$find[0] => "incorrect format"], Response::HTTP_UNPROCESSABLE_ENTITY);
                }
            }

            return $this->errorResponse(trans('apiResponse.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (ValidationException $exception) {
            return $this->errorResponse($exception->errors());
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse(trans("apiResponse.tryLater"), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(Request $request, $id)
    {
        $this->validate($request, [
            'company_id' => 'required|integer'
        ]);
        try {
            $check = Document::where("id", $id)
                ->where("company_id", $request->company_id)
                ->where("status", "=", Document::WAIT)
                ->delete();
            if ($check)
                return $this->errorResponse(trans("apiResponse.unProcess"));
            return $this->successResponse("OK");
        } catch (\Exception $e) {
            return $this->errorResponse(trans("apiResponse.tryLater"), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function makeActive(Request $request, $id)
    {
        $this->validate($request, [
            'company_id' => 'required|integer'
        ]);
        try {
            $check = Document::where("id", $id)
                ->where("status", Document::WAIT)
                ->where("company_id", $request->company_id)
                ->update(
                    ["status" => Document::ACTIVE]);
            if (!$check)
                return $this->errorResponse(trans("apiResponse.unProcess"));
            return $this->successResponse("OK");

        } catch (\Exception $e) {
            return $this->errorResponse(trans("apiResponse.tryLater"), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getDocumentsNo(Request $request)
    {
        $this->validate($request, [
            'company_id' => 'required|integer',
            'document_no' => 'sometimes|required',
            'theme' => 'sometimes|required',
        ]);
        $documents = Document::where("company_id", $request->company_id)
            ->where("status", "!=", Document::DRAFT);
        if ($request->has('document_no'))
            $documents->where('document_no', 'like', $request->document_no . '%');
        if ($request->has('theme'))
            $documents->where('theme', 'like', $request->theme . '%');
        $documents = $documents
            ->whereNotNull('document_no')
            ->orderBy('id', 'desc')
            ->take(50)
            ->get(['id', 'document_no', 'theme']);

        return $this->successResponse($documents);
    }

    public function getDocumentsRegNo(Request $request)
    {
        $this->validate($request, [
            'company_id' => 'required|integer',
            'register_number' => 'sometimes|required',
            'theme' => 'sometimes|required',
        ]);

        $documents = Document::where("company_id", $request->company_id)
            ->where("status", "!=", Document::DRAFT);

        if ($request->has('register_number'))
            $documents->where('register_number', 'like', $request->register_number . "%");

        if ($request->has('theme'))
            $documents->where('theme', 'like', $request->theme . '%');
        $documents = $documents
            ->orderBy('id', 'desc')
            ->take(50)
            ->get(['id', 'register_number', 'theme' , 'theme']);

        return $this->successResponse($documents);
    }

    public function changeStatus(Request $request, $id)
    {
        $this->validate($request, [
            'status' => 'required|integer|in:0,1,3,4',
            'company_id' => 'required|integer',

            'has_permission' => 'sometimes|required|in:0,1'
        ]);
        try {
            $document = Document::where('id', $id)
                ->where('company_id', $request->company_id)->first(['status']);

            $update = ['status' => $request->status];
            if (!$document) return $this->errorResponse(trans('apiResponse.unProcess'));

            if ($document->status == Document::WAIT_FOR_ACCEPTANCE and $request->status == Document::ACTIVE) {
                AssignmentItem::whereHas('assignment', function ($q) use ($id) {
                    $q->where('document_id', $id);
                })->update([
                    'status' => AssignmentItem::WAIT
                ]);
            }
            if ($request->status == Document::ARCHIVE and $request->has('folder')) {
                $update['folder'] = $request->folder;
            }
            Document::where('id', $id)
                ->update($update);

            return $this->successResponse('OK');
        } catch (\Exception $e) {
            return $this->errorResponse(trans('apiResponse.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}



