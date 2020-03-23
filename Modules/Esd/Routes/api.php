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

        Route::post("/{id}/add/documents", "DocumentController@addDocument");
        Route::post("/{id}/update/document", "DocumentController@updateDocument");

        Route::post("/{id}", "DocumentController@show");

        Route::post("/", "DocumentController@store");
        Route::post("/{id}/delete", "DocumentController@destroy");
        Route::post("/{id}/update", "DocumentController@update");

        Route::post("/{id}/admin/update", "DocumentController@updateForAdmin");
        //  Route::get("/tome" , "DocumentController@getDocumentToMe");

        /**  start  assignment */
            Route::post("/{id}/to", "AssignmentController@store");
            Route::post("/{id}/update/assignment", "AssignmentController@update");
            Route::post('/{id}/remove/assignment', 'AssignmentController@delete');


            Route::post("/{id}/add/user", "AssignmentController@addUser");
            Route::post("/{id}/remove/user", "AssignmentController@removeUser");


            Route::post("/{id}/mark/done", "AssignmentController@done");
            Route::post("/{id}/tome/add/notes", "AssignmentController@addNotes");
            Route::post("/{id}/tome/update/note", "AssignmentController@updateNote");
            Route::post("/{id}/tome/remove/note", "AssignmentController@deleteNote");

            Route::post("/{id}/mark/read", "AssignmentController@markAsRead");

            Route::post('/{id}/add/helper/user', 'AssignmentController@addUsersByMainAssignment');



        Route::group([
            'prefix' => 'assignment'
        ], function ($r) {
            Route::get('/templates', 'AssignmentTemplates@index');
            Route::post('/templates', 'AssignmentTemplates@store');
            Route::post('/templates/update', 'AssignmentTemplates@update');
            Route::post('/templates/delete', 'AssignmentTemplates@delete');

                Route::post("/{id}/change/status", "AssignmentController@changeStatus");
                Route::get('/', 'AssignmentController@index');
                Route::get('/{id}', 'AssignmentController@show');

        });

        /** end assignment */


        Route::post("/{id}/activate", "DocumentController@makeActive");

        Route::get("/sections", "SectionController@index");

        Route::get('/send/types', 'SectionController@getSendTypes');
        Route::get('/send/forms', 'SectionController@getSendForms');


        Route::get("/document_no", "DocumentController@getDocumentsNo");
        Route::get("/register_number", "DocumentController@getDocumentsRegNo");


        Route::post('/{id}/change/status', 'DocumentController@changeStatus');
        Route::post('/make/archive', 'ArchiveController@store');

        Route::post('/{id}/archive/update', 'ArchiveController@update');

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
        Route::post("/{id}/update", "DraftController@update");
        Route::post("/{id}/delete", "DraftController@destroy");

        Route::post("/{id}/add/documents", "DraftController@addDocument");
        Route::post("/{id}/remove/documents", "DraftController@removeDocument");
        Route::post("/{id}/update/documents", "DraftController@updateDocument");

        Route::post("/{id}/make/document", "DraftController@makeRealDocument");
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

