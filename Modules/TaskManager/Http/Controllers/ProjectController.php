<?php

namespace Modules\TaskManager\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\TaskManager\Entities\Project;
use Modules\TaskManager\Entities\ProjectUser;
use Throwable;

/**
 * Class ProjectController
 * @package Modules\TaskManager\Http\Controllers
 */
class ProjectController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function index(Request $request)
    {
        $this->validate($request, [
            'name' => "sometimes|required|min:1|string"
        ]);
        $projects = (new Project())->isActive()->select([
            'id',
            'name',
        ])->get();
        return $this->dataResponse($projects);

    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     * @throws Throwable
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
            "user_ids.*.user_id" => "required_with:user_ids|int",
            "user_ids.*.role_id" => "required_with:user_ids|integer",
            "emails" => "sometimes|required|array|min:1",
            "emails.*.email" => "required_with:emails|email",
            "emails.*.role_id" => "required_with:emails|integer"
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
            $this->saveUsers($project->id, $request->input("user_ids"));
            $this->emailInvitation($request->input("emails"));
        });
        return $this->successResponse(trans('responses.success_add'));
    }

    /**
     * @param $project_id
     * @param $user_ids
     */
    private function saveUsers($project_id, $user_ids)
    {
        $users[] = [
            'project_id' => $project_id,
            "user_id" => auth()->id(),
            "role_id" => 1 //TODO change this to group admin role
        ];
        foreach ($user_ids as $item) {
            $users[] = [
                'project_id' => $project_id,
                "user_id" => $item['user_id'],
                "role_id" => $item['role_id']
            ];
        }
        (new ProjectUser)->insert($users);
    }

    /**
     * @param $emails
     */
    private function emailInvitation($emails)
    {
        //TODO Write email invitation email to here.
    }
}
