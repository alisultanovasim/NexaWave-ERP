<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'v1'
] , function ($router) {

    Route::group([
        'prefix' => 'profile',
        'middleware' => 'auth:api',
        'namespace' => 'Auth'
    ] , function ($r) {
        Route::get('/', 'ProfileController@profile');
        Route::post('/update', 'ProfileController@update');
        Route::get('/me', 'ProfileController@index');
        Route::get('/history', 'ProfileController@history');
    }); // profile



    Route::group([
        'prefix' => 'users',
        'middleware' => 'auth:api',
        'namespace' => 'Auth'
    ] , function ($r) {
        Route::get('/', 'UserController@index');
        Route::post('/', 'UserController@store');
        Route::put('/{id}', 'UserController@update');
        Route::get('/{id}', 'UserController@show');
        //delete
        Route::get('/search', 'UserController@searchByFin');

    }); // profile


    Route::group([
        'prefix' => 'logs',
    ], function () {
        Route::get('/', 'GlobalLogsController@index');
        Route::get('/{id:[0-9]+}', 'GlobalLogsController@show');
        Route::post('/delete/{id:[0-9]+}', 'GlobalLogsController@delete');
    }); // logs

    Route::group([
        'prefix' => "auth",
        'namespace' => 'Auth'
    ], function () {
        Route::get("/module/permissions", "ModulePermissionController@getModuleAndPermissionList");
        Route::post("/role", "RoleController@store");
        Route::put("/role/{id}", "RoleController@update");
        Route::delete("/role/{id}", "RoleController@destroy");

        Route::post("/users", "UserController@store");
        Route::delete("/users/{id}", "UserController@destroy");
        Route::get("/users", "UserController@index");
        Route::put("/password/change", "UserController@changePassword");

        Route::post('/forgot', 'UserController@sendResetLinkToEmail');
        Route::post('/validate/hash/{hash}', 'UserController@checkResetHashExists');
        Route::post('/reset/password', 'UserController@reset');

        Route::post('/login' , 'UserController@login');
        Route::post('/register' , 'UserController@register');
    }); // auth

    Route::group(['prefix' => 'permissions', 'middleware' => ['auth:api', 'company']], function (){
        Route::post('set', 'PermissionController@setPositionPermissions');
        Route::get('/modules', 'PermissionController@getModules');
        Route::get('/', 'PermissionController@getPermissions');
        Route::get('/positions', 'PermissionController@getPositions');
    }); // permissions

});


////todo need to delete
//Route::group([], function ($route) {
//    Route::get('/v1/users', function () {
//        $users = User::where('company_id', Auth::user()->company_id)->get(['username', 'id']);
//        return response($users, 200)->header('Content-Type', 'application/json');
//    });
//    Route::get('/v1/part', function () {
//        $parts = config('static-data.part');
//        return response($parts, 200)->header('Content-Type', 'application/json');
//    });
//});
