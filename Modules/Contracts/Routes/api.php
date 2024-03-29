<?php

use Illuminate\Support\Facades\Route;


Route::group([
    'prefix' => "v1/agreement",
    'middleware' => ['auth:api', 'authorize']
], function ($route) {

    Route::get('/contract/type', 'AgreementsController@getContractTypes');
    Route::get('/{id}', 'AgreementsController@getAgreementById');
    Route::delete('/{id}', 'AgreementsController@destroy');
    Route::put('/{id}', 'AgreementsController@update');
    Route::put('/{id}/approve', 'AgreementsController@approve');
    Route::put('/{id}/terminate', 'AgreementsController@terminate');
    Route::post('/', 'AgreementsController@createAgreement');
    Route::post('/addition', 'AgreementsController@addAdditionToAgreement');
    Route::get('/', 'AgreementsController@getAgreements');
    Route::get('/{agreementId}/additions', 'AgreementsController@getAdditionsByAgreementId');
    Route::get('/additions/{additionId}', 'AgreementsController@getAdditionById');

    Route::group(['prefix' => 'bank/infos'], function () {
        Route::get('/', 'CompanyAgreementPartnerBankInfoController@index');
        Route::post('/', 'CompanyAgreementPartnerBankInfoController@create');
        Route::put('/{id}', 'CompanyAgreementPartnerBankInfoController@update');
        Route::delete('/{id}', 'CompanyAgreementPartnerBankInfoController@destroy');
    });

});


