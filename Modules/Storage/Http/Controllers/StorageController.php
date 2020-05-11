<?php

namespace Modules\Storage\Http\Controllers;

use App\Traits\ApiResponse;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;
use Modules\Storage\Entities\Storage;

class StorageController extends Controller
{
    use  ApiResponse, ValidatesRequests;

    public function index(Request $request)
    {
        $this->validate($request, [
            'company_id' => ['required', 'integer'],
            'per_page' =>  ['nullable' , 'integer' , 'min:1']
        ]);

        $storage = Storage::where('company_id', $request->get('company_id'))
            ->orderBy('id', 'DESC')
            ->paginate($request->get('per_page'));


        return $this->successResponse($storage);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => ['required', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'company_id' => ['required', 'integer']
        ]);

        Storage::create($request->only('name', 'location', 'company_id'));

        return $this->successResponse('ok');
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'location' => ['nullable', 'string', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
            'company_id' => ['required', 'integer']
        ]);

        $data = $request->only('location', 'name');
        if (!$data) return $this->errorResponse('response.nothing');

        Storage::where('id', $id)
            ->where('company_id', $request->get('company_id'))
            ->update($data);

        return $this->successResponse('ok');
    }

    public function show(Request $request, $id)
    {
        $storage = Storage::where('id', $id)
            ->where('company_id', $request->get('company_id'))
            ->first();

        if (!$storage) return $this->errorResponse('response.notFound', 404);

        return $this->successResponse($storage);
    }

    public function delete(Request $request, $id)
    {
        try {
            Storage::where('id', $id)
                ->where('company_id', $request->get('company_id'))
                ->delete();

            return $this->successResponse('ok');
        } catch (QueryException $e) {
            dd($e->errorInfo);
        }
    }
}
