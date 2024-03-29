<?php

namespace Modules\TaskManager\Http\Controllers;

use App\Http\Controllers\Controller;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Modules\TaskManager\Entities\Task;
use Modules\TaskManager\Entities\TaskWatcher;
use Throwable;

/**
 * Class TaskController
 * @package Modules\TaskManager\Http\Controllers
 */
class TaskController extends Controller
{

    /**
     *
     */
    public function index()
    {

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
            "list_id" => "required|integer|exists:tm_lists,id",
            "parent_id" => "sometimes|required|integer|exists:tm_tasks,id",
            "assigned_id" => "sometimes|required|integer|exists:users,id",
            "name" => "required|string|min:1|max:300",
            "description" => "sometimes|required|string|min:1",
            "deadline" => "sometimes|required|date|date_format:Y-m-d",
            "budget" => "sometimes|required|numeric",
            "watchers" => "sometimes|required|array",
            "watchers.*" => "required_with:watchers|integer|exists:users,id"
        ]);

        DB::transaction(function () use ($request) {
            $task = new Task();
            $task->fill($request->only([
                'list_id',
                'parent_id',
                'name',
                'description',
                'deadline',
                'budget'
            ]));
            $task->status = Task::PENDING;
            $task->created_id = auth()->id();
            $task->save();
            $this->addWatchers($task->id, $request->input("watchers"));
        });

        return $this->successResponse(trans("responses.success_add"), 201);
    }

    /**
     * @param $task_id
     * @param $watchers
     */
    private function addWatchers($task_id, $watchers)
    {
        $watcherUsers = [];
        foreach ($watchers as $user_id) {
            $watcherUsers[] = [
                'user_id' => $user_id,
                'task_id' => $task_id
            ];
        }

        (new TaskWatcher)->insert($watcherUsers);
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function show($id)
    {
        $task = Task::with([
            'watchers',
            "createdBy:id,name,surname,username",
            'parent:id,name,status',
            'subTasks',
            'comments',
            'files',
            'watchers'
        ])->findOrFail($id);

        return $this->dataResponse($task);
    }


    /**
     * @param $id
     * @param $status
     * @return JsonResponse
     */
    public function changeTaskStatus($id, $status)
    {
        if (!in_array($status, Task::statuses()))
            return $this->errorResponse(trans('responses.status_not_found'));

        DB::transaction(function () use ($id, $status) {
            $task = Task::findOrFail($id);
            $task->fill(
                [
                    "status" => $status
                ]
            );
            $task->save();
        });
        return $this->successResponse(trans('responses.success_update'));

    }
}
