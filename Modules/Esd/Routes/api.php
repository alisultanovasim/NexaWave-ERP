<?php


use Illuminate\Support\Facades\Route;

Route::group([
    "prefix" => "v1/esd",
    'middleware' =>   ['auth:api' , 'company']

], function ($route) {


    Route::get('regions', 'SenderCompanies@getAllRegions');

    Route::group([
        "prefix" => "documents"
    ], function ($rotes) {


        /** crud doc*/
        Route::get("/", "DocumentController@index");
        Route::get("/{id}", "DocumentController@show");

        Route::post("/add/documents/{id}", "DocumentController@addDocument");
        Route::post("/update/document/{id}", "DocumentController@updateDocument");

        Route::post("/{id}", "DocumentController@show");

        Route::post("/", "DocumentController@store");
        Route::post("/delete/{id}", "DocumentController@destroy");
        Route::post("/update/{id}", "DocumentController@update");

        Route::post("/admin/update/{id}", "DocumentController@updateForAdmin");
        //  Route::get("/tome" , "DocumentController@getDocumentToMe");

        /**  start  assignment */
            Route::post("/to/{id}", "AssignmentController@store");
            Route::post("/update/assignment/{id}", "AssignmentController@update");
            Route::post('/remove/assignment/{id}', 'AssignmentController@delete');


            Route::post("/{id}/add/user", "AssignmentController@addUser");
            Route::post("/{id}/remove/user", "AssignmentController@removeUser");


            Route::post("/mark/done/{id}", "AssignmentController@done");
            Route::post("/tome/add/notes/{id}", "AssignmentController@addNotes");
            Route::post("/tome/update/note/{id}", "AssignmentController@updateNote");
            Route::post("/tome/remove/note/{id}", "AssignmentController@deleteNote");

            Route::post("/mark/read/{id}", "AssignmentController@markAsRead");

            Route::post('/add/helper/user/{id}', 'AssignmentController@addUsersByMainAssignment');



        Route::group([
            'prefix' => 'assignment'
        ], function ($r) {
            Route::get('/templates', 'AssignmentTemplates@index');
            Route::post('/templates', 'AssignmentTemplates@store');
            Route::post('/templates/update', 'AssignmentTemplates@update');
            Route::post('/templates/delete', 'AssignmentTemplates@delete');

                Route::post("/change/status/{id}", "AssignmentController@changeStatus");
                Route::get('/', 'AssignmentController@index');
                Route::get('/{id}', 'AssignmentController@show');

        });

        /** end assignment */


        Route::post("/activate/{id}", "DocumentController@makeActive");

        Route::get("/sections", "SectionController@index");

        Route::get('/send/types', 'SectionController@getSendTypes');
        Route::get('/send/forms', 'SectionController@getSendForms');


        Route::get("/document_no", "DocumentController@getDocumentsNo");
        Route::get("/register_number", "DocumentController@getDocumentsRegNo");


        Route::post('/change/status', '/{id}DocumentController@changeStatus');
        Route::post('/make/archive', 'ArchiveController@store');

        Route::post('/archive/update/{id}', 'ArchiveController@update');

        Route::group(['prefix' => 'adjustments'], function () {
            Route::get('/', 'AdjustmentController@index');
            Route::post('/', 'AdjustmentController@update');
        });

    });//documents

    Route::group([
        'prefix' => 'sender'
    ], function ($q) {
        Route::get('/companies', 'SenderCompanies@index');
        Route::post('/companies', 'SenderCompanies@store');
        Route::post('/companies/update', 'SenderCompanies@update');
        Route::post('/companies/delete', 'SenderCompanies@delete');


        Route::get('/users', 'SenderCompaniesUser@index');
        Route::post('/users', 'SenderCompaniesUser@store');
        Route::post('/users/update', 'SenderCompaniesUser@update');
        Route::post('/users/delete', 'SenderCompaniesUser@delete');

        Route::get('/role', 'SenderCompaniesUser@getAllRoles');
        Route::post('/role', 'SenderCompaniesUser@storeRole');
        Route::post('/role/update', 'SenderCompaniesUser@updateRole');
        Route::post('/role/delete', 'SenderCompaniesUser@deleteRole');
    });//sender

    Route::group([
        "prefix" => "draft"
    ], function ($rotes) {
        Route::get("/", "DraftController@index");
        Route::post("/", "DraftController@store");
        Route::get("/{id}", "DraftController@show");
        Route::post("/update/{id}", "DraftController@update");
        Route::post("/delete/{id}", "DraftController@destroy");

        Route::post("/add/documents/{id}", "DraftController@addDocument");
        Route::post("/remove/documents/{id}", "DraftController@removeDocument");
        Route::post("/update/documents/{id}", "DraftController@updateDocument");

        Route::post("/make/document/{id}", "DraftController@makeRealDocument");
    });//draft


    Route::group([
        "prefix" => "data"
    ], function ($rotes) {
        Route::get('/' , 'DataController@index');
    });//data

//    Route::group([
//        "prefix" => "inbox",
//    ], function ($rotes) {
//        Route::get("/", "InboxController@index");
//        Route::get("/{id}", "InboxController@show");
//        Route::post("/{id}/markasread", "InboxController@markAsRead");
//        Route::post("/", "InboxController@store");
//
//        Route::post("/{id}/delete", "InboxController@destroy");
//        Route::post("/delete/many", "InboxController@destroyMany");
//        Route::post("/{id}/softdelete", "InboxController@markAsDelete");
//        Route::post("/softdelete/many", "InboxController@markAsDeleteMany");
//
//        Route::get("/getnew", "InboxController@getNotReadCount");
//
//    }); //inbox

    Route::group([
        'prefix' => 'statistics'
    ], function ($r) {
        Route::get('/documents', 'StatisticsController@documents');
    });
});

