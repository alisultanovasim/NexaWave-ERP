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
        Route::put('/{id}', 'ProductController@update');
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
        Route::delete('{id}', 'ProductKindController@delete');
    }); //kinds

    Route::group([
        'prefix' => 'models'
    ], function ($q) {
        Route::get('/', 'ProductModelController@index');
        Route::get('/{id}', 'ProductModelController@show');
        Route::post('/', 'ProductModelController@store');
        Route::put('/{id}', 'ProductModelController@update');
        Route::delete('{id}', 'ProductModelController@delete');
    }); //models

    Route::group([
        'prefix' => 'states'
    ], function ($q) {
        Route::get('/', 'ProductStateController@index');
        Route::get('/{id}', 'ProductStateController@show');
        Route::post('/', 'ProductStateController@store');
        Route::put('/{id}', 'ProductStateController@update');
        Route::delete('{id}', 'ProductStateController@delete');
    }); //states


    Route::group([
        'prefix' => 'colors'
    ], function ($q) {
        Route::get('/', 'ProductColorController@index');
        Route::get('/{id}', 'ProductColorController@show');
        Route::post('/', 'ProductColorController@store');
        Route::put('/{id}', 'ProductColorController@update');
        Route::delete('{id}', 'ProductColorController@delete');
    }); //states


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
