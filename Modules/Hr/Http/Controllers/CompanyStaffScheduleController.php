<?php

namespace Modules\Hr\Http\Controllers;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class CompanyStaffScheduleController extends Controller
{
    use ApiResponse, ValidatesRequests;

    public function index(Request $request): JsonResponse
    {
        $this->validate($request, [
            'per_page' => 'nullable|integer'
        ]);
    }
}
