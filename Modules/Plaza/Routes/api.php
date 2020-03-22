<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'v1/plaza'
], function ($route) {

    Route::group([
        'prefix' => 'floors'
    ], function () {
            Route::get('/', 'FloorController@index');
            Route::post('/', 'FloorController@store');
            Route::get('/{id:[0-9]+}', 'FloorController@show');
            Route::post('/{id:[0-9]+}/update', 'FloorController@update');
            Route::post('/{id:[0-9]+}/destroy'  ,'FloorController@destroy');
        }); //floors

    Route::group([
        'prefix' => 'offices'
    ], function () {

        Route::get('/', 'OfficeController@index');

//        Route::get('/my', 'OfficeController@my'); //  if we need to ty office then ->  my offices in office controller

        Route::post('/', 'OfficeController@store');
        Route::get('/{id:[0-9]+}', 'OfficeController@show');
        Route::post('/{id:[0-9]+}/update', 'OfficeController@update');
        Route::post('/{id:[0-9]+}/delete', 'OfficeController@delete');

        Route::post("/{id:[0-9]+}/contact/add", 'OfficeController@contactAdd');
        Route::post("/{id:[0-9]+}/contact/update", 'OfficeController@contactUpdate');
        Route::post("/{id:[0-9]+}/contact/delete", 'OfficeController@contactDelete');

        Route::post("/{id:[0-9]+}/location/add", 'OfficeController@locationAdd');
        Route::post("/{id:[0-9]+}/location/update", 'OfficeController@locationUpdate');
        Route::post("/{id:[0-9]+}/location/delete", 'OfficeController@locationDestroy');

        Route::post("/{id:[0-9]+}/users/add", 'OfficeController@addUser');
        Route::post("/{id:[0-9]+}/users/remove", 'OfficeController@removeUser');
        Route::get("/users", 'OfficeController@getOfficeAssignedToUser');


        Route::group([
            'prefix' => '{id:[0-9]+}/documents'
        ], function () {
            Route::post('/add', 'OfficeController@documentAdd');
            Route::post('/update', 'OfficeController@documentUpdate');
            Route::post('/delete', 'OfficeController@documentDestroy');

        }); //documents


        Route::group([
            'prefix' => '{id:[0-9]+}/payments'
        ], function () {
            Route::get('/', 'PaymentController@index');
            Route::post('/', 'PaymentController@pay');
            Route::post('/punishment', 'PaymentController@payForPunishment');
            Route::post('/punishment/add', 'PaymentController@createAdditive');
            Route::post('/punishment/update', 'PaymentController@updateAdditive');
            Route::post('/punishment/delete', 'PaymentController@deleteAdditive');

        }); //payments

    }); //offices


    Route::group([
        'prefix' => 'workers'
    ], function ($route) {
        Route::get('/' , 'WorkerController@index');
        Route::get('/all' , 'WorkerController@all');
        Route::post('/' , 'WorkerController@store');
        Route::get('/{id:[0-9]+}' , 'WorkerController@show');
        Route::post('/{id:[0-9]+}/update' , 'WorkerController@update');
        Route::post('/{id:[0-9]+}/delete' , 'WorkerController@delete');
    }); //workers

    Route::group([
        'prefix' => 'attendance'
    ], function ($route) {
        Route::get('/' , 'AttendanceController@index');
        Route::get('/advance' , 'AttendanceController@showByOffice');
        Route::post('/' , 'AttendanceController@store');
    }); //attendance


    Route::group([
        'prefix' => 'roles'
    ], function ($route) {
        Route::get('/' , 'WorkerController@getRoles');
        Route::post('/' , 'WorkerController@storeRole');
        Route::get('/{id:[0-9]+}' , 'WorkerController@showRole');
        Route::post('/{id:[0-9]+}/update' , 'WorkerController@updateRole');
        Route::post('/{id:[0-9]+}/delete' , 'WorkerController@deleteRole');
    }); //roles

    Route::group([
        'prefix' => 'cards'
    ], function ($route) {
        Route::get('/' , 'CardController@index');
        Route::post('/' , 'CardController@store');
        Route::get('/{id:[0-9]+}' , 'CardController@show');
        Route::post('/{id:[0-9]+}/update' , 'CardController@update');
        Route::post('/{id:[0-9]+}/delete' , 'CardController@delete');
    }); //roles

    Route::group([
        'prefix' => 'meeting'
    ], function () {
        Route::group([
            'prefix' => 'rooms'
        ], function () {
            Route::group([
                'prefix' => 'images'
            ], function () {
                Route::get('/', 'MeetingRoomImageController@index');
                Route::post('/', 'MeetingRoomImageController@store');
                Route::post('/delete/{id:[0-9]+}', 'MeetingRoomImageController@delete');
            });
            Route::get('/', 'MeetingRoomController@getAllRooms');
            Route::post('/', 'MeetingRoomController@storeRooms');
            Route::get('/{id:[0-9]+}', 'MeetingRoomController@showRooms');
            Route::post('/{id:[0-9]+}/update', 'MeetingRoomController@updateRoom');
            Route::post('/{id:[0-9]+}/delete', 'MeetingRoomController@deleteRoom');
        });

        Route::get('/', 'MeetingRoomController@index');
        Route::get('/{id:[0-9]+}', 'MeetingRoomController@show');
        Route::post('/{id:[0-9]+}/update/for/plaza', 'MeetingRoomController@updateForPlaza');
        Route::post('/', 'MeetingRoomController@store');
        Route::post('/{id:[0-9]+}/update', 'MeetingRoomController@update');
        Route::post('/{id:[0-9]+}/delete', 'MeetingRoomController@delete');
    }); //meeting

    Route::group([
        'prefix' => 'guests'
    ], function () {

        Route::get('/', 'GuestController@index');
        Route::get('/{id:[0-9]+}', 'GuestController@show');
        Route::post('/', 'GuestController@store');
        Route::post('/{id:[0-9]+}/update', 'GuestController@update');
        Route::post('/{id:[0-9]+}/delete', 'GuestController@delete');
    }); //guests

    Route::group([
        'prefix' => 'offers'
    ], function () {
        Route::get('/', 'OffersController@index');
        Route::get('/{id:[0-9]+}', 'OffersController@show');
        Route::post('/', 'OffersController@store');
        Route::post('/{id:[0-9]+}/update', 'OffersController@update');
        Route::post('/{id:[0-9]+}/delete', 'OffersController@delete');
    }); //offers

    Route::group([
        'prefix'=>'dialogs'
    ] , function(){

        Route::group(['prefix'=>'offices'] , function(){
            Route::get('/' , 'DialogController@getDialogWithPlaza');
            Route::get('/{id:[0-9]+}' , 'DialogController@showDialogWithPlaza');
            Route::post('/{id:[0-9]+}/update' , 'DialogController@updateDialogForOffice');

            Route::post('/' , 'DialogController@createToPlaza');
            Route::post('/{id:[0-9]+}/message' , 'DialogController@addMessageFromOffice');
        });

        Route::group(['prefix'=>'plaza'] , function(){
            Route::get('/' , 'DialogController@getDialogWithOffices');
            Route::get('/{id:[0-9]+}' , 'DialogController@showDialogWithOffices');
            Route::post('/{id:[0-9]+}/assign' , 'DialogController@updateDialogForPlaza');
            Route::post('/' , 'DialogController@createToOffice');
            Route::post('/{id:[0-9]+}/message' , 'DialogController@addMessageFromPlaza');
        });
        Route::get('/kinds' , 'KindController@index');

    });
    Route::group([
        'prefix' => 'specializations'
    ], function () {
        Route::get('/' , 'SpecializationController@index');
        Route::get('/{id:[0-9]+}' , 'SpecializationController@show');

        Route::post('/' , 'SpecializationController@store');
        Route::post('/{id:[0-9]+}/update' , 'SpecializationController@update');
        Route::post('/{id:[0-9]+}/delete' , 'SpecializationController@delete');

    }); //specializations


    Route::group([
        'prefix' => 'statistics'
    ], function () {
        Route::get('/dialogs', 'StatisticController@dialogsStatistic');
        Route::get('/floors', 'StatisticController@floorsStatistic');
    }); //statistics

//    Route::group([
//        'prefix'=>'data'
//    ],function ($q){
//        Route::get('/floors', 'DataController@floors');
//        Route::get('/offices', 'DataController@offices');
//    });
});
