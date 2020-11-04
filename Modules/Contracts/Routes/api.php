<?php

use Illuminate\Support\Facades\Route;


Route::group([
    'prefix' => "v1/agreement",
    'middleware' => ['auth:api', 'authorize']
], function ($route) {

    Route::get('/contract/type', 'AgreementsController@getContractTypes');
    Route::post('/', 'AgreementsController@createAgreement');
    Route::post('/addition', 'AgreementsController@addAdditionToAgreement');

});


