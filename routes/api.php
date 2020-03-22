<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'v1'
] , function ($router) {
    Route::get('/profile', 'ProfileController@info');

    Route::group([
        'prefix' => 'logs',
    ], function () {
        Route::get('/', 'GlobalLogsController@index');
        Route::get('/{id:[0-9]+}', 'GlobalLogsController@show');
        Route::post('/delete/{id:[0-9]+}', 'GlobalLogsController@delete');
    }); // logs

    Route::group([
        'prefix' => "auth",
    ], function () {
        Route::get("/module/permissions", "Auth\ModulePermissionController@getModuleAndPermissionList");
        Route::post("/role", "Auth\RoleController@store");
        Route::put("/role/{id}", "Auth\RoleController@update");
        Route::delete("/role/{id}", "Auth\RoleController@destroy");

        Route::post("/users", "Auth\UserController@store");
        Route::delete("/users/{id}", "Auth\UserController@destroy");
        Route::get("/users", "Auth\UserController@index");
        Route::put("/password/change", "Auth\UserController@changePassword");

        Route::post('/forgot', 'Auth\UserController@sendResetLinkToEmail');
        Route::post('/validate/hash/{hash}', 'Auth\UserController@checkResetHashExists');
        Route::post('/reset/password', 'Auth\UserController@reset');

        Route::post('/login' , 'Auth\UserController@login');
        Route::post('/register' , 'Auth\UserController@register');
    });

});


//todo need to delete
Route::group([], function ($route) {
    Route::get('/v1/users', function () {
        $users = User::where('company_id', Auth::user()->company_id)->get(['username', 'id']);
        return response($users, 200)->header('Content-Type', 'application/json');
    });
    Route::get('/v1/part', function () {
        $parts = config('static-data.part');
        return response($parts, 200)->header('Content-Type', 'application/json');
    });
});
