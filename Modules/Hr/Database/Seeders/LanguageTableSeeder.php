<?php

namespace Modules\Hr\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Hr\Entities\LanguageLevel;

class LanguageTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        \Modules\Hr\Entities\Language::create([
            "name" => "Azerbaycan dili",
            "code" => "AZE",
            "iso" => "AZ"
        ]);

        \Modules\Hr\Entities\Language::create([
            "name" => "Ingilis dili",
            "code" => "ENG",
            "iso" => "EN"
        ]);

        \Modules\Hr\Entities\Language::create([
            "name" => "Rus dili",
            "code" => "RUS",
            "iso" => "RU"
        ]);

        LanguageLevel::create([
            "name"=> "Əla",
            "code"=> "c2"
        ]);

        LanguageLevel::create([
            "name"=> "Orta",
            "code"=> "b2"
        ]);

        LanguageLevel::create([
            "name"=> "Zəyif",
            "code"=> "a1"
        ]);

        LanguageLevel::create([
            "name"=> "Kafi",
            "code"=> "b1"
        ]);

    }
}
