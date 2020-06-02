<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1'] , function ($router) {

    Route::group(['prefix' => 'profile', 'middleware' => ['auth:api', 'authorize'], 'namespace' => 'Auth'] , function ($r) {
        Route::get('/', 'ProfileController@profile');
        Route::post('/update', 'ProfileController@update');
        Route::get('/me', 'ProfileController@index');
        Route::get('/history', 'ProfileController@history');
    }); // profile


    Route::group(['prefix' => 'users', 'middleware' => 'auth:api', 'namespace' => 'Auth'] , function ($r) {
        Route::get('/', 'UserController@index');
        Route::post('/', 'UserController@store');
        Route::put('/{id}', 'UserController@update');
        Route::get('/{id}', 'UserController@show');
        //delete
        Route::get('/search', 'UserController@searchByFin');

    }); // profile


    Route::group(['prefix' => 'logs',], function () {
        Route::get('/', 'GlobalLogsController@index');
        Route::get('/{id:[0-9]+}', 'GlobalLogsController@show');
        Route::post('/delete/{id:[0-9]+}', 'GlobalLogsController@delete');
    }); // logs

    Route::group(['prefix' => "auth", 'namespace' => 'Auth'], function () {
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

    Route::group(['prefix' => 'permissions', 'middleware' => ['auth:api', 'authorize']], function (){
        Route::post('set', 'PermissionController@setRolePermissions');
        Route::get('/modules', 'PermissionController@getModules');
        Route::get('/', 'PermissionController@getPermissions');
        Route::get('/positions', 'PermissionController@getPositions');
        Route::get('/roles', 'PermissionController@getRoles');
        Route::get('/roles/{id}', 'PermissionController@getRolePermissions');
        Route::get('/module/{id}', 'PermissionController@userGetPermissionsByModuleId');
    }); // permissions

    Route::get('/test', 'TestController@test')->middleware(['auth:api', 'authorize']);


});

