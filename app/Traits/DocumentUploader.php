<?php


namespace App\Traits;


use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

trait DocumentUploader
{
    public function saveDocs($notes, $request, $str)
    {
        $docs = [];
        foreach ($notes as $uploadedFile) {
            $docs[] = $this->saveDoc($uploadedFile, $request, $str);
        }
        return $docs;
    }

    private function saveDoc($document, $request, $str)
    {
        if ($document instanceof UploadedFile) {
            if (in_array($request->document->extension(), config("esd.document.extensions"))) {
                $filesize = filesize($document->getRealPath());
                $sub_documents = [
                    "uploader" => Auth::id(),
                    "resource" => $this->uploadFile($document, $request->company_id, $str),
                    "type" => config("esd.document.type.file"),
                    'size' => $filesize

                ];
            } else
                throw ValidationException::withMessages([
                    "$str" => ['Not valid extention'],
                ]);
        } else {
            $sub_documents = [
                "uploader" => Auth::id(),
                "resource" => $document,
                "type" => config("esd.document.type.editor"),
                'size' => strlen($document)
            ];
        }
        return $sub_documents;
    }

    private function saveNotes($assignmentItem , $notes , $request , $str = 'notes'){
        $all = [];
        foreach ($notes as $note){
            $all[] = $this->saveNote($assignmentItem , $note , $request , $str);
        }
        return $all;
    }

    private function saveNote($assignmentItem, $note , $request , $str){
        if ($note instanceof UploadedFile) {
            if (in_array($note->extension(), config("esd.document.extensions"))) {
                $filesize = filesize($note->getRealPath());
                $item = [
                    "resource" => $this->uploadFile($note, $request->company_id, $str),
                    "type" => config("esd.document.type.file"),
                    'size' => $filesize,
                    'assignment_item_id'=>$assignmentItem->id
                ];
            } else
                throw ValidationException::withMessages([
                    "$str" => ['Not valid extention'],
                ]);
        } else {
            $item = [
                "resource" => $note,
                "type" => config("esd.document.type.editor"),
                'size' => strlen($note),
                'assignment_item_id'=>$assignmentItem->id
            ];
        }
        return $item;
    }
    private function BaseDocumentBuilder($baseDocument, $document, $request, $str = 'documents')
    {
        if ($baseDocument instanceof UploadedFile) {
            if (in_array($baseDocument->extension(), config("esd.document.extensions"))) {
                $filesize = filesize($baseDocument->getPathName());
                $base_document = [
                    "uploader" => Auth::id(),
                    "resource" => $this->uploadFile($baseDocument, $request->company_id, $str),
                    "parent_id" => null,
                    "type" => config("esd.document.type.file"),
                    "document_id" => $document->id,
                    'size' => $filesize

                ];
            } else
                throw ValidationException::withMessages([
                    "$str" => ['Not valid extention'],
                ]);
        } else {
            $base_document = [
                "uploader" => Auth::id(),
                "resource" => $baseDocument,
                "parent_id" => null,
                "type" => config("esd.document.type.editor"),
                "document_id" => $document->id,
                'size' => strlen($baseDocument)
            ];
        }
        return $base_document;
    }

    public function uploadFile(UploadedFile $file, $company_id, $str = "documents"): string
    {
        return $file->store("documents/$company_id/$str");
//        $file->move(base_path("storage/app/documents/$company_id/$str"), $filename);
    }

    private function SubDocumentsBuilder($document, $documents, $baseDoc, $request, $str = 'documents')
    {
        foreach ($documents as $sub_document) {
            if ($sub_document instanceof UploadedFile) {
                if (in_array($sub_document->extension(), config("esd.document.extensions"))) {
                    $filesize = filesize($sub_document->getPathName());
                    $sub_documents[] = [
                        "uploader" => Auth::id(),
                        "resource" => $this->uploadFile($sub_document, $request->company_id, $str),
                        "parent_id" => $baseDoc->id,
                        "type" => config("esd.document.type.file"),
                        "document_id" => $document->id,
                        'size' => $filesize
                    ];
                } else
                    throw ValidationException::withMessages([
                        "$str" => ['Not valid extention'],
                    ]);
            } else {
                $sub_documents[] = [
                    "uploader" => Auth::id(),
                    "resource" => $sub_document,
                    "parent_id" => $baseDoc->id,
                    "type" => config("esd.document.type.editor"),
                    "document_id" => $document->id,
                    'size' => strlen($sub_document)
                ];
            }
        }
        return $sub_documents;
    }
}
