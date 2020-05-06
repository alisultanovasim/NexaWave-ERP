<?php


namespace Modules\Esd\Http\Controllers;

use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Auth;
use Modules\Esd\Entities\Citizen;
use Modules\Esd\Entities\InCompany;
use Modules\Esd\Entities\Section;
use Modules\Esd\Entities\senderCompany;
use Modules\Esd\Entities\Structure;
use App\Traits\ApiResponse;
use App\Traits\DocumentBySection;
use App\Traits\DocumentUploader;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Modules\Esd\Entities\Doc;
use Modules\Esd\Entities\Document;
use Illuminate\Routing\Controller;

class DraftController extends Controller
{
    use  ApiResponse , DocumentBySection , DocumentUploader  ,ValidatesRequests;

    protected $errors  = [];

    /**
     * drafts crud
     */
    public function index(Request $request)
    {
        $this->validate($request, [
            'company_id' => 'required|integer',
            "per_page" => "sometimes|required|integer",
            "section_id" => "sometimes|required|integer",
            'time_from' => 'sometimes|required|date|date_format:Y-m-d',
            'time_to' => 'sometimes|required|date|date_format:Y-m-d',
            'document_no' => 'sometimes|required',
            'register_number' => 'sometimes|required',
        ]);

        $draft = Document::with(['section:id,name'])
            ->where("company_id", $request->company_id)
            ->where("status", config("esd.document.status.draft"))
            ->where("from",  Auth::id());

        if ($request->has("theme"))
            $draft->where("theme", 'like', $request->theme . "%");

        if ($request->has("time_from"))
            $draft->where("created_at", ">", $request->time_from);

        if ($request->has("time_to"))
            $draft->where("created_at", "<", $request->time_to);

        if ($request->has("document_no"))
            $draft->where("document_no", 'like', $request->document_no . "%");

        if ($request->has("register_number"))
            $draft->where("register_number", 'like', $request->register_number . "%");

        if ($request->has("register_time"))
            $draft->where("register_time", $request->register_time);

        if ($request->has('section_id')){
            $draft->where("section_id", $request->section_id);
        }

        $draft = $draft->orderBy('id' ,'DESC')->paginate($request->per_page ?? 10);

        return $this->successResponse($draft);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'theme' => 'sometimes|required|min:2|max:255',
            'base_document' => 'sometimes|required',
            'document_no' => 'sometimes|required|max:255',
            'register_number' => 'sometimes|required|min:2|max:255',
            'sub_documents' => 'sometimes|required|array',
            'sub_documents.*' => 'sometimes|required',
            'section_id' => 'required|integer',
            'description' => 'sometimes|sometimes|required',
            'register_time' => 'sometimes|sometimes|required|date|date_format:Y-m-d',

            'company_id' => 'required|integer',
            'parent_id' => 'sometimes|required|integer',
            'page_count' => 'sometimes|required|integer',
            'copy_count' => 'sometimes|required|integer',
            'send_type' => 'sometimes|required|integer',
            'send_form' => 'sometimes|required|integer',
            'inner_inspect' => 'sometimes|required|integer|in:0,1',
            'expiry_time' => 'sometimes|required|date|date_format:Y-m-d',
            'document_time' => 'sometimes|required|date|date_format:Y-m-d',
            'send_to_user' => 'sometimes|required|integer|in:0,1',
            'company_user' => 'sometimes|required|integer'
        ]);

        $arr = $request->only(
            'inner_inspect',
            'register_number',
            'document_no',
            'page_count', 'copy_count',
            'send_type', 'send_form',
            'parent_id', 'theme',
            'description', 'section_id', 'document_time', 'register_time', 'send_to_user', 'company_user');

        $company_id = $request->company_id;
        try {
            DB::beginTransaction();

            $document = new Document;

            if ($request->has('parent_id')) {
                $check = Document::where('id', $request->parent_id)->where('company_id', $company_id)->where('status', '!=', config("esd.document.status.draft"))->exists();
                if (!$check)
                    return $this->errorResponse(trans('apiResponse.parentNotFound'));
            }

            $document->fill(array_merge($arr, [
                "status" => config("esd.document.status.draft"),
                "from" => Auth::id(),
                "company_id" => $company_id,
            ]));

            $document->save();

            if ($request->has('base_document')) {
                $base_document = $this->BaseDocumentBuilder( $request->base_document, $document , $request , 'document');
                $base = Doc::create($base_document);
                if ($request->has("sub_documents")) {
                    $sub_documents = $this->SubDocumentsBuilder( $document ,$request->sub_documents , $base , $request);
                    Doc::insert($sub_documents);
                }
            }


            $table = Section::RULES[$document->section_id];
//            $table = Section::where('id', $request->section_id)->first('table')->table;

            $data = $this->dataBySection($request , $document, $table);

            if($data instanceof JsonResponse) return $data;

            $data = $data === false ?  [] : $data;
            DB::table($table)->insert($data + ['document_id'=>$document->id]);
            DB::commit();
            return $this->successResponse("OK");
        }catch (QueryException $e) {
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
                if (preg_match("/\`[a-z\_]+\`/", $e->errorInfo[2], $find)) {
                    return $this->errorResponse([$find[0] => "incorrect format"], Response::HTTP_UNPROCESSABLE_ENTITY);
                }
                if (preg_match("/\'[a-z\_]+\'/", $e->errorInfo[2], $find)) {
                    return $this->errorResponse([$find[0] => "incorrect format"], Response::HTTP_UNPROCESSABLE_ENTITY);
                }
            }

             return $this->errorResponse(trans('apiResponse.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->errorResponse(trans("apiResponse.tryLater"), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
//    public function uploadFile(UploadedFile $file, $company_id): string
//    {
//        $filename = $company_id . '/' . rand(1, 10000) . time() . "." . $file->extension();
//        $file->move(base_path("public/documents/" . $company_id), $filename);
//        return $filename;
//    }
    public function show(Request $request, $id)
    {
        $this->validate($request, [

            'company_id' => 'required|integer',
        ]);
        try {
            $draft = Document::where("id", $id)
                ->where("status", config("esd.document.status.draft"))
                ->where("company_id", $request->company_id)
                ->where('from' , Auth::id())
                ->first(['section_id']);
            if (!$draft)
                return $this->errorResponse(trans("apiResponse.unProcess"));

            $table = $draft->section['table'];

            $draft = Document::with(['section' , 'docs' ,'docs.subDocs' , 'sendType' , 'sendType','sendForm' , 'parent'])->where(["documents.id" => $id])
                ->join($table, "documents.id", '=', "$table.document_id");

            $data = [DB::raw('documents.*')];

            foreach (config("esd.tables.{$table}") as $column )
                array_push($data, "$table.$column");

            $draft->with(config("esd.table_relations.{$table}"));

            $draft = $draft->first($data);

            return $this->successResponse($draft);
        } catch (\Exception $e) {
            return $this->errorResponse(trans("apiResponse.tryLater"), Response::HTTP_INTERNAL_SERVER_ERROR);

        }
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'theme' => 'sometimes |required|min:2|max:255',
            'base_document' => 'sometimes|required',
            'document_no' => 'sometimes|required|max:255',
            'register_number' => 'sometimes|required|min:2|max:255',
            'sub_documents' => 'sometimes|required|array',
            'sub_documents.*' => 'sometimes|required',
            'description' => 'sometimes|sometimes|required',
            'register_time' => 'sometimes|sometimes|required|date|date_format:Y-m-d',

            'company_id' => 'required|integer',
            'parent_id' => 'sometimes|required|integer',
            'page_count' => 'sometimes|required|integer',
            'copy_count' => 'sometimes|required|integer',
            'send_type' => 'sometimes|required|integer',
            'send_form' => 'sometimes|required|integer',
            'inner_inspect' => 'sometimes|required|integer|in:0,1',
            'expiry_time' => 'sometimes|required|date|date_format:Y-m-d',
            'document_time' => 'sometimes|required|date|date_format:Y-m-d',
            'send_to_user' => 'sometimes|required|integer|in:0,1',
            'company_user' => 'sometimes|required|integer'

        ]);

        $company_id = $request->company_id;
        $arr = $request->only(
            'inner_inspect',
            'expiry_time',
            'register_number',
            'document_no',
            'page_count', 'copy_count',
            'send_type', 'send_form',
            'parent_id', 'theme',
            'description', 'document_time', 'register_time', 'send_to_user');
        try {

            $document = Document::where("id", $id)
                ->where("company_id", $company_id)
                ->where("from", Auth::id())
                ->first(['id' , 'status', 'section_id']);
            if (!$document)
                return $this->errorResponse(trans("apiResponse.unProcess"));
            if ($document->status != config("esd.document.status.draft"))
                return $this->errorResponse(trans("apiResponse.docStatusError", ["status" => $document->status]));
            if ($request->has('parent_id')) {
                $check = Document::where('id', $request->parent_id)->where('company_id', $company_id)->exists();
                if (!$check)
                    return $this->errorResponse(trans('apiResponse.partNotFound'));
            }
            Document::where("id", $id)->update($arr);


            //$table = Section::where('id', $document->section_id)->first('table')->table;
            $table = Section::RULES[$document->section_id];
            $data = $this->dataBySection($request , $document, $table);

            if($data instanceof JsonResponse) return $data;
            if ($data)
                DB::table($table)->where('document_id' , $document->id)->update((array)$data);

            return $this->successResponse("OK");

        } catch (\Exception $e) {
            dd($e);
            return $this->errorResponse(trans("apiResponse.tryLater"), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(Request $request, $id)
    {
        $this->validate($request, [

            'company_id' => 'required|integer',
        ]);
        try {
            $check = Document::where([
                "id" => $id,
                "status" => config("esd.document.status.draft"),
                "company_id" => $request->company_id,
                "from" => Auth::id()
            ])->delete();
            if (!$check)
                return $this->errorResponse(trans("apiResponse.unProcess"));
            return $this->successResponse("OK");
        } catch (\Exception $e) {
            return $this->errorResponse(trans("apiResponse.tryLater"), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    /**
     * crud end
     */

    public function addDocument(Request $request, $id)
    {
        $this->validate($request, [
            "documents" => "required|array",
            "documents.*" => "required",
            'company_id' => 'required|integer',
        ]);
        $company_id = $request->company_id;
        try {
            $document = Document::where("id", $id)
                ->where("company_id", $company_id)
                ->where("from",  Auth::id())
                ->first(['id', 'status']);
            if (!$document)
                return $this->errorResponse(trans("apiResponse.unProcess"));
            if ($document->status != Document::DRAFT)
                return $this->errorResponse(trans("apiResponse.docStatusError", ["status" => $document->status]));

            $doc = Doc::where("document_id", $document->id)->whereNull('parent_id')->first('id');
            if (!$doc)
                return $this->errorResponse(trans("apiResponse.unProcess"), Response::HTTP_UNPROCESSABLE_ENTITY);

            $sub_documents = $this->SubDocumentsBuilder($document , $request->documents , $doc , $request);

            Doc::insert($sub_documents);

            return $this->successResponse("OK");


        } catch (\Exception $e) {
            return $this->errorResponse(trans("apiResponse.tryLater"), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function removeDocument(Request $request, $id)
    {
        $this->validate($request, [
            'doc_id' => 'required|integer',

            'company_id' => 'required|integer',
        ]);

        $company_id = $request->company_id;

        try {
            $document = Document::where("id", $id)
                ->where("company_id", $company_id)
                ->where("from", Auth::id())
                ->first('id', 'status');
            if (!$document)
                return $this->errorResponse(trans("apiResponse.unProcess"));
            if ($document->status != config("esd.document.draft"))
                return $this->errorResponse(trans("apiResponse.docStatusError", ["status" => $document->status]));


            $doc = Doc::where('id', $request->doc_id)->where('document_id', $id)->first('parent_id');
            if (!$doc)
                return $this->errorResponse(trans("apiResponse.docNotFound"));
            if ($doc->parent_id == null)
                return $this->errorResponse(trans("apiResponse.baseDelete"));

            $check = Doc::where('id', $request->doc_id)->delete();

            if (!$check)
                return $this->errorResponse(trans("apiResponse.unProcess"));

            return $this->successResponse("OK");

        } catch (\Exception $e) {
            return $this->errorResponse(trans("apiResponse.tryLater"), Response::HTTP_INTERNAL_SERVER_ERROR);

        }
    }

    public function updateDocument(Request $request, $id)
    {
        $this->validate($request, [
            'document' => 'required',
            'doc_id' => 'required|integer',

            'company_id' => 'required|integer',
        ]);
        $company_id = $request->company_id;
        try {
            $document = Document::where("id", $id)
                ->where("company_id", $company_id)
                ->where("from",  Auth::id())
                ->first('id', 'status');
            if (!$document)
                return $this->errorResponse(trans("apiResponse.unProcess"));
            if ($document->status != config("esd.document.draft"))
                return $this->errorResponse(trans("apiResponse.docStatusError" . ["status" => $document->status]), [
                    "current_status" => $document->status,
                    "status" => config("esd.document.status")
                ]);

            $check = Doc::where('id', $request->doc_id)->where('document_id', $id)->exists();
            if (!$check)
                return $this->errorResponse(trans("apiResponse.docNotFound"));

            $updData = $this->saveDoc($request->document , $request, 'documents');

            $check = Doc::where('id', $request->doc_id)->update($updData);
            if (!$check)
                return $this->errorResponse(trans("apiResponse.unProcess"));
            return $this->successResponse('OK');
        } catch (\Exception $ex) {
            return $this->errorResponse(trans("apiResponse.tryLater"), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function makeRealDocument(Request $request, $id)
    {
        $this->validate($request, [

            'company_id' => 'required|integer',
        ]);
        $company_id = $request->company_id;
        try {
            $document = Document::where([
                "status" => config("esd.document.status.draft"),
                "company_id" => $company_id,
                "from" =>  Auth::id(),
                "id" => $id
            ])->first();
            if (!$document)
                return $this->errorResponse(trans("apiResponse.unProcess"));


            $check = $this->checkDocumentAbilityToBeingRealDocument($document);
            if(!$check)
                return $this->errorResponse(['message' => trans("apiResponse.incompleteData"), 'errors'=>$this->errors]);


            $check = Doc::where('document_id', $id)->whereNull('parent_id')->exists();


            if (!$check)
                return $this->errorResponse(trans('apiResponse.baseHas'));

            $check = Document::where(["id" => $id])->update([
                "status" => config("esd.document.status.wait")
            ]);



            if (!$check)
                return $this->errorResponse(trans('apiResponse.unProcess'));

            return $this->successResponse("OK");
        } catch (\Exception $e) {
            return $this->errorResponse(trans("apiResponse.tryLater"), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function checkDocumentAbilityToBeingRealDocument(Document $document){
        if (is_null($document->theme))
            $this->errors['theme'] = trans('apiResponse.themeRequired');

        if (is_null($document->register_number))
            $this->errors['register_number'] = trans('apiResponse.registerNumberRequired');

        $table = Section::RULES[$document->section_id];
//        $table = Section::where('id', $document->section_id)->first('table')->table;

        switch ($table) {
            case "structure_docs":
                $document->type = Structure::where('document_id' , $document->id)->first('sender_company_id');
                if (is_null($document->type->sender_company_id))
                    $this->errors['sender_company_id'] = trans('apiResponse.senderCompanyIdRequired');
                break;
            case "citizen_docs":
                $document->type = Citizen::where('document_id' , $document->id)->first('name');
                if (is_null($document->type->name))
                    $this->errors['name'] = trans('apiResponse.nameRequired');
                break;
            case "in_company_docs":
                $document->type = InCompany::where('document_id' , $document->id)->first('to_in_our_company');
                if (is_null($document->type->to_in_our_company))
                    $this->errors['to_in_our_company'] = trans('apiResponse.toInOurCompanyRequired');
                break;
        }

        $flag = $this->errors ? false : true;

        return $flag;
    }


}
