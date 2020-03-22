<?php


use Illuminate\Support\Facades\Route;

Route::group([
    "prefix" => "v1",
    'middleware' =>   ['auth:api' , 'company']

], function ($route) {


    Route::get('regions', 'SenderCompanies@getAllRegions');

    Route::group([
        "prefix" => "documents"
    ], function ($rotes) {


        /** crud doc*/
        Route::get("/", "DocumentController@index");
        Route::get("/{id:[0-9]+}", "DocumentController@show");

        Route::post("/{id:[0-9]+}/add/documents", "DocumentController@addDocument");
        Route::post("/{id:[0-9]+}/update/document", "DocumentController@updateDocument");

        Route::post("/{id:[0-9]+}", "DocumentController@show");

        Route::post("/", "DocumentController@store");
        Route::post("/{id:[0-9]+}/delete", "DocumentController@destroy");
        Route::post("/{id:[0-9]+}/update", "DocumentController@update");

        Route::post("/{id:[0-9]+}/admin/update", "DocumentController@updateForAdmin");
        //  Route::get("/tome" , "DocumentController@getDocumentToMe");

        /**  start  assignment */
            Route::post("/{id:[0-9]+}/to", "AssignmentController@store");
            Route::post("/{id:[0-9]+}/update/assignment", "AssignmentController@update");
            Route::post('/{id:[0-9]+}/remove/assignment', 'AssignmentController@delete');


            Route::post("/{id:[0-9]+}/add/user", "AssignmentController@addUser");
            Route::post("/{id:[0-9]+}/remove/user", "AssignmentController@removeUser");


            Route::post("/{id:[0-9]+}/mark/done", "AssignmentController@done");
            Route::post("/{id:[0-9]+}/tome/add/notes", "AssignmentController@addNotes");
            Route::post("/{id:[0-9]+}/tome/update/note", "AssignmentController@updateNote");
            Route::post("/{id:[0-9]+}/tome/remove/note", "AssignmentController@deleteNote");

            Route::post("/{id:[0-9]+}/mark/read", "AssignmentController@markAsRead");

            Route::post('/{id:[0-9]+}/add/helper/user', 'AssignmentController@addUsersByMainAssignment');



        Route::group([
            'prefix' => 'assignment'
        ], function ($r) {
            Route::get('/templates', 'AssignmentTemplates@index');
            Route::post('/templates', 'AssignmentTemplates@store');
            Route::post('/templates/update', 'AssignmentTemplates@update');
            Route::post('/templates/delete', 'AssignmentTemplates@delete');

                Route::post("/{id:[0-9]+}/change/status", "AssignmentController@changeStatus");
                Route::get('/', 'AssignmentController@index');
                Route::get('/{id:[0-9]+}', 'AssignmentController@show');

        });

        /** end assignment */


        Route::post("/{id:[0-9]+}/activate", "DocumentController@makeActive");

        Route::get("/sections", "SectionController@index");

        Route::get('/send/types', 'SectionController@getSendTypes');
        Route::get('/send/forms', 'SectionController@getSendForms');


        Route::get("/document_no", "DocumentController@getDocumentsNo");
        Route::get("/register_number", "DocumentController@getDocumentsRegNo");


        Route::post('/{id:[0-9]+}/change/status', 'DocumentController@changeStatus');
        Route::post('/make/archive', 'ArchiveController@store');

        Route::post('/{id:[0-9]+}/archive/update', 'ArchiveController@update');

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
        Route::get("/{id:[0-9]+}", "DraftController@show");
        Route::post("/{id:[0-9]+}/update", "DraftController@update");
        Route::post("/{id:[0-9]+}/delete", "DraftController@destroy");

        Route::post("/{id:[0-9]+}/add/documents", "DraftController@addDocument");
        Route::post("/{id:[0-9]+}/remove/documents", "DraftController@removeDocument");
        Route::post("/{id:[0-9]+}/update/documents", "DraftController@updateDocument");

        Route::post("/{id:[0-9]+}/make/document", "DraftController@makeRealDocument");
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
//        Route::get("/{id:[0-9]+}", "InboxController@show");
//        Route::post("/{id:[0-9]+}/markasread", "InboxController@markAsRead");
//        Route::post("/", "InboxController@store");
//
//        Route::post("/{id}/delete", "InboxController@destroy");
//        Route::post("/delete/many", "InboxController@destroyMany");
//        Route::post("/{id:[0-9]+}/softdelete", "InboxController@markAsDelete");
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

