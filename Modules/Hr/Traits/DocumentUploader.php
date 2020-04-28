<?php


namespace Modules\Hr\Traits;

use Illuminate\Http\UploadedFile;

trait DocumentUploader
{

    protected static function save($file , $company , $subFolder)
    {
        if ($file instanceof  UploadedFile){
            $filename = rand(1, 10000) . time() . "." . $file->extension();
            $file->move(base_path("public/documents/$company/$subFolder"), $filename);
            return "$company/$subFolder/$filename";
        }
    }

}
