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
        $this->call(CountryCityRegionTableSeeder::class);
        $this->call(EducationTableSeeder::class);
        $this->call(LanguageTableSeeder::class);
        $this->call(CurrencyColorBloodTableSeeder::class);
//        $this->call(HelperDataTableSeeder::class);
        // $this->call("OthersTableSeeder");
    }
}
