<?php

namespace Modules\Hr\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Hr\Entities\BloodGroup;
use Modules\Hr\Entities\City;
use Modules\Hr\Entities\Color;
use Modules\Hr\Entities\Country;
use Modules\Hr\Entities\Currency;
use Modules\Hr\Entities\Department;
use Modules\Hr\Entities\DurationType;
use Modules\Hr\Entities\EducationLevel;
use Modules\Hr\Entities\EducationPlace;
use Modules\Hr\Entities\EducationSpecialty;
use Modules\Hr\Entities\EducationState;
use Modules\Hr\Entities\Faculty;
use Modules\Hr\Entities\LanguageLevel;
use Modules\Hr\Entities\Positions;
use Modules\Hr\Entities\Region;
use Modules\Hr\Entities\Section;
use Modules\Hr\Entities\Sector;

class HrDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();
//
        $country = Country::create([
            'name' => "Azərbaycan Respublikasi",
            'short_name' => 'Azərbaycan'
        ]);

        $city = City::create([
            'name' => "Baki şəhəri",
            'country_id' => $country->id
        ]);

        $region = Region::create([
            'city_id' => $city->id,
            'name' => 'Xəzər'
        ]);


        $eduSpeciality = EducationSpecialty::create([
            "name" => "Computer science",
            "code" => "ks",
        ]);

        $eduSpeciality = EducationPlace::create([
            "name" => "Azərbaycan Respublikasının yanında Dövlət İdarəçilik Akademiyası",
            "code" => "dia",
            "note" => null,
            "country_id" => 1,
            "city_id" => 1,
            "region_id" => null,
        ]);

        $eduSpeciality = EducationLevel::create([
            "name" => "Bakalavr dərəcəsi",
            "code" => "bakalvr",
        ]);

        $faculty = Faculty::create([
            "name" => "Avtomatlaşdırma",
            "code" => "avtm",
        ]);

        $edulevel = EducationState::create([
            "name" => "Bitmiş",
            "code" => 50631
        ]);
        LanguageLevel::create([
            "name"=> "orta",
            "code"=> "language levelinde hec codu olar?"
        ]);


        DurationType::create([
            'name' => 'type_1'
        ]);

        Currency::create([
            'char' => '$',
            'name' => 'aze',
            'code' => 1231,
        ]);

        Color::insert(
          [
              'name'=>'blue',
            'code'=> 'blue',
            'position' => 1,
            'company_id'=>1
          ],
            [
                'name'=>'red',
                'code'=> 'red',
                'position' => 1,
                'company_id'=>1
            ]
        );
//
//
        BloodGroup::insert([
            ['name' => 'O(I)RH+'],
            ['name' => 'O(I)RH-'],
            ['name' => 'A(II)RH+'],
            ['name' => 'A(II)RH-'],
            ['name' => 'B(III)RH+'],
            ['name' => 'B(III)RH-'],
            ['name' => 'AB(IV)RH+'],
            ['name' => 'AB(IV)RH-'],
        ]);


        $dep = Department::create([
            'name'=>'Informasiya texnologiyalari',
            'short_name' => 'IT',
            'code' => 1,
            'country_id' => 1,
            'city_id' => 1,
            'position' => 1,
            'company_id' => 1,
        ]);

        $section =   Section::create([
            'department_id' => $dep->id,
            'name' => 'Programlasdirma' ,
            'short_name' => 'Programlasdirma' ,
            'code' => '1' ,
            'position' => 1 ,
            'company_id' => 1 ,
        ]);

        $sector = Sector::create([
            'section_id' =>$section->id ,
            'name' => 'Banck-end',
            'short_name' => 'back-end',
            'code' => 1,
            'position' =>1 ,
            'company_id' => 1,
        ]);
        // $this->call("OthersTableSeeder");
    }
}
