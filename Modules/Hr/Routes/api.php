<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => "v1/contract",
    'middleware' => ['auth:api']
], function ($route) {
    Route::get('getContactStatics', 'ContractController@getContactStatics');
});
Route::group([
    'prefix' => "v1/hr",
    'middleware' => ['auth:api', 'authorize']
], function ($route) {

    Route::post("organizationType", "OrganizationTypeController@store");
    Route::get("organizationType", "OrganizationTypeController@index");
    Route::delete("organizationType/{id}", "OrganizationTypeController@destroy");
    Route::put("organizationType/{id}", "OrganizationTypeController@update");


    Route::group(['prefix' => "organizations"], function ($router) {
        Route::get('/', 'OrganizationController@index');
        Route::post('/', 'OrganizationController@store');
        Route::put('/{id}', 'OrganizationController@update');
        Route::delete('/{id}', 'OrganizationController@destroy');
    });
    Route::group(['prefix' => "paragraphs"], function ($router) {
        Route::get('/', 'ParagraphController@index');
        Route::get('/{id}', 'ParagraphController@show');
        Route::put('/{id}', 'ParagraphController@update');
        Route::post('/', 'ParagraphController@store');
    });


    Route::group(['prefix' => "countries"], function ($router) {
        Route::get('/', 'CountryController@index');
        Route::post('/', 'CountryController@store');
        Route::put('/{id}', 'CountryController@update');
        Route::delete('/{id}', 'CountryController@destroy');
    });

    Route::group(['prefix' => 'notification/cases'], function ($router) {
        Route::get('/', 'NotificationCaseController@index');
        Route::post('/', 'NotificationCaseController@create');
        Route::put('/{id}', 'NotificationCaseController@update');
        Route::delete('/{id}', 'NotificationCaseController@destroy');
    });

    Route::group(['prefix' => 'notification/types'], function ($router) {
        Route::get('/', 'NotificationTypeController@index');
        Route::post('/', 'NotificationTypeController@create');
        Route::put('/{id}', 'NotificationTypeController@update');
        Route::delete('/{id}', 'NotificationTypeController@destroy');
    });

    Route::group(['prefix' => 'address/types'], function ($router) {
        Route::get('/', 'AddressTypeController@index');
        Route::post('/', 'AddressTypeController@create');
        Route::put('/{id}', 'AddressTypeController@update');
        Route::delete('/{id}', 'AddressTypeController@destroy');
    });

    Route::group(['prefix' => 'education/states'], function ($router) {
        Route::get('/', 'EducationStateController@index');
        Route::post('/', 'EducationStateController@create');
        Route::put('/{id}', 'EducationStateController@update');
        Route::put('/{id}', 'EducationStateController@update');
        Route::delete('/{id}', 'EducationStateController@destroy');
    });

    Route::group(['prefix' => 'political/parties'], function ($router) {
        Route::get('/', 'PoliticalPartyController@index');
        Route::post('/', 'PoliticalPartyController@create');
        Route::put('/{id}', 'PoliticalPartyController@update');
        Route::delete('/{id}', 'PoliticalPartyController@destroy');
    });

    Route::group(['prefix' => 'bank/information'], function ($router) {
        Route::get('/', 'BankInformationController@index');
        Route::post('/', 'BankInformationController@create');
        Route::put('/{id}', 'BankInformationController@update');
        Route::delete('/{id}', 'BankInformationController@destroy');
    });


    Route::group(['prefix' => 'holidays'], function ($router) {
        Route::get('/', 'HolidayController@index');
        Route::post('/', 'HolidayController@create');
        Route::put('/{id}', 'HolidayController@update');
        Route::delete('/{id}', 'HolidayController@destroy');
    });

    Route::group(['prefix' => 'punishment/types'], function ($router) {
        Route::get('/', 'PunishmentTypeController@index');
        Route::post('/', 'PunishmentTypeController@create');
        Route::put('/{id}', 'PunishmentTypeController@update');
        Route::delete('/{id}', 'PunishmentTypeController@destroy');
    });

    Route::group(['prefix' => 'reward/types'], function ($router) {
        Route::get('/', 'RewardTypeController@index');
        Route::post('/', 'RewardTypeController@create');
        Route::put('/{id}', 'RewardTypeController@update');
        Route::delete('/{id}', 'RewardTypeController@destroy');
    });

    Route::group(['prefix' => 'social/states'], function ($router) {
        Route::get('/', 'SocialStateController@index');
        Route::post('/', 'SocialStateController@create');
        Route::put('/{id}', 'SocialStateController@update');
        Route::delete('/{id}', 'SocialStateController@destroy');
    });

    Route::group(['prefix' => 'labor/codes'], function ($router) {
        Route::get('/', 'LaborCodeController@index');
        Route::post('/', 'LaborCodeController@create');
        Route::put('/{id}', 'LaborCodeController@update');
        Route::delete('/{id}', 'LaborCodeController@destroy');
    });

    Route::group(['prefix' => 'supplement/salary/types'], function ($router) {
        Route::get('/', 'SupplementSalaryTypeController@index');
        Route::post('/', 'SupplementSalaryTypeController@create');
        Route::put('/{id}', 'SupplementSalaryTypeController@update');
        Route::delete('/{id}', 'SupplementSalaryTypeController@destroy');
    });

    Route::group(['prefix' => 'salaries'], function () {
        Route::get('/', 'SalaryController@index');
        Route::get('/{id}', 'SalaryController@show');
        Route::post('/', 'SalaryController@create');
        Route::put('/{id}', 'SalaryController@update');
        Route::delete('/{id}', 'SalaryController@destroy');
    });

    Route::group(['prefix' => 'uniform/types'], function ($router) {
        Route::get('/', 'UniformTypeController@index');
        Route::post('/', 'UniformTypeController@create');
        Route::put('/{id}', 'UniformTypeController@update');
        Route::delete('/{id}', 'UniformTypeController@destroy');
    });

    Route::group(['prefix' => 'inventory/types'], function ($router) {
        Route::get('/', 'InventoryTypeController@index');
        Route::post('/', 'InventoryTypeController@create');
        Route::put('/{id}', 'InventoryTypeController@update');
        Route::delete('/{id}', 'InventoryTypeController@destroy');
    });

    Route::group(['prefix' => 'health/types'], function ($router) {
        Route::get('/', 'HealthTypeController@index');
        Route::post('/', 'HealthTypeController@create');
        Route::put('/{id}', 'HealthTypeController@update');
        Route::delete('/{id}', 'HealthTypeController@destroy');
    });

    Route::group(['prefix' => 'medical/examination/types'], function ($router) {
        Route::get('/', 'MedicalExaminationTypeController@index');
        Route::post('/', 'MedicalExaminationTypeController@create');
        Route::put('/{id}', 'MedicalExaminationTypeController@update');
        Route::delete('/{id}', 'MedicalExaminationTypeController@destroy');
    });

    Route::group(['prefix' => 'relationship/types'], function ($router) {
        Route::get('/', 'RelationshipTypeController@index');
        Route::post('/', 'RelationshipTypeController@create');
        Route::put('/{id}', 'RelationshipTypeController@update');
        Route::delete('/{id}', 'RelationshipTypeController@destroy');
    });

    Route::group(['prefix' => 'language/levels'], function ($router) {
        Route::get('/', 'LanguageLevelController@index');
        Route::post('/', 'LanguageLevelController@create');
        Route::put('/{id}', 'LanguageLevelController@update');
        Route::delete('/{id}', 'LanguageLevelController@destroy');
    });

    Route::group(['prefix' => 'exam/types'], function ($router) {
        Route::get('/', 'ExamTypeController@index');
        Route::post('/', 'ExamTypeController@create');
        Route::put('/{id}', 'ExamTypeController@update');
        Route::delete('/{id}', 'ExamTypeController@destroy');
    });

    Route::group(['prefix' => 'education/specialties'], function ($router) {
        Route::get('/', 'EducationSpecialtyController@index');
        Route::post('/', 'EducationSpecialtyController@create');
        Route::put('/{id}', 'EducationSpecialtyController@update');
        Route::delete('/{id}', 'EducationSpecialtyController@destroy');
    });

    Route::group(['prefix' => 'faculties'], function ($router) {
        Route::get('/', 'FacultyController@index');
        Route::post('/', 'FacultyController@create');
        Route::put('/{id}', 'FacultyController@update');
        Route::delete('/{id}', 'FacultyController@destroy');
    });

    Route::group(['prefix' => 'education/levels'], function ($router) {
        Route::get('/', 'EducationLevelController@index');
        Route::post('/', 'EducationLevelController@create');
        Route::put('/{id}', 'EducationLevelController@update');
        Route::delete('/{id}', 'EducationLevelController@destroy');
    });

    Route::group(['prefix' => 'nationalities'], function ($router) {
        Route::get('/', 'NationalityController@index');
        Route::post('/', 'NationalityController@create');
        Route::put('/{id}', 'NationalityController@update');
        Route::delete('/{id}', 'NationalityController@destroy');
    });

    Route::group(['prefix' => 'academic/degrees'], function ($router) {
        Route::get('/', 'AcademicDegreeController@index');
        Route::post('/', 'AcademicDegreeController@create');
        Route::put('/{id}', 'AcademicDegreeController@update');
        Route::delete('/{id}', 'AcademicDegreeController@destroy');
    });

    Route::group(['prefix' => 'education/situations'], function ($router) {
        Route::get('/', 'EducationSituationController@index');
        Route::post('/', 'EducationSituationController@create');
        Route::put('/{id}', 'EducationSituationController@update');
        Route::delete('/{id}', 'EducationSituationController@destroy');
    });

    Route::group(['prefix' => 'military/states'], function ($router) {
        Route::get('/', 'MilitaryStateController@index');
        Route::post('/', 'MilitaryStateController@create');
        Route::put('/{id}', 'MilitaryStateController@update');
        Route::delete('/{id}', 'MilitaryStateController@destroy');
    });

    Route::group(['prefix' => 'colors'], function ($router) {
        Route::get('/', 'ColorController@index');
        Route::post('/', 'ColorController@create');
        Route::put('/{id}', 'ColorController@update');
        Route::delete('/{id}', 'ColorController@destroy');
    });

    Route::group(['prefix' => 'marital/states'], function ($router) {
        Route::get('/', 'MaritalStateController@index');
        Route::post('/', 'MaritalStateController@create');
        Route::put('/{id}', 'MaritalStateController@update');
        Route::delete('/{id}', 'MaritalStateController@destroy');
    });

    Route::group(['prefix' => 'languages'], function ($router) {
        Route::get('/', 'LanguageController@index');
        Route::post('/', 'LanguageController@create');
        Route::put('/{id}', 'LanguageController@update');
        Route::delete('/{id}', 'LanguageController@destroy');
    });

    Route::group(['prefix' => 'work/environments'], function ($router) {
        Route::get('/', 'WorkEnvironmentController@index');
        Route::post('/', 'WorkEnvironmentController@create');
        Route::put('/{id}', 'WorkEnvironmentController@update');
        Route::delete('/{id}', 'WorkEnvironmentController@destroy');
    });

    Route::group(['prefix' => 'specialization/degrees'], function ($router) {
        Route::get('/', 'SpecializationDegreeController@index');
        Route::post('/', 'SpecializationDegreeController@create');
        Route::put('/{id}', 'SpecializationDegreeController@update');
        Route::delete('/{id}', 'SpecializationDegreeController@destroy');
    });

    Route::group(['prefix' => 'professions'], function ($router) {
        Route::get('/', 'ProfessionController@index');
        Route::post('/', 'ProfessionController@create');
        Route::put('/{id}', 'ProfessionController@update');
        Route::delete('/{id}', 'ProfessionController@destroy');
    });

    Route::group(['prefix' => 'positions'], function ($router) {
        Route::get('/', 'PositionController@index');
        Route::post('/', 'PositionController@create');
        Route::put('/{id}', 'PositionController@update');
        Route::delete('/{id}', 'PositionController@destroy');
    });

    Route::group(['prefix' => 'personal/categories'], function ($router) {
        Route::get('/', 'PersonalCategoryController@index');
        Route::post('/', 'PersonalCategoryController@create');
        Route::put('/{id}', 'PersonalCategoryController@update');
        Route::delete('/{id}', 'PersonalCategoryController@destroy');
    });

    Route::group(['prefix' => 'cities'], function ($router) {
        Route::get('/', 'CityController@index');
        Route::post('/', 'CityController@create');
        Route::put('/{id}', 'CityController@update');
        Route::delete('/{id}', 'CityController@destroy');
    });

    Route::group(['prefix' => 'regions'], function ($router) {
        Route::get('/', 'RegionController@index');
        Route::post('/', 'RegionController@create');
        Route::put('/{id}', 'RegionController@update');
        Route::delete('/{id}', 'RegionController@destroy');
    });

    Route::group(['prefix' => 'education/places'], function ($router) {
        Route::get('/', 'EducationPlaceController@index');
        Route::post('/', 'EducationPlaceController@create');
        Route::put('/{id}', 'EducationPlaceController@update');
        Route::delete('/{id}', 'EducationPlaceController@destroy');
    });

    Route::group(['prefix' => 'contracts'], function ($router) {
        Route::get('/', 'ContractController@index');
        Route::post('/', 'ContractController@create');
        Route::put('/{id}', 'ContractController@update');
        Route::delete('/{id}', 'ContractController@destroy');
    });

    Route::group(['prefix' => 'work/shifts'], function ($router) {
        Route::get('/', 'WorkShiftController@index');
        Route::post('/', 'WorkShiftController@create');
        Route::put('/{id}', 'WorkShiftController@update');
        Route::delete('/{id}', 'WorkShiftController@destroy');
    });


    Route::group(['prefix' => 'departments'], function ($router) {
        Route::get('/', 'DepartmentController@index');
        Route::get('/structure', 'DepartmentController@structure');
        Route::get('/{id}', 'DepartmentController@show');
        Route::post('/', 'DepartmentController@create');
        Route::put('/{id}', 'DepartmentController@update');
        Route::delete('/{id}', 'DepartmentController@destroy');
    });

    Route::group(['prefix' => 'sections'], function ($router) {
        Route::get('/', 'SectionController@index');
        Route::get('/{id}', 'SectionController@show');
        Route::post('/', 'SectionController@create');
        Route::put('/{id}', 'SectionController@update');
        Route::delete('/{id}', 'SectionController@destroy');
    });

    Route::group(['prefix' => 'sectors'], function ($router) {
        Route::get('/', 'SectorController@index');
        Route::get('/{id}', 'SectorController@show');
        Route::post('/', 'SectorController@create');
        Route::put('/{id}', 'SectorController@update');
        Route::delete('/{id}', 'SectorController@destroy');
    });

    Route::group(['prefix' => 'organization/links'], function ($router) {
        Route::get('/', 'OrganizationLinkController@index');
        Route::post('/', 'OrganizationLinkController@create');
        Route::put('/{id}', 'OrganizationLinkController@update');
        Route::delete('/{id}', 'OrganizationLinkController@destroy');
    });

    Route::group(['prefix' => 'profession/links'], function ($router) {
        Route::get('/', 'ProfessionLinkController@index');
        Route::post('/', 'ProfessionLinkController@store');
        Route::put('/{id}', 'ProfessionLinkController@update');
        Route::delete('/{id}', 'ProfessionLinkController@destroy');
    });

    Route::group(['prefix' => "blood/groups"], function ($router) {
        Route::get("/", 'BloodGroupController@index');
    });

    Route::group([
        'prefix' => "employees",
        'namespace' => 'Employee'
    ], function ($router) {

        Route::get("/", 'EmployeeController@index');
        Route::get("/{id}", 'EmployeeController@show')->where('id', '[0-9]+');
        Route::post("/", 'EmployeeController@store');
        Route::post("/{id}", 'EmployeeController@update')->where('id', '[0-9]+');
        Route::delete("/{id}", 'EmployeeController@delete')->where('id', '[0-9]+');

        Route::group(['prefix' => 'rewards'], function () {
            Route::get("/", 'RewardController@index');
            Route::get("/{id}", 'RewardController@show');
            Route::post("/", 'RewardController@create');
            Route::put("/{id}", 'RewardController@update');
            Route::delete("/{id}", 'RewardController@destroy');
        });

        Route::group(['prefix' => 'punishments'], function () {
            Route::get("/", 'PunishmentController@index');
            Route::get("/{id}", 'PunishmentController@show');
            Route::post("/", 'PunishmentController@create');
            Route::put("/{id}", 'PunishmentController@update');
            Route::delete("/{id}", 'PunishmentController@destroy');
        });

//
//
//        Route::post("/make/in/{id}", 'EmployeeController@makeIn');
//        Route::post("/make/out/{id}", 'EmployeeController@makeOut');


        Route::group([
            'prefix' => 'contracts'
        ], function ($route) {
            Route::get("/", 'ContractController@index');
            Route::put("/terminate/{id}", 'ContractController@terminate');
            Route::get("/{id}", 'ContractController@show');
            Route::post("/", 'ContractController@store');
            Route::post("/update/{id}", 'ContractController@update');
            Route::post("/add/{id}", 'ContractController@add');
            Route::delete("/{id}", 'ContractController@delete');
        });
    });


    Route::group(['prefix' => 'workplaces'], function ($router) {
        Route::get('/', 'WorkplaceController@index');
        Route::post('/', 'WorkplaceController@create');
        Route::put('/{id}', 'WorkplaceController@update');
        Route::delete('/{id}', 'WorkplaceController@destroy');
    });


    //additions
    Route::group(['prefix' => 'currency'], function ($router) {
        Route::get('/', 'CurrencyController@index');
        Route::get('/{id}', 'CurrencyController@show');
        Route::post('/', 'CurrencyController@store');
        Route::put('/{id}', 'CurrencyController@update');
        Route::delete('/{id}', 'CurrencyController@destroy');
    });
    Route::group(['prefix' => 'duration/types'], function ($router) {
        Route::get('/', 'DurationTypeController@index');
        Route::get('/{id}', 'DurationTypeController@show');
        Route::post('/', 'DurationTypeController@store');
        Route::put('/{id}', 'DurationTypeController@update');
        Route::delete('/{id}', 'DurationTypeController@destroy');
    });

    Route::group(['prefix' => 'contract/types'], function ($router) {
        Route::get('/', 'ContractTypeController@index');
        Route::get('/{id}', 'ContractTypeController@show');
        Route::post('/', 'ContractTypeController@store');
        Route::put('/{id}', 'ContractTypeController@update');
        Route::delete('/{id}', 'ContractTypeController@destroy');
    });

    Route::group(['prefix' => 'inventories'], function () {
        Route::get('/', 'InventoryController@index');
        Route::post('/', 'InventoryController@create');
        Route::put('/{id}', 'InventoryController@update');
        Route::delete('/{id}', 'InventoryController@destroy');
    });

    Route::group(['prefix' => 'uniforms'], function () {
        Route::get('/', 'UniformController@index');
        Route::post('/', 'UniformController@create');
        Route::put('/{id}', 'UniformController@update');
        Route::delete('/{id}', 'UniformController@destroy');
    });

    Route::group([
        'namespace' => 'User'
    ], function ($router) {
        Route::group(['prefix' => 'users/education'], function ($router) {
            Route::get('/', 'UserEducationController@index');
            Route::get('/{id}', 'UserEducationController@show');
            Route::post('/', 'UserEducationController@store');
            Route::put('/{id}', 'UserEducationController@update');
            Route::delete('/{id}', 'UserEducationController@delete');
        });

        Route::group(['prefix' => 'users/certificates'], function () {
            Route::get('/', 'UserCertificateController@index');
            Route::get('/{id}', 'UserCertificateController@show');
            Route::post('/', 'UserCertificateController@create');
            Route::put('/{id}', 'UserCertificateController@update');
            Route::delete('/{id}', 'UserCertificateController@destroy');
        });

        Route::group(['prefix' => 'users/social/states'], function () {
            Route::get('/', 'UserSocialStateController@index');
            Route::get('/{id}', 'UserSocialStateController@show');
            Route::post('/', 'UserSocialStateController@create');
            Route::put('/{id}', 'UserSocialStateController@update');
            Route::delete('/{id}', 'UserSocialStateController@destroy');
        });

        Route::group(['prefix' => 'users/contact/information'], function () {
            Route::get('/', 'ContactInformationController@index');
            Route::get('/{id}', 'ContactInformationController@show');
            Route::post('/', 'ContactInformationController@create');
            Route::put('/{id}', 'ContactInformationController@update');
            Route::delete('/{id}', 'ContactInformationController@destroy');
        });

        Route::group(['prefix' => 'users/labor/activities'], function () {
            Route::get('/', 'LaborActivityController@index');
            Route::get('/{id}', 'LaborActivityController@show');
            Route::post('/', 'LaborActivityController@create');
            Route::put('/{id}', 'LaborActivityController@update');
            Route::delete('/{id}', 'LaborActivityController@destroy');
        });

        Route::group(['prefix' => 'users/language/skills'], function ($router) {
            Route::get('/', 'UserLanguageSkillController@index');
            Route::get('/{id}', 'UserLanguageSkillController@show');
            Route::post('/', 'UserLanguageSkillController@store');
            Route::put('/{id}', 'UserLanguageSkillController@update');
            Route::delete('/{id}', 'UserLanguageSkillController@delete');
        });

        Route::group(['prefix' => 'users/files'], function ($router) {
            Route::get('/', 'PrivateFileController@index');
            Route::get('/{id}', 'PrivateFileController@show');
            Route::post('/', 'PrivateFileController@create');
            Route::put('/{id}', 'PrivateFileController@update');
            Route::delete('/{id}', 'PrivateFileController@destroy');
        });
    });

    Route::group(['prefix' => 'time/tracking'], function () {

        Route::group(['prefix' => 'work/events'], function ($router) {
            Route::get('/', 'WorkEventController@index');
            Route::get('/{id}', 'WorkEventController@show');
            Route::post('/', 'WorkEventController@create');
            Route::put('/{id}', 'WorkEventController@update');
            Route::delete('/{id}', 'WorkEventController@destroy');
        });

        Route::group(['prefix' => 'vacation/planning'], function () {
            Route::get('/', 'VacationPlanningController@index');
            Route::get('/{id}', 'VacationPlanningController@show');
            Route::post('/', 'VacationPlanningController@create');
            Route::put('/{id}', 'VacationPlanningController@update');
            Route::delete('/{id}', 'VacationPlanningController@destroy');
        });

        Route::group(['prefix' => 'past/unused/vacation'], function () {
            Route::get('/', 'PastUnusedVacationController@index');
            Route::post('/', 'PastUnusedVacationController@create');
            Route::put('/{id}', 'PastUnusedVacationController@update');
            Route::delete('/{id}', 'PastUnusedVacationController@destroy');
        });

        Route::group(['prefix' => 'labor/vacation'], function () {
            Route::get('/', 'LaborVacationTrackingController@index');
        });

        Route::group(['prefix' => 'work/calendar'], function () {
            Route::get('/', 'WorkCalendarController@index');
            Route::post('/', 'WorkCalendarController@create');
            Route::delete('/{id}', 'WorkCalendarController@remove');
        });

        Route::group(['prefix' => 'event'], function () {
            Route::get('/', 'CompanyEventController@index');
            Route::post('/', 'CompanyEventController@create');
            Route::put('/{id}', 'CompanyEventController@update');
            Route::delete('/{id}', 'CompanyEventController@remove');
        });

        Route::group(['prefix' => 'work/hours'], function () {
            Route::get('/', 'CompanyWorkingHourController@index');
            Route::get('/{id}', 'CompanyWorkingHourController@show');
            Route::post('/', 'CompanyWorkingHourController@create');
            Route::put('/{id}', 'CompanyWorkingHourController@create');
            Route::delete('/{id}', 'CompanyWorkingHourController@destroy');
        });

        Route::group(['prefix' => 'work/skips'], function () {
            Route::get('/main', 'WorkSkipsController@getMainWorkSkips');
            Route::get('/', 'WorkSkipsController@index');
            Route::get('/{id}', 'WorkSkipsController@show');
            Route::post('/', 'WorkSkipsController@create');
            Route::put('/{id}', 'WorkSkipsController@create');
            Route::delete('/{id}', 'WorkSkipsController@destroy');
        });

    });

    Route::group(['prefix' => 'company/structure'], function () {
        Route::post('/set/curator', 'CompanyStructureController@setCuratorToStructure');
        Route::post('/add', 'CompanyStructureController@companyCreateStructures');
        Route::put('/{id}', 'CompanyStructureController@companyUpdateStructure');
        Route::get('/', 'CompanyStructureController@index');
        Route::post('/', 'CompanyStructureController@addStructureLink');
        Route::post('/set', 'CompanyStructureController@setCompanyStructure');
        Route::post('/positions', 'CompanyStructureController@setStructurePositions');
        Route::get('/positions', 'CompanyStructureController@getStructurePositions');
        Route::get('/employees', 'CompanyStructureController@getEmployees');
    });

    Route::get('company/staff/schedule', 'CompanyStaffScheduleController@index');

    Route::group(['prefix' => 'company/authorized/employees'], function () {
        Route::get('/', 'CompanyAuthorizedUsersController@index');
        Route::post('/', 'CompanyAuthorizedUsersController@addOrUpdateAuthorizedEmployee');
        Route::delete('/', 'CompanyAuthorizedUsersController@removeEmployeeFromAuthorizedUsers');
    });

    Route::group(['prefix' => 'company/orders'], function () {
        Route::get('/', 'CompanyOrderController@index');
        Route::get('/employees', 'CompanyOrderController@getOrderEmployees');
        Route::post('/', 'CompanyOrderController@create');
        Route::put('/{id}', 'CompanyOrderController@update');
        Route::get('/{id}', 'CompanyOrderController@show');
        Route::put('/confirm/{id}', 'CompanyOrderController@confirm');
        Route::delete('/{id}', 'CompanyOrderController@destroy');
    });

    Route::group(['prefix'=>'consent'],function (){
       Route::get('/','ConsentController@index');
       Route::post('/','ConsentController@create');
       Route::get('/getresponsibles','ConsentController@getResponsibles');
       Route::post('/{id}','ConsentController@delete');
       Route::post('/allowconsent/{consent_id}','ConsentController@allowConsent');
       Route::post('/disallowconsent/{consent_id}','ConsentController@disAllowConsent');
    });

});

