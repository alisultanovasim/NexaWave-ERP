<?php

namespace App\Http\Controllers;

use App\Services\File\TemporaryFileService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FileUploadController extends Controller
{
    use ApiResponse;

    private $fileService;

    public function __construct(TemporaryFileService $fileService)
    {
        $this->fileService = $fileService;
    }

    public function companyUploadFile(Request $request): JsonResponse
    {
        $this->validate($request, [
            'file' => 'required|file'
        ]);

        $tempFile = $this->fileService->companyUploadFile($request, $request->get('company_id'));

        return $this->successResponse([
            'file' => $tempFile->toArray()
        ]);
    }

    public function companyUploadMultipleFiles(Request $request): JsonResponse
    {
        $this->validate($request, [
            'files' => 'required|array|min:2',
            'files.*' => 'file'
        ]);

        $fileCollection = $this->fileService->companyUploadMultipleFiles($request, $request->get('company_id'));

        return $this->successResponse([
            'files' => $fileCollection
        ]);
    }
}
