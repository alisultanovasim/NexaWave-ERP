<?php

namespace Modules\Hr\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Hr\Entities\EducationLevel;
use Modules\Hr\Entities\EducationPlace;
use Modules\Hr\Entities\EducationSpecialty;
use Modules\Hr\Entities\EducationState;
use Modules\Hr\Entities\Faculty;

class EducationTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();
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
    }
}
