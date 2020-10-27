<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'v1/plaza',
    'middleware' =>   ['auth:api' , 'authorize']
], function ($route) {

    Route::group([
        'prefix' => 'floors'
    ], function () {
            Route::get('/', 'FloorController@index');
            Route::post('/', 'FloorController@store');
            Route::get('/{id}', 'FloorController@show');
            Route::post('/update/{id}', 'FloorController@update');
            Route::post('/destroy/{id}'  ,'FloorController@destroy');
        }); //floors

    Route::group([
        'prefix' => 'offices'
    ], function () {

        Route::get('/', 'OfficeController@index');

//        Route::get('/my', 'OfficeController@my'); //  if we need to ty office then ->  my offices in office controller

        Route::post('/', 'OfficeController@store');
        Route::get('/{id}', 'OfficeController@show');
        Route::post('/update/{id}', 'OfficeController@update');
        Route::post('/delete/{id}', 'OfficeController@delete');

        Route::post("/contact/add/{id}", 'OfficeController@contactAdd');
        Route::post("/contact/update/{id}", 'OfficeController@contactUpdate');
        Route::post("/contact/delete/{id}", 'OfficeController@contactDelete');

        Route::post("/location/add/{id}", 'OfficeController@locationAdd');
        Route::post("/location/update/{id}", 'OfficeController@locationUpdate');
        Route::post("/location/delete/{id}", 'OfficeController@locationDestroy');

        Route::post("/users/add/{id}", 'OfficeController@addUser');
        Route::post("/users/update/{id}", 'OfficeController@updateUser');
        Route::delete("/users/remove/{id}", 'OfficeController@removeUser');
        Route::get("/users/{id}", 'OfficeController@getOfficeAssignedToUser');
        Route::get("/users/show/{id}", 'OfficeController@getOfficeUser');


        Route::group([
            'prefix' => 'documents'
        ], function () {
            Route::post('/add/{id}', 'OfficeController@documentAdd');
            Route::post('/update/{id}/', 'OfficeController@documentUpdate');
            Route::post('/delete/{id}/', 'OfficeController@documentDestroy');

        }); //documents


        Route::group([
            'prefix' => 'payments'
        ], function () {
            Route::get('/{id}', 'PaymentController@index');
            Route::post('/{id}', 'PaymentController@pay');
            Route::post('/punishment/{id}', 'PaymentController@payForPunishment');
            Route::post('/punishment/add/{id}', 'PaymentController@createAdditive');
            Route::post('/punishment/update/{id}', 'PaymentController@updateAdditive');
            Route::post('/punishment/delete/{id}', 'PaymentController@deleteAdditive');

        }); //payments

    }); //offices


    Route::group([
        'prefix' => 'workers'
    ], function ($route) {
        Route::get('/' , 'WorkerController@index');
        Route::get('/all' , 'WorkerController@all');
        Route::post('/' , 'WorkerController@store');
        Route::get('/{id}' , 'WorkerController@show');
        Route::post('/update/{id}' , 'WorkerController@update');
        Route::post('/delete/{id}' , 'WorkerController@delete');
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
        Route::get('/{id}' , 'WorkerController@showRole');
        Route::post('/update/{id}' , 'WorkerController@updateRole');
        Route::post('/delete/{id}' , 'WorkerController@deleteRole');
    }); //roles

    Route::group([
        'prefix' => 'cards'
    ], function ($route) {
        Route::get('/' , 'CardController@index');
        Route::post('/' , 'CardController@store');
        Route::get('/{id}' , 'CardController@show');
        Route::post('/update/{id}' , 'CardController@update');
        Route::post('/delete/{id}' , 'CardController@delete');
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
                Route::post('/delete/{id}', 'MeetingRoomImageController@delete');
            });
            Route::get('/', 'MeetingRoomController@getAllRooms');
            Route::post('/', 'MeetingRoomController@storeRooms');
            Route::get('/{id}', 'MeetingRoomController@showRooms');
            Route::post('/update/{id}', 'MeetingRoomController@updateRoom');
            Route::post('/delete/{id}', 'MeetingRoomController@deleteRoom');
        });

        Route::get('/', 'MeetingRoomController@index');
        Route::get('/{id}', 'MeetingRoomController@show');
        Route::post('/update/for/plaza/{id}', 'MeetingRoomController@updateForPlaza');
        Route::post('/', 'MeetingRoomController@store');
        Route::post('/update/{id}', 'MeetingRoomController@update');
        Route::post('/delete/{id}', 'MeetingRoomController@delete');
    }); //meeting

    Route::group([
        'prefix' => 'guests'
    ], function () {

        Route::get('/', 'GuestController@index');
        Route::get('/{id}', 'GuestController@show');
        Route::post('/', 'GuestController@store');
        Route::post('/update/{id}', 'GuestController@update');
        Route::post('/delete/{id}', 'GuestController@delete');
    }); //guests

    Route::group([
        'prefix' => 'offers'
    ], function () {
        Route::get('/', 'OffersController@index');
        Route::get('/{id}', 'OffersController@show');
        Route::post('/', 'OffersController@store');
        Route::post('/update/{id}', 'OffersController@update');
        Route::post('/delete/{id}', 'OffersController@delete');
    }); //offers

    Route::group([
        'prefix'=>'dialogs'
    ] , function(){

        Route::group(['prefix'=>'offices'] , function(){
            Route::get('/' , 'DialogController@getDialogWithPlaza');
            Route::get('/{id}' , 'DialogController@showDialogWithPlaza');
            Route::post('/update/{id}' , 'DialogController@updateDialogForOffice');

            Route::post('/' , 'DialogController@createToPlaza');
            Route::post('/message/{id}' , 'DialogController@addMessageFromOffice');
        });

        Route::group(['prefix'=>'plaza'] , function(){
            Route::get('/' , 'DialogController@getDialogWithOffices');
            Route::get('/{id}' , 'DialogController@showDialogWithOffices');
            Route::post('/assign/{id}' , 'DialogController@updateDialogForPlaza');
            Route::post('/' , 'DialogController@createToOffice');
            Route::post('/message/{id}' , 'DialogController@addMessageFromPlaza');
        });
        Route::get('/kinds' , 'KindController@index');

    });
    Route::group([
        'prefix' => 'specializations'
    ], function () {
        Route::get('/' , 'SpecializationController@index');
        Route::get('/{id}' , 'SpecializationController@show');

        Route::post('/' , 'SpecializationController@store');
        Route::post('/update/{id}' , 'SpecializationController@update');
        Route::post('/delete/{id}' , 'SpecializationController@delete');

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
