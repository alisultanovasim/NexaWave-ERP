<?php

namespace Modules\Hr\Http\Controllers;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Hr\Entities\Currency;
use Modules\Hr\Entities\DurationType;

class DurationTypeController extends Controller
{
    use ValidatesRequests, ApiResponse;

    public function index()
    {
        return DurationType::all();
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => ['required', 'string', 'max:255'],
        ]);
        DurationType::create([
            'name' => $request->get('name')
        ]);
        return $this->successResponse('ok');
    }


    public function show($id)
    {
        return $this->successResponse(DurationType::findOrFail($id));
    }


    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => ['nullable', 'string', 'max:255'],
        ]);
        if (!DurationType::where('id', $id)->first(['id']))
            return $this->errorResponse(['response.durationTypeNotFound']);
        DurationType::where('id', $id)->update($request->only('name'));
        $this->successResponse('ok');

    }

    public function destroy($id)
    {
        DurationType::where('id', $id)->delete();
        return $this->successResponse('ok');
    }
}
