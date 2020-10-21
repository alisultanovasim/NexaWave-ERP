<?php

namespace Modules\TaskManager\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\TaskManager\Entities\Project;

/**
 * Class ProjectController
 * @package Modules\TaskManager\Http\Controllers
 */
class ProjectController extends Controller
{
    public function index(Request $request)
    {

    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => "required|string|min:1|max:255",
            "start_date" => "required|date|date_format:Y-m-d",
            "end_date" => "required|date|date_format:Y-m-d",
            "is_active" => "sometimes|required|boolean",
            "company_id" => "required|int|exists:companies,id",
            "contract_id" => "sometimes|required|int", //TODO Check exists in contracts table
            "user_ids" => "required|array|min:1",
            "user_ids.*" => "required|int",
            "partner_user_ids" => "required|array",
            "partner_user_ids.*" => "required_with:partner_user_ids|int"
        ]);

        DB::transaction(function () use ($request) {
            $project = new Project();
            $project->company_id = $request->input("company_id");
            $project->name = $request->input("name");
            $project->start_date = $request->input("start_date");
            $project->end_date = $request->input("end_date");
            $project->contract_id = $request->input("contract_id");
            $project->is_active = $request->input("is_active", true);
            $project->save();
        });
        return $this->successResponse(trans('responses.success_add'));
    }

    private function saveUsers($project_id, $user_ids, $partner = false)
    {
        $users = [];
        foreach ($user_ids as $id) {
            $users[] = [
                'project_id' => $project_id,
                "user_id" => $id
            ];
        }
    }
}
