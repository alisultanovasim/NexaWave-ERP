<?php

namespace Modules\Hr\Http\Controllers;

use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Hr\Entities\BloodGroup;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class BloodGroupController
 * @package Modules\Hr\Http\Controllers
 */
use Illuminate\Routing\Controller;

class BloodGroupController extends Controller
{
    use ApiResponse,ValidatesRequests;

    /**
     * @return JsonResponse
     */
    public function index()
    {
        $bloodGroups = (new BloodGroup())->where("is_active", true)->get();
        return $this->dataResponse($bloodGroups);
    }
}
