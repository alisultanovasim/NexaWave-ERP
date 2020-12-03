<?php

use Illuminate\Support\Facades\Route;


Route::group([
    'prefix' => "v1/agreement",
    'middleware' => ['auth:api', 'authorize']
], function ($route) {

    Route::get('/contract/type', 'AgreementsController@getContractTypes');
    Route::get('/{id}', 'AgreementsController@getAgreementById');
    Route::post('/', 'AgreementsController@createAgreement');
    Route::post('/addition', 'AgreementsController@addAdditionToAgreement');
    Route::get('/', 'AgreementsController@getAgreements');
    Route::get('/{agreementId}/additions', 'AgreementsController@getAdditionsByAgreementId');
    Route::get('/additions/{additionId}', 'AgreementsController@getAdditionById');

});


