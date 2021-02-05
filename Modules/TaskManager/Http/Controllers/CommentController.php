<?php

namespace Modules\TaskManager\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Modules\TaskManager\Entities\TaskComment;

/**
 * Class CommentController
 * @package Modules\TaskManager\Http\Controllers
 */
class CommentController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'task_id' => "required|integer|exists:tm_tasks,id",
            'comment' => "required|string|min:1"
        ]);

        $comment = new TaskComment();
        $comment->fill([
            'comment' => $request->input("comment"),
            'task_id' => $request->input("task_id"),
            'user_id' => auth()->id()
        ]);
        $comment->save();

        return $this->dataResponse($this->getComment($comment->id));
    }

    /**
     * @param $id
     * @return Builder|Builder[]|Collection|Model|TaskComment
     */
    private function getComment($id)
    {
        return TaskComment::with(["user:id,name,username"])->find($id);
    }


    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     * @throws ValidationException
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'comment' => "required|string|min:1"
        ]);

        $comment = TaskComment::findOrFail($id);
        $comment->fill($request->only("comment"));
        $comment->save();
        return $this->dataResponse($this->getComment($id));
    }
}
