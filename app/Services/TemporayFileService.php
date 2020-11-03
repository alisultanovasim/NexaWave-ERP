<?php


namespace App\Services;


use App\Models\TemporaryFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TemporayFileService
{
    public function uploadFile(Request $request, int $companyId = null): TemporaryFile
    {
        $ext = $request->file('file')->getClientOriginalExtension();
        $size = $request->file('file')->getSize();
        $name = createNewPhotoName($ext);
        $path = 'public/temp_files';
        $request->file('file')->storeAs($path, $name);

        $file = new TemporaryFile();
        $file->fill([
            'file' => $path . '/' . $name,
            'disk_name' => 'local',
            'name' => $name,
            'extension' => $ext,
            'size' => $size,
            'size_type' => 'KB'
        ]);

        return $file;
    }

    public function uploadMultipleFiles(Request $request, int $companyId = null): TemporaryFile
    {

    }

    public function moveFileToCompanyFiles(TemporaryFile $file, int $companyId): array
    {
        $fileStorage = Storage::disk($file->getAttribute('disk_name'))->get(($file->getAttribute('file')));
        Storage::disk('company_storage')->put($companyId . '/' . $file->getAttribute('name'), $fileStorage);

        return [];
    }

}
