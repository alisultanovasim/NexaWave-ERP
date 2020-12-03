<?php

namespace Modules\Hr\Http\Controllers;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Hr\Entities\ContractType;

class ContractTypeController extends Controller
{
    use ValidatesRequests, ApiResponse;

    public function index()
    {
        return ContractType::all();
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => ['required', 'string', 'max:255'],
        ]);
        ContractType::create([
            'name' => $request->get('name')
        ]);
        return $this->successResponse('ok');
    }


    public function show($id)
    {
        return $this->successResponse(ContractType::findOrFail($id));
    }


    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => ['nullable', 'string', 'max:255'],
        ]);
        if (!ContractType::where('id', $id)->first(['id']))
            return $this->errorResponse(['response.durationTypeNotFound']);
        ContractType::where('id', $id)->update($request->only('name'));
        return $this->successResponse('ok');
    }

    public function destroy($id)
    {
        ContractType::where('id', $id)->delete();
        return $this->successResponse('ok');
    }
}
