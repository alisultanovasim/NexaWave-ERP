<?php

namespace App\Http\Controllers;

use App\Services\TemporayFileService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FileUploadController extends Controller
{
    use ApiResponse;

    private $fileService;

    public function __construct(TemporayFileService $fileService)
    {
        $this->fileService = $fileService;
    }

    public function uploadFile(Request $request): JsonResponse
    {
        $this->validate($request, [
            'file' => 'required'
        ]);
        dd('a');

        return $this->successResponse([
            'id' => $this->fileService->uploadFile($request)->getKey()
        ]);
    }

    public function uploadMultipleFiles(Request $request): JsonResponse
    {

    }
}
