<?php

use Illuminate\Support\Facades\Route;





Route::group([
    'middleware' => ['auth:api', 'authorize'],
    'prefix' => 'v1/storage'
], function ($q) {

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
        Route::get('/all', 'ProductController@index');
        Route::get('/deleted', 'ProductController@getDeletes');
        Route::get('/', 'ProductController@firstPage');
        Route::get('/titles', 'ProductController@getTitles');
        Route::get('/kinds', 'ProductController@getKinds');
        Route::get('/history/{id}', 'ProductController@showHistory');
        Route::get('/{id}', 'ProductController@show');
        Route::get('/filterproducts', 'ProductController@filterProducts');
        Route::put('/{id}', 'ProductController@update');
        Route::post('/', 'ProductController@store');
        Route::post('/increase/{id}', 'ProductController@increase');
        Route::post('/reduce/{id}', 'ProductController@reduce');
        Route::post('/delete/{id}', 'ProductController@delete');
    }); //products

    Route::group([
        'prefix' => 'assignment'
    ], function ($q) {
        Route::get('/', 'ProductAssignmentController@index');
        Route::get('/{id}', 'ProductAssignmentController@show');
        Route::put('/{id}', 'ProductAssignmentController@update');
        Route::post('/', 'ProductAssignmentController@store');
        Route::delete('{id}', 'ProductAssignmentController@delete');
    }); //assignment

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
        Route::delete('{id}', 'ProductStateController@destroy');
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
    ], function ($r) {
        Route::get('/', 'UnitController@index');
    }); //units

    Route::group([
        'prefix' => 'demands'
    ], function () {
        Route::get('/', 'DemandController@index');
        Route::get('/{id}', 'DemandController@show');
        Route::get('/directed-demands', 'DemandController@directedToUserDemandList');
        Route::post('/', 'DemandController@store');
        Route::patch('/{id}', 'DemandController@confirm');
        Route::post('/reject/{id}', 'DemandController@reject')->where('demand-id','[0-9]+');
        Route::put('/{id}', 'DemandController@update');
        Route::delete('/{id}', 'DemandController@delete');

        Route::group([
            'prefix' => 'assignments'
        ], function () {
            Route::get('/', 'DemandAssignmentController@index');
            Route::post('/', 'DemandAssignmentController@store');
            Route::put('/{id}', 'DemandAssignmentController@update');
            Route::delete('/{id}', 'DemandAssignmentController@delete');

            Route::post('/item', 'DemandAssignmentController@addItem');
            Route::put('/item/{id}', 'DemandAssignmentController@updateItem');
            Route::delete('/item/{id}', 'DemandAssignmentController@deleteItem');

            Route::group([
                'prefix' => 'employee'
            ], function () {
                Route::put('/{id}', 'DemandAssignmentController@employeeUpdate');
            });

        });

    }); // demands

    Route::group(['prefix'=>'propose'],function (){
        Route::get('/','ProposeController@index');
        Route::get('/{propose}','ProposeController@show');
        Route::post('/','ProposeController@store');
        Route::post('/reject/{id}','ProposeController@reject');
        Route::post('/{id}','ProposeController@delete');

        Route::group(['prefix'=>'purchase'],function (){
            Route::get('/','PurchaseController@index');
            Route::post('/','PurchaseController@store');
            Route::post('/addtostorage/{id}','PurchaseController@addToStorage')->where('id','[0-9]+');
            Route::post('/add-to-archive/{id}','PurchaseController@addToArchive')->where('id','[0-9]+');
            Route::get('/getpurchasearchive','PurchaseController@getAllPurchaseArchive');
        });
    });//proposes

    Route::group([
        'prefix' => 'acts'
    ], function ($q) {
        Route::get('/', 'SellActController@index');
        Route::get('/{id}', 'SellActController@show');
        Route::post('/', 'SellActController@store');
        Route::put('/{id}', 'SellActController@update');
        Route::delete('/{id}', 'SellActController@delete');

        Route::post('/demand/{id}', 'SellActController@addDemand');
        Route::put('/demand/{id}', 'SellActController@updateDemand');
        Route::delete('/demand/{id}', 'SellActController@deleteDemand');


    });

    Route::group([
        'prefix' => 'suppliers'
    ], function ($q) {
        Route::get('/', 'SupplierController@index');
        Route::get('/{id}', 'SupplierController@show');
        Route::post('/', 'SupplierController@store');
        Route::put('/{id}', 'SupplierController@update');
        Route::delete('{id}', 'SupplierController@delete');
    }); //models
});
