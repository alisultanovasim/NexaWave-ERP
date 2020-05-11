<?php

namespace Modules\Hr\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Hr\Entities\City;
use Modules\Hr\Entities\Country;
use Modules\Hr\Entities\EducationLevel;
use Modules\Hr\Entities\EducationPlace;
use Modules\Hr\Entities\EducationSpecialty;
use Modules\Hr\Entities\EducationState;
use Modules\Hr\Entities\Faculty;
use Modules\Hr\Entities\LanguageLevel;
use Modules\Hr\Entities\Positions;
use Modules\Hr\Entities\Region;

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

        // $this->call("OthersTableSeeder");
    }
}
