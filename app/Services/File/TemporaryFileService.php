<?php


namespace App\Services\File;


use App\Models\TemporaryFile;
use App\Services\File\Exceptions\CompanyStorageSpaceException;
use Carbon\Carbon;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TemporaryFileService
{
    public function companyUploadFile(Request $request, int $companyId): TemporaryFile
    {
        //check if company has storage
        //case
//        if (false) {
//            throw new CompanyStorageSpaceException(trans('messages.not_enough_space'));
//        }
        return $this->uploadFile($request->file('file'));
    }

    public function companyUploadMultipleFiles(Request $request, int $companyId): Collection
    {
        //check if company has storage
        $totalSize = 0;
        foreach ($request->file('files') as $file) {
            $totalSize += $file->getSize();
        }
        //case
//        if (false) {
//            throw new CompanyStorageSpaceException(trans('messages.not_enough_space'));
//        }
        return $this->uploadMultipleFiles($request);
    }

    /**
     * @param UploadedFile $uploadFile
     * @return TemporaryFile
     */
    private function uploadFile(UploadedFile $uploadFile): TemporaryFile
    {
        $ext = $uploadFile->getClientOriginalExtension();
        $size = $uploadFile->getSize();
        $name = strtolower(Str::random(10) . '_' . time() . '.' . $ext);
        $path = 'temp/files';
        $uploadFile->storeAs($path, $name);

        $file = new TemporaryFile();
        $file->file=$path . '/' . $name;
        $file->disk_name='local';
        $file->name=$name;
        $file->extension=$ext;
        $file->size=$size;
        $file->size_type='KB';
        $file->save();
//        $file->fill([
//            'file' => $path . '/' . $name,
//            'disk_name' => 'local',
//            'name' => $name,
//            'extension' => $ext,
//            'size' => $size,
//            'size_type' => 'KB'
//        ])->save();

        return $file;
    }

    /**
     * @param Request $request
     * @return Collection
     */
    public function uploadMultipleFiles(Request $request): Collection
    {
        $collection = new Collection();
        foreach ($request->file('files') as $file) {
            $collection->push($this->uploadFile($file)->toArray());
        }

        return $collection;
    }

    /**
     * @param TemporaryFile $file
     * @param int $companyId
     * @return string
     * @throws FileNotFoundException
     */
    public function moveFileToCompanyStorage(TemporaryFile $file, int $companyId): string
    {
        $path = Storage::disk('company_storage')->path($companyId . '/' . $file->getAttribute('name'));
        $fileStorage = Storage::disk($file->getAttribute('disk_name'))->get(($file->getAttribute('file')));
        Storage::disk('company_storage')->put($companyId . '/' . $file->getAttribute('name'), $fileStorage);

        return $path;
    }

}
