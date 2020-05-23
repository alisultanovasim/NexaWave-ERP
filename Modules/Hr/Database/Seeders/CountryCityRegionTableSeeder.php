<?php

namespace Modules\Hr\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Hr\Entities\City;
use Modules\Hr\Entities\Country;
use Modules\Hr\Entities\Region;

class CountryCityRegionTableSeeder extends Seeder
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
    }
}
