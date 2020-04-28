<?php

use Illuminate\Support\Facades\Route;


Route::group([
    'middleware' =>   ['auth:api' , 'company'],
    'prefix' => 'v1/storage'
], function ($q){

    Route::group([
        'prefix' => 'storages'
    ], function ($q) {
        Route::get('/', 'StorageController@index');
        Route::post('/', 'StorageController@store');
        Route::get('/{id}', 'StorageController@show');
        Route::put('/{id}', 'StorageController@update');
        Route::delete('/{id}', 'StorageController@delete');
    }); //storages

    Route::group([
        'prefix' => 'products'
    ], function ($q) {
        Route::get('/', 'ProductController@index');
        Route::get('/{id}', 'ProductController@show');
        Route::post('/', 'ProductController@store');
        Route::post('/increase/{id}', 'ProductController@increase');
        Route::post('/reduce/{id}', 'ProductController@reduce');
        Route::delete('{id}', 'ProductController@delete');
    }); //products

    Route::group([
            'prefix' => 'titles'
    ], function ($q) {
        Route::get('/', 'ProductTitleController@index');
        Route::post('/', 'ProductTitleController@store');
        Route::put('/{id}', 'ProductTitleController@update');
        Route::get('/{id}', 'ProductTitleController@show');
        Route::delete('/{id}', 'ProductTitleController@delete');
    }); //titles

    Route::group([
        'prefix' => 'kinds'
    ], function ($q) {
        Route::get('/', 'ProductKindController@index');
        Route::get('/{id}', 'ProductKindController@show');
        Route::post('/', 'ProductKindController@store');
        Route::put('/{id}', 'ProductKindController@update');
        Route::delete('{id}', 'ProductKindController@update');
    }); //kinds

    Route::group([
        'prefix' => 'report'
    ], function ($q) {
        Route::get('/income', 'ReportController@income');
        Route::get('/outcome', 'ReportController@outCome');
    }); //report

    Route::group([
        'prefix' => 'units'
    ] , function ($r){
        Route::get('/', 'UnitController@index');
    });
});
