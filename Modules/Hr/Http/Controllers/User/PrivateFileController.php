<?php

namespace Modules\Hr\Http\Controllers\User;

use App\Rules\IsValidEmployeeRule;
use App\Traits\ApiResponse;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Routing\Controller;
use Illuminate\Validation\ValidationException;
use Modules\Hr\Entities\PrivateFile;

class PrivateFileController extends Controller
{
    use ApiResponse, ValidatesRequests;

    private $files;

    public function __construct(PrivateFile $file)
    {
        $this->files = $file;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function index(Request $request): JsonResponse {
        $this->validate($request, [
            'per_page' => 'nullable|integer'
        ]);

        $files = $this->files
        ->with([
            'user:id,name,surname',
        ])
        ->company()
        ->orderBy('id', 'desc')
        ->paginate($request->get('per_page'));

        return $this->successResponse($files);
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse {
        $file = $this->files
            ->with([
                'user:id,name,surname',
            ])
            ->company()
            ->firstOrFail();
        return $this->successResponse($file);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function create(Request $request): JsonResponse {
        $this->validate($request, $this->getFileRules());
        $this->saveUserFile($request, $this->files);
        return $this->successResponse(trans('messages.saved'), 201);
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     * @throws ValidationException
     */
    public function update(Request $request, $id): JsonResponse {
        $this->validate($request, $this->getFileRules('update'));
        $file = $this->files->where('id', $id)->company()->firstOrFail(['id']);
        $this->saveUserFile($request, $file);
        return $this->successResponse(trans('messages.saved'), 200);
    }

    /**
     * @param Request $request
     * @param PrivateFile $file
     */
    private function saveUserFile(Request $request, PrivateFile $file): void {
        $fillable = [
            'user_id' => $request->get('user_id'),
            'name' => $request->get('name'),
            'note' => $request->get('note')
        ];
        if ($request->file('file')){
            $fillable['file'] = $this->uploadAndGetName($request->file('file'), $request->get('user_id'), 'user/files');
            $size = $request->file('file')->getSize();
            $file['size'] = number_format($size / 1048576,4) . 'MB';
        }
        $file->fill($fillable)->save();
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse {
        $file = $this->files->where('id', $id)->company()->firstOrFail(['id']);
        return $file->delete()
            ? $this->successResponse(trans('messages.saved'))
            : $this->errorResponse(trans('messages.not_saved'), 400);
    }

    private function uploadAndGetName($file, $userId, $folder = '', $pre = ''): array {

        $uploadedFile = $file;
        $filename = '';
        if ($pre)
            $filename .= $pre . '-';
        $filename = $filename .time() . $uploadedFile->getClientOriginalName();
        $disk = 'local';
        $path = $folder . '/' . $userId . '/' . $filename;
        Storage::disk($disk)->putFileAs(
            $path,
            $uploadedFile,
            $filename
        );

        return [
            'disk' => $disk,
            'path' => $path,
            'filename' => $filename
        ];
    }


    /**
     * @param string $type
     * @return array
     */
    private function getFileRules($type = 'create'): array {
        $extensions = implode(',', $this->files::allowedExtensions());
        return [
            'user_id' => [
                'required',
                new IsValidEmployeeRule(\request()->get('company_id'))
            ],
            'name' => 'required',
            'file' => $type === 'create'
                ? 'required|file|mimes:'.$extensions.'|max:5120'
                : 'nullable|file|mimes:'.$extensions.'|max:5120',
            'note' => 'nullable|max:255'
        ];
    }
}
