<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group([
    'prefix' => "v1/task-manager",
    "middleware" => "auth:api"
], function ($tm) {

    $tm->group(['prefix' => "project"], function ($project) {
        $project->post("/", "ProjectController@store");
        $project->get("/", "ProjectController@index");
    });

    $tm->group(['prefix' => "list", "middleware" => "projectId"], function ($list) {
        $list->post("/", "ListController@store");
        $list->get("/", "ListController@index");
    });

    $tm->group(['prefix' => "task", "middleware" => "projectId"], function ($task) {
        $task->post("/", "TaskController@store");
        $task->get("/{id}", "TaskController@show");
        $task->get("/", "TaskController@index");
        $task->put("/{id}/status/{status}", "TaskController@changeTaskStatus");


        $task->group(['prefix' => "comment", "middleware" => "projectId"], function ($comment) {
            $comment->post("/", "CommentController@store");
            $comment->put("/{uuid}", "CommentController@update");
        });
    });

});
