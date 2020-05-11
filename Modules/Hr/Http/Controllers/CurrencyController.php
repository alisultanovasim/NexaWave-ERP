<?php

namespace Modules\Hr\Http\Controllers;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Hr\Entities\Currency;
use PHPUnit\Framework\MockObject\Api;

class CurrencyController extends Controller
{
    use ValidatesRequests, ApiResponse;

    public function index()
    {
        return Currency::all();
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => ['required', 'string', 'max:255'],
            'char' => ['required', 'string', 'max:255'],
            'code' => ['required', 'integer'],
        ]);
        Currency::create($request->all());
        return $this->successResponse('ok');
    }


    public function show($id)
    {
        return $this->successResponse(Currency::findOrFail($id));
    }


    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => ['nullable', 'string', 'max:255'],
            'char' => ['nullable', 'string', 'max:255'],
            'code' => ['nullable', 'integer'],
        ]);
        if (!Currency::where('id', $id)->first(['id']))
            return $this->errorResponse(['response.currencyNotFound']);
        Currency::where('id', $id)->update($request->only('name', 'code', 'char'));
        $this->successResponse('ok');

    }

    public function destroy($id)
    {
        Currency::where('id', $id)->delete();
        return $this->successResponse('ok');
    }
}
