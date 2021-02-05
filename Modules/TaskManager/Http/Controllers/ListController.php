<?php

namespace Modules\TaskManager\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Modules\TaskManager\Entities\TaskList;

/**
 * Class ListController
 * @package Modules\TaskManager\Http\Controllers
 */
class ListController extends Controller
{

    public function index()
    {
        $lists = TaskList::whereHas("project.users", function ($q) {
            $q->where("user_id", auth()->id());
        })->isNotArchive()->get();
        return $this->dataResponse($lists);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            "project_id" => "required|integer|exists:tm_projects,id",
            "name" => "required|string|min:1|max:255",
        ]);

        $list = new TaskList();
        $list->fill($request->only([
            'project_id',
            'name'
        ]));
        $list->is_archive = false;
        $list->save();

        return $this->dataResponse($list, 201);
    }
}
